<?php

namespace App\Http\Controllers;

use App\Models\BeerPayment;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\Staff;
use App\Services\NightSessionService;
use Illuminate\Http\Request;

class DashboardController extends Controller
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
        $beerPayments = collect();
        if ($session) {
            $closing = $this->sessionService->recalculateClosing($session);
            $beerPayments = $session->beerPayments()->orderByDesc('id')->get();
        }

        $totalProducts = Product::where('is_active', true)->count();
        $totalStaff = Staff::where('is_active', true)->count();

        return view('dashboard.index', compact('session', 'allSessions', 'closing', 'beerPayments', 'totalProducts', 'totalStaff'));
    }

    public function storeBeerPayment(Request $request)
    {
        $validated = $request->validate([
            'night_session_id' => 'required|exists:night_sessions,id',
            'payment_method' => 'required|in:efectivo,qr',
            'qr_provider' => 'nullable|required_if:payment_method,qr|in:YASTA,YAPE',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        $session = NightSession::findOrFail($validated['night_session_id']);
        if (! $session->isOpen()) {
            return back()->with('error', 'No se puede registrar un pago de cerveza en una noche cerrada.');
        }

        if ($validated['payment_method'] === 'efectivo') {
            $validated['qr_provider'] = null;
        }

        BeerPayment::create([
            'night_session_id' => $session->id,
            'payment_method' => $validated['payment_method'],
            'qr_provider' => $validated['qr_provider'] ?? null,
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Pago de cerveza registrado correctamente.');
    }

    public function destroyBeerPayment(Request $request, BeerPayment $beerPayment)
    {
        $session = $beerPayment->nightSession;
        if ($session && ! $session->isOpen()) {
            return back()->with('error', 'No se puede eliminar un pago de cerveza de una noche cerrada.');
        }

        $beerPayment->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pago de cerveza eliminado.']);
        }

        return back()->with('success', 'Pago de cerveza eliminado.');
    }

    public function updateReportedCash(Request $request)
    {
        $validated = $request->validate([
            'night_session_id' => 'required|exists:night_sessions,id',
            'reported_cash' => 'required|numeric|min:0',
        ]);

        $session = NightSession::findOrFail($validated['night_session_id']);
        if (! $session->isOpen()) {
            return back()->with('error', 'No se puede actualizar el efectivo de una noche cerrada.');
        }

        $closing = $session->cashClosing()->firstOrCreate(['night_session_id' => $session->id]);
        $closing->update([
            'reported_cash' => $validated['reported_cash'],
        ]);

        return back()->with('success', 'Efectivo en mano actualizado.');
    }
}
