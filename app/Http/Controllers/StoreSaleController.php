<?php

namespace App\Http\Controllers;

use App\Models\CashClosing;
use App\Models\Invoice;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\QrPayment;
use App\Models\StoreSale;
use App\Services\NightSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreSaleController extends Controller
{
    protected NightSessionService $sessionService;

    public function __construct(NightSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'night_session_id' => 'required|exists:night_sessions,id',
            'orders' => 'required|array|min:1',
            'orders.*.product_id' => 'required|exists:products,id',
            'orders.*.quantity' => 'required|integer|min:1',
            'orders.*.unit_price' => 'required|numeric|min:0',
            'cash_amount' => 'required|numeric|min:0',
            'qr_amount' => 'required|numeric|min:0',
            'card_amount' => 'required|numeric|min:0',
            'cobrante_name' => 'required|string|max:100',
            'bank_app' => 'nullable|in:YASTA,YAPE',
            'sync_qr' => 'nullable',
        ]);

        $cashAmount = (float)$validated['cash_amount'];
        $qrAmount = (float)$validated['qr_amount'];
        $cardAmount = (float)$validated['card_amount'];
        $cobrante = trim($validated['cobrante_name']);
        $session = NightSession::findOrFail($validated['night_session_id']);
        if (!$session->isOpen()) {
            return back()->with('error', 'No se pueden registrar despachos en una noche cerrada.');
        }

        $totalPrice = collect($validated['orders'])->sum(
            fn (array $order): float => (int)$order['quantity'] * (float)$order['unit_price']
        );
        $paymentTotal = $cashAmount + $qrAmount + $cardAmount;
        if (abs($totalPrice - $paymentTotal) > 0.01) {
            throw ValidationException::withMessages([
                'cash_amount' => 'La suma de efectivo, QR y tarjeta debe coincidir con el total del pedido.',
            ]);
        }

        DB::transaction(function () use ($validated, $session, $cobrante, $cashAmount, $qrAmount, $cardAmount, $request): void {
            foreach ($validated['orders'] as $index => $order) {
                $quantity = (int)$order['quantity'];
                $unitPrice = (float)$order['unit_price'];
                StoreSale::create([
                    'night_session_id' => $session->id,
                    'product_id' => $order['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                    'cash_amount' => $index === 0 ? $cashAmount : 0,
                    'qr_amount' => $index === 0 ? $qrAmount : 0,
                    'cobrante_name' => $cobrante,
                ]);

                // Consumir directamente de Bodega Central (sin exigir stock previo estricto)
                $product = Product::find($order['product_id']);
                if ($product) {
                    $product->deductWarehouseStock($quantity);
                }
            }

            if ($qrAmount > 0) {
                QrPayment::create([
                    'night_session_id' => $session->id,
                    'point_of_sale' => 'Tienda',
                    'operator_name' => $cobrante,
                    'bank_app' => $request->input('bank_app', 'YASTA'),
                    'amount' => $qrAmount,
                    'reference_code' => null,
                    'is_confirmed' => true,
                ]);
            }

            if ($cardAmount > 0) {
                $invoice = new Invoice();
                $invoice->night_session_id = $session->id;
                $invoice->correlative_num = (Invoice::where('night_session_id', $session->id)->max('correlative_num') ?? 0) + 1;
                $invoice->payment_method = 'tarjeta';
                $invoice->bar_name = 'Tienda';
                $invoice->amount = $cardAmount;
                $invoice->commission_rate = $session->pos_commission_rate;
                $invoice->notes = 'Pago Tarjeta POS Tienda';
                $invoice->calculateNet();
                $invoice->save();
            }
        });

        $this->sessionService->recalculateClosing($session);

        return back()->with('success', 'Pedido de Tienda registrado con ' . count($validated['orders']) . ' producto(s) por ' . $cobrante . '.');
    }

    public function destroy(StoreSale $storeSale)
    {
        $session = $storeSale->nightSession;
        if ($session && !$session->isOpen()) {
            return back()->with('error', 'No se pueden eliminar despachos de una noche cerrada.');
        }

        // Devolver cantidad a Bodega Central
        if ($storeSale->product) {
            $storeSale->product->restoreWarehouseStock($storeSale->quantity);
        }

        $storeSale->delete();

        if ($session) {
            $this->sessionService->recalculateClosing($session);
        }

        return back()->with('success', 'Pedido de Tienda eliminado.');
    }

    public function updateExtras(Request $request)
    {
        $validated = $request->validate([
            'night_session_id' => 'required|exists:night_sessions,id',
            'guardarropa_amount' => 'nullable|numeric|min:0',
            'snacks_amount' => 'nullable|numeric|min:0',
        ]);

        $session = NightSession::findOrFail($validated['night_session_id']);
        if (!$session->isOpen()) {
            return back()->with('error', 'No se pueden modificar ingresos en una noche cerrada.');
        }

        $closing = CashClosing::firstOrCreate(['night_session_id' => $session->id]);

        $closing->update([
            'total_guardarropa' => (float)($validated['guardarropa_amount'] ?? 0),
            'total_snacks' => (float)($validated['snacks_amount'] ?? 0),
        ]);

        $this->sessionService->recalculateClosing($session);

        return back()->with('success', 'Ingresos de Guardarropa y Snacks actualizados correctamente.');
    }
}
