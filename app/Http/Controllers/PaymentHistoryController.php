<?php

namespace App\Http\Controllers;

use App\Models\NightSession;
use App\Models\StaffAttendance;
use App\Services\NightSessionService;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    protected NightSessionService $sessionService;

    public function __construct(NightSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Muestra el historial de pagos y deudas del personal por noche
     */
    public function index(Request $request)
    {
        $session = $this->sessionService->resolveSession($request->get('session_id'));
        $allSessions = NightSession::orderByDesc('session_date')->get();

        $attendances = collect();
        $totalPlanilla = 0;
        $totalPagado = 0;
        $totalDeuda = 0;
        $totalTrabajadores = 0;
        $conDeudaCount = 0;
        $pagadosCount = 0;

        if ($session) {
            // Obtenemos los registros de la noche seleccionada
            $rawAttendances = StaffAttendance::with('staff')
                ->where('night_session_id', $session->id)
                ->get();

            // Calcular montos y ordenar: mayor deuda primero, deuda 0 al final
            $attendances = $rawAttendances->map(function ($att) {
                $payAmount = (float)$att->pay_amount;
                $isPaid = (bool)$att->is_paid;
                $debt = $isPaid ? 0.0 : $payAmount;

                $att->debt_amount = $debt;
                return $att;
            })->sort(function ($a, $b) {
                // Ordenar por deuda descendente (a los que se debe más primero)
                if ($b->debt_amount != $a->debt_amount) {
                    return $b->debt_amount <=> $a->debt_amount;
                }
                // Si la deuda es igual, ordenar alfabéticamente por nombre
                return strcmp($a->staff->name ?? '', $b->staff->name ?? '');
            })->values();

            foreach ($attendances as $att) {
                $totalPlanilla += (float)$att->pay_amount;
                if ($att->is_paid) {
                    $totalPagado += (float)$att->pay_amount;
                    $pagadosCount++;
                } else {
                    $totalDeuda += (float)$att->pay_amount;
                    $conDeudaCount++;
                }
            }

            $totalTrabajadores = $attendances->count();
        }

        // Historial de todas las noches con estadísticas de deuda por noche
        $nightsWithDebts = $allSessions->map(function ($s) {
            $unpaidAttendances = StaffAttendance::where('night_session_id', $s->id)->where('is_paid', false)->get();
            $paidAttendances = StaffAttendance::where('night_session_id', $s->id)->where('is_paid', true)->get();

            $debt = (float)$unpaidAttendances->sum('pay_amount');
            $paid = (float)$paidAttendances->sum('pay_amount');
            $total = $debt + $paid;
            $unpaidCount = $unpaidAttendances->count();
            $paidCount = $paidAttendances->count();

            return [
                'session' => $s,
                'total_debt' => $debt,
                'total_paid' => $paid,
                'total_payroll' => $total,
                'unpaid_count' => $unpaidCount,
                'paid_count' => $paidCount,
                'total_staff' => $unpaidCount + $paidCount,
                'has_debt' => $debt > 0,
            ];
        });

        // Historial global consolidado de todas las noches para resumen general
        $globalSummary = [
            'total_deuda_historica' => (float)StaffAttendance::where('is_paid', false)->sum('pay_amount'),
            'total_pagado_historico' => (float)StaffAttendance::where('is_paid', true)->sum('pay_amount'),
            'noches_con_deuda' => NightSession::whereHas('staffAttendances', function ($q) {
                $q->where('is_paid', false);
            })->count(),
        ];

        return view('payment_history.index', compact(
            'session',
            'allSessions',
            'attendances',
            'totalPlanilla',
            'totalPagado',
            'totalDeuda',
            'totalTrabajadores',
            'conDeudaCount',
            'pagadosCount',
            'globalSummary',
            'nightsWithDebts'
        ));
    }

    /**
     * Pagar a un trabajador específico que faltaba liquidar (desde el historial de pagos)
     */
    public function paySingle(Request $request, StaffAttendance $attendance)
    {
        $attendance->update([
            'is_paid' => true,
        ]);

        if ($attendance->nightSession) {
            $this->sessionService->recalculateClosing($attendance->nightSession);
        }

        $workerName = $attendance->staff->name ?? 'Personal';

        if ($request->wantsJson() || $request->ajax()) {
            $session = $attendance->nightSession;
            $unpaid = $session ? (float)$session->staffAttendances()->where('is_paid', false)->sum('pay_amount') : 0;
            $paid = $session ? (float)$session->staffAttendances()->where('is_paid', true)->sum('pay_amount') : 0;
            return response()->json([
                'success' => true,
                'workerName' => $workerName,
                'amount' => (float)$attendance->pay_amount,
                'totalDeuda' => $unpaid,
                'totalPagado' => $paid,
            ]);
        }

        return back()->with('success', "Se registró el pago de Bs. " . number_format($attendance->pay_amount, 2) . " para {$workerName}. Deuda saldada.");
    }
}
