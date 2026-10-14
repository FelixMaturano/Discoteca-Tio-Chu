<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\NightSession;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Services\NightSessionService;
use Illuminate\Http\Request;

class CashClosingController extends Controller
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

        $closing = null;
        $attendances = collect();
        $expenses = collect();
        $availableStaff = collect();

        if ($session) {
            $closing = $this->sessionService->recalculateClosing($session);
            $attendances = StaffAttendance::with('staff')
                ->where('night_session_id', $session->id)
                ->get()
                ->sort(function ($a, $b) {
                    $rankA = $a->staff ? $a->staff->getRoleCategoryRank() : 5;
                    $rankB = $b->staff ? $b->staff->getRoleCategoryRank() : 5;
                    if ($rankA === $rankB) {
                        return strcasecmp($a->staff->name ?? '', $b->staff->name ?? '');
                    }
                    return $rankA <=> $rankB;
                })->values();

            $expenses = Expense::where('night_session_id', $session->id)
                ->orderByDesc('id')
                ->get();

            $assignedStaffIds = $attendances->pluck('staff_id')->toArray();
            $availableStaff = Staff::where('is_active', true)
                ->whereNotIn('id', $assignedStaffIds)
                ->get()
                ->sort(function ($a, $b) {
                    $rankA = $a->getRoleCategoryRank();
                    $rankB = $b->getRoleCategoryRank();
                    if ($rankA === $rankB) {
                        return strcasecmp($a->name ?? '', $b->name ?? '');
                    }
                    return $rankA <=> $rankB;
                })->values();
        }

        return view('closing.index', compact(
            'session',
            'allSessions',
            'closing',
            'attendances',
            'expenses',
            'availableStaff'
        ));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'night_session_id' => 'required|exists:night_sessions,id',
            'category' => 'required|in:interno,externo',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'receipt_no' => 'nullable|string|max:100',
        ]);

        $session = NightSession::findOrFail($validated['night_session_id']);
        if (!$session->isOpen()) {
            return back()->with('error', 'No se pueden registrar gastos en una noche cerrada.');
        }

        Expense::create($validated);
        $this->sessionService->recalculateClosing($session);

        return back()->with('success', 'Gasto registrado correctamente.');
    }

    public function destroyExpense(Request $request, Expense $expense)
    {
        $session = $expense->nightSession;
        if ($session && !$session->isOpen()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se pueden eliminar gastos de una noche cerrada.'], 403);
            }
            return back()->with('error', 'No se pueden eliminar gastos de una noche cerrada.');
        }

        $expense->delete();

        if ($session) {
            $this->sessionService->recalculateClosing($session);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Gasto eliminado.']);
        }

        return back()->with('success', 'Gasto eliminado.');
    }

    public function updateAttendance(Request $request)
    {
        if ($request->has('night_session_id')) {
            $session = NightSession::find($request->input('night_session_id'));
            if ($session && !$session->isOpen()) {
                return back()->with('error', 'No se pueden modificar asistencias en una noche cerrada.');
            }
        }

        $attendancesData = $request->input('attendances', []);

        foreach ($attendancesData as $id => $data) {
            $att = StaffAttendance::find($id);
            if ($att) {
                $att->update([
                    'pay_amount' => (float)($data['pay_amount'] ?? 0),
                    'is_paid' => isset($data['is_paid']) && $data['is_paid'] == '1',
                ]);
            }
        }

        if (isset($session) && $session) {
            $this->sessionService->recalculateClosing($session);
        }

        return back()->with('success', 'Pagos a personal actualizados.');
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'night_session_id' => 'required|exists:night_sessions,id',
            'staff_id' => 'required|exists:staff,id',
            'pay_amount' => 'nullable|numeric|min:0',
        ]);

        $session = NightSession::findOrFail($validated['night_session_id']);
        if (!$session->isOpen()) {
            return back()->with('error', 'No se puede agregar personal en una noche cerrada.');
        }

        $staff = Staff::findOrFail($validated['staff_id']);

        $payAmount = isset($validated['pay_amount']) && $validated['pay_amount'] > 0
            ? (float)$validated['pay_amount']
            : $staff->getPayForDay($session->day_name);

        StaffAttendance::firstOrCreate(
            [
                'night_session_id' => $session->id,
                'staff_id' => $staff->id,
            ],
            [
                'pay_amount' => $payAmount,
                'is_paid' => false,
            ]
        );

        $this->sessionService->recalculateClosing($session);

        return back()->with('success', "Personal {$staff->name} agregado a la noche de {$session->day_name}.");
    }

    public function destroyAttendance(StaffAttendance $attendance)
    {
        $session = $attendance->nightSession;
        if ($session && !$session->isOpen()) {
            return back()->with('error', 'No se puede retirar personal de una noche cerrada.');
        }

        $name = $attendance->staff->name ?? 'Trabajador';
        $attendance->delete();

        if ($session) {
            $this->sessionService->recalculateClosing($session);
        }

        return back()->with('success', "{$name} retirado del turno de esta noche.");
    }

    public function closeNight(Request $request, NightSession $session)
    {
        $session->update(['status' => 'closed']);
        $closing = $this->sessionService->recalculateClosing($session);
        $closing->update(['closed_at' => now()]);

        return back()->with('success', 'La noche ha sido cerrada satisfactoriamente.');
    }

    public function reopenNight(Request $request, NightSession $session)
    {
        $session->update(['status' => 'open']);
        return back()->with('success', 'La noche ha sido reabierta.');
    }
}
