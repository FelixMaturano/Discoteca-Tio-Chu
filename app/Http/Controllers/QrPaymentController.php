<?php

namespace App\Http\Controllers;

use App\Models\NightSession;
use App\Models\QrPayment;
use App\Models\Staff;
use App\Services\NightSessionService;
use Illuminate\Http\Request;

class QrPaymentController extends Controller
{
    protected NightSessionService $sessionService;

    public function __construct(NightSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function index(Request $request)
    {
        $session = $this->sessionService->resolveSession($request->get('session_id'));
        $allSessions = NightSession::orderByDesc('session_date')->get();

        $pointsOfSale = ['Barra Principal', 'Tienda', 'Subte'];
        $paymentsByPos = [
            'Barra Principal' => collect(),
            'Tienda' => collect(),
            'Subte' => collect(),
        ];
        $totalsByPos = [
            'Barra Principal' => ['yasta' => 0, 'yape' => 0, 'total' => 0],
            'Tienda' => ['yasta' => 0, 'yape' => 0, 'total' => 0],
            'Subte' => ['yasta' => 0, 'yape' => 0, 'total' => 0],
        ];

        $totalYasta = 0;
        $totalYape = 0;
        $totalGeneral = 0;

        if ($session) {
            $allPayments = QrPayment::where('night_session_id', $session->id)
                ->orderByDesc('id')
                ->get();

            foreach ($allPayments as $p) {
                // Normalizar punto de venta si vino como 'Barra Subte' o vacío
                $pos = $p->point_of_sale;
                if ($pos === 'Barra Subte') {
                    $pos = 'Subte';
                } elseif (!in_array($pos, $pointsOfSale)) {
                    $pos = 'Barra Principal';
                }

                $paymentsByPos[$pos]->push($p);

                if ($p->bank_app === 'YASTA') {
                    $totalsByPos[$pos]['yasta'] += (float)$p->amount;
                    $totalYasta += (float)$p->amount;
                } else {
                    $totalsByPos[$pos]['yape'] += (float)$p->amount;
                    $totalYape += (float)$p->amount;
                }
                $totalsByPos[$pos]['total'] += (float)$p->amount;
            }

            $totalGeneral = $totalYasta + $totalYape;
        }

        // Obtener personal activo filtrado: solo Meseros, Bartenders y Refuerzos
        // Excluye explícitamente Limpieza y Seguridad
        $cobrantesStaff = Staff::where('is_active', true)
            ->where(function ($q) {
                $q->where('role', 'like', '%mesero%')
                  ->orWhere('role', 'like', '%mozo%')
                  ->orWhere('role', 'like', '%refuerzo%')
                  ->orWhere('role', 'like', '%bartender%')
                  ->orWhere('role', 'like', '%barra%');
            })
            ->where('role', 'not like', '%limpieza%')
            ->where('role', 'not like', '%seguridad%')
            ->orderBy('name')
            ->get();

        return view('qrs.index', compact(
            'session',
            'allSessions',
            'pointsOfSale',
            'paymentsByPos',
            'totalsByPos',
            'totalYasta',
            'totalYape',
            'totalGeneral',
            'cobrantesStaff'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'night_session_id' => 'required|exists:night_sessions,id',
            'point_of_sale' => 'nullable|string|in:Barra Principal,Tienda,Subte',
            'operator_name' => 'nullable|string|max:100',
            'cobrante_name' => 'nullable|string|max:100',
            'bank_app' => 'required|in:YASTA,YAPE',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $session = NightSession::findOrFail($validated['night_session_id']);
        if (!$session->isOpen()) {
            return back()->with('error', 'No se pueden registrar cobros QR en una noche cerrada.');
        }

        $pointOfSale = $validated['point_of_sale'] ?? 'Barra Principal';

        $cobrante = trim($validated['cobrante_name'] ?? $validated['operator_name'] ?? '');
        if (empty($cobrante)) {
            return back()->withErrors(['operator_name' => 'El Nombre del Cobrante es obligatorio.'])->withInput();
        }

        QrPayment::create([
            'night_session_id' => $validated['night_session_id'],
            'point_of_sale' => $pointOfSale,
            'operator_name' => $cobrante,
            'bank_app' => $validated['bank_app'],
            'amount' => $validated['amount'],
            'reference_code' => null, // Removido por requerimiento expreso
            'is_confirmed' => true,
        ]);

        $this->sessionService->recalculateClosing($session);

        return back()->with('success', 'Pago QR registrado en ' . $pointOfSale . '.');
    }

    public function destroy(Request $request, QrPayment $qr)
    {
        $session = $qr->nightSession;
        if ($session && !$session->isOpen()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se pueden anular cobros QR de una noche cerrada.'], 403);
            }
            return back()->with('error', 'No se pueden anular cobros QR de una noche cerrada.');
        }

        $qr->delete();

        if ($session) {
            $this->sessionService->recalculateClosing($session);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pago QR eliminado.']);
        }

        return back()->with('success', 'Pago QR eliminado.');
    }
}
