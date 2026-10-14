<?php

namespace App\Http\Controllers;

use App\Models\BarSale;
use App\Models\CashClosing;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Services\NightSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NightSessionController extends Controller
{
    protected NightSessionService $sessionService;

    public function __construct(NightSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function index()
    {
        $sessions = NightSession::orderByDesc('session_date')->get();
        return view('sessions.index', compact('sessions'));
    }

    public function create()
    {
        $openSession = NightSession::where('status', 'open')->first();
        return view('sessions.create', compact('openSession'));
    }

    public function store(Request $request)
    {
        // Validar que no haya una noche anterior abierta
        $openSession = NightSession::where('status', 'open')->first();
        if ($openSession) {
            $openDate = Carbon::parse($openSession->session_date)->format('d/m/Y');
            return redirect()->route('sessions.create')->with('error', "No puedes aperturar una nueva noche porque la jornada del {$openSession->day_name} ({$openDate}) aún se encuentra ABIERTA. Debes realizar el Cierre de Caja definitivo antes de abrir una nueva.");
        }

        $today = Carbon::now()->format('Y-m-d');

        $validated = $request->validate([
            'session_date' => 'required|date|unique:night_sessions,session_date',
            'pos_commission_rate' => 'required|numeric|min:0|max:1',
            'notes' => 'nullable|string',
        ], [
            'session_date.unique' => 'Ya existe una noche registrada para esta fecha.',
        ]);

        // Determinar automáticamente el día de la semana en español
        $diasEspañol = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];
        $parsedDate = Carbon::parse($validated['session_date']);
        $validated['day_name'] = $diasEspañol[$parsedDate->dayOfWeek] ?? 'Viernes';
        $validated['status'] = 'open';

        $session = NightSession::create($validated);
        session(['active_night_session_id' => $session->id]);

        // Inicializar resumen de cierre
        CashClosing::create(['night_session_id' => $session->id]);

        // Cargar personal activo según el día de la semana
        $dayName = $validated['day_name'];
        $staff = Staff::where('is_active', true)->get();
        foreach ($staff as $member) {
            if ($member->worksOnDay($dayName)) {
                StaffAttendance::create([
                    'night_session_id' => $session->id,
                    'staff_id' => $member->id,
                    'pay_amount' => $member->getPayForDay($dayName),
                    'is_paid' => false,
                ]);
            }
        }

        // Buscar noche anterior para heredar saldos sobrantes de barra
        $dateStr = Carbon::parse($validated['session_date'])->format('Y-m-d');
        $previousSession = NightSession::where('id', '!=', $session->id)
            ->whereDate('session_date', '<', $dateStr)
            ->orderByDesc('session_date')
            ->first() ?? NightSession::where('id', '<', $session->id)->orderByDesc('id')->first();

        // Cargar productos en los 3 puntos de venta (Barra Principal, Barra Subterráneo, Tienda)
        $products = Product::where('is_active', true)->get();
        foreach (['Barra Principal', 'Barra Subterráneo', 'Tienda'] as $bar) {
            foreach ($products as $prod) {
                BarSale::create([
                    'night_session_id' => $session->id,
                    'product_id' => $prod->id,
                    'bar_name' => $bar,
                    'initial_packages' => 0,
                    'initial_units' => 0,
                    'added_packages' => 0,
                    'added_units' => 0,
                    'night_packages' => 0,
                    'night_units' => 0,
                    'packages' => 0,
                    'units' => 0,
                    'total_initial' => 0,
                    'saldo' => 0,
                    'vendido' => 0,
                    'unit_price' => $prod->sale_price,
                    'subtotal' => 0,
                ]);
            }
        }

        return redirect()->route('sessions.index')->with('success', 'Noche creada para el ' . $validated['day_name'] . ' ' . $parsedDate->format('d/m/Y') . ' con inventarios inicializados.');
    }

    public function updateStatus(Request $request, NightSession $session)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,closed',
            'pos_commission_rate' => 'nullable|numeric|min:0|max:1',
        ]);

        $session->update($validated);

        return back()->with('success', 'Estado de la noche actualizado.');
    }

    public function destroy(NightSession $session)
    {
        $dateFormatted = Carbon::parse($session->session_date)->format('d/m/Y');
        $session->delete();

        return redirect()->route('sessions.index')->with('success', 'La noche del ' . $dateFormatted . ' ha sido eliminada correctamente.');
    }

    public function consolidatedSummary(Request $request)
    {
        $sessionIds = $request->input('session_ids', []);
        if (is_string($sessionIds)) {
            $sessionIds = explode(',', $sessionIds);
        }
        $sessionIds = array_filter(array_map('intval', (array)$sessionIds));

        if (empty($sessionIds)) {
            $sessionIds = NightSession::orderByDesc('session_date')->limit(3)->pluck('id')->toArray();
        }

        $summary = $this->sessionService->getConsolidatedSummary($sessionIds);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($summary);
        }

        return view('sessions.consolidated', compact('summary'));
    }
}
