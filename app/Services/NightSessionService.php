<?php

namespace App\Services;

use App\Models\CashClosing;
use App\Models\NightSession;

class NightSessionService
{
    /**
     * Resuelve la sesión activa o seleccionada, recordando la selección en la sesión HTTP
     */
    public function resolveSession(?int $sessionId = null): ?NightSession
    {
        if ($sessionId) {
            $session = NightSession::find($sessionId);
            if ($session) {
                session(['active_night_session_id' => $session->id]);
                return $session;
            }
        }

        $sessionFromStorage = session('active_night_session_id');
        if ($sessionFromStorage) {
            $session = NightSession::find($sessionFromStorage);
            if ($session) {
                return $session;
            }
        }

        $session = NightSession::orderByDesc('session_date')->first();
        if ($session) {
            session(['active_night_session_id' => $session->id]);
        }

        return $session;
    }

    /**
     * Recalcula y sincroniza el cuadre de caja de una noche
     */
    public function recalculateClosing(NightSession $session): CashClosing
    {
        $closing = CashClosing::firstOrCreate(['night_session_id' => $session->id]);

        // Facturas (tarjetas — de cualquier barra)
        $cardInvoices = $session->invoices()->where('payment_method', 'tarjeta')->get();
        $cashInvoices = $session->invoices()->where('payment_method', 'efectivo')->get();

        $totalCard           = $cardInvoices->sum('amount');
        $totalCardCommission = $cardInvoices->sum('commission_amount');
        $totalCardNet        = $cardInvoices->sum('net_amount');
        $totalCashInvoices   = $cashInvoices->sum('amount');

        // Pagos QR globales
        $totalQrYasta = (float) $session->qrPayments()->where('bank_app', 'YASTA')->sum('amount');
        $totalQrYape  = (float) $session->qrPayments()->where('bank_app', 'YAPE')->sum('amount');

        // 1. Efectivo en Barra Principal: Ventas - QR - Tarjeta bruto
        $salesPrincipal = (float) $session->barSales()->whereIn('bar_name', ['Barra Principal', 'Barra Kelly (Principal)', 'Principal', 'Kelly'])->sum('subtotal');
        $qrPrincipal = (float) $session->qrPayments()->whereIn('point_of_sale', ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'])->sum('amount');
        $cardPrincipal = (float) $session->invoices()->whereIn('bar_name', ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'])->where('payment_method', 'tarjeta')->sum('amount');
        $cashPrincipal = max(0, $salesPrincipal - $qrPrincipal - $cardPrincipal);

        // 2. Efectivo en Barra Subterráneo: Ventas - QR - Tarjeta bruto
        $salesSubte = (float) $session->barSales()->whereIn('bar_name', ['Barra Subterráneo', 'Barra Ariel (Subte)', 'Subterráneo', 'Subterraneo', 'Subte', 'Ariel'])->sum('subtotal');
        $qrSubte = (float) $session->qrPayments()->whereIn('point_of_sale', ['Barra Subterráneo', 'Subte', 'Barra Subte', 'Subterráneo', 'Subterraneo', 'Barra Ariel (Subte)', 'Ariel'])->sum('amount');
        $cardSubte = (float) $session->invoices()->whereIn('bar_name', ['Barra Subterráneo', 'Subterráneo', 'Subterraneo', 'Subte', 'Barra Ariel (Subte)', 'Ariel'])->where('payment_method', 'tarjeta')->sum('amount');
        $cashSubte = max(0, $salesSubte - $qrSubte - $cardSubte);

        // 3. Efectivo en Tienda: Ventas (Inventario + Pedidos directos) - QR Tienda - Tarjeta Tienda
        $salesTienda = (float) $session->barSales()->whereIn('bar_name', ['Tienda', 'tienda'])->sum('subtotal')
            + (float) $session->storeSales()->sum('total_price');
        $qrTienda = (float) $session->qrPayments()->whereIn('point_of_sale', ['Tienda', 'tienda'])->sum('amount');
        $cardTienda = (float) $session->invoices()->whereIn('bar_name', ['Tienda', 'tienda'])->where('payment_method', 'tarjeta')->sum('amount');
        $cashTienda = max(0, $salesTienda - $qrTienda - $cardTienda);

        // Efectivo total en Cierre de Caja = Suma de efectivos de las barras y tienda
        $totalEfectivo = $cashPrincipal + $cashSubte + $cashTienda;

        // Ventas totales de referencia (barras + tienda)
        $totalBarSales = $salesPrincipal + $salesSubte + $salesTienda;

        // Personal
        $totalStaffPaid = (float) $session->staffAttendances()->where('is_paid', true)->sum('pay_amount');

        // Gastos
        $totalExpenses = (float) $session->expenses()->sum('amount');

        // Ingresos extras de Tienda (Guardarropa y Snacks) — se mantienen guardados pero no se suman al cuadre
        $totalGuardarropa = (float)($closing->total_guardarropa ?? 0);
        $totalSnacks      = (float)($closing->total_snacks ?? 0);

        // Total Ingresos = Tarjeta bruto + QR YASTA + QR YAPE + Efectivo Barras
        $totalIngresos = $totalCard + $totalQrYasta + $totalQrYape + $totalEfectivo;

        // Total Egresos (Personal + Gastos)
        $totalEgresos = $totalStaffPaid + $totalExpenses;

        // Saldo Neto en Caja
        $netCashClosing = $totalIngresos - $totalEgresos;

        $closing->update([
            'total_card'            => $totalCard,
            'total_card_commission' => $totalCardCommission,
            'total_card_net'        => $totalCardNet,
            'total_cash_invoices'   => $totalEfectivo,  // reutilizamos este campo para el efectivo calculado
            'total_qr_yasta'        => $totalQrYasta,
            'total_qr_yape'         => $totalQrYape,
            'total_bar_sales'       => $totalBarSales,
            'total_guardarropa'     => $totalGuardarropa,
            'total_snacks'          => $totalSnacks,
            'total_staff_paid'      => $totalStaffPaid,
            'total_expenses'        => $totalExpenses,
            'net_cash_closing'      => $netCashClosing,
        ]);

        return $closing;
    }

    /**
     * Genera el consolidado financiero de múltiples noches (ej: Fin de semana Vie-Sáb-Dom)
     *
     * @param array<int> $sessionIds
     * @return array<string, mixed>
     */
    public function getConsolidatedSummary(array $sessionIds): array
    {
        if (empty($sessionIds)) {
            return [
                'sessions' => collect(),
                'count' => 0,
                'date_range' => 'Ninguna noche seleccionada',
                'totals' => [
                    'ventas' => 0,
                    'ingresos' => 0,
                    'efectivo' => 0,
                    'qr_total' => 0,
                    'qr_yasta' => 0,
                    'qr_yape' => 0,
                    'card_gross' => 0,
                    'card_commission' => 0,
                    'card_net' => 0,
                    'personal' => 0,
                    'gastos' => 0,
                    'egresos' => 0,
                    'neto' => 0,
                    'guardarropa' => 0,
                    'snacks' => 0,
                ],
                'bars' => [
                    'principal' => ['name' => 'Barra Principal', 'ventas' => 0, 'efectivo' => 0, 'qr' => 0, 'card' => 0],
                    'subte' => ['name' => 'Barra Subterráneo', 'ventas' => 0, 'efectivo' => 0, 'qr' => 0, 'card' => 0],
                    'tienda' => ['name' => 'Tienda Oficial', 'ventas' => 0, 'efectivo' => 0, 'qr' => 0, 'card' => 0, 'store_orders' => 0, 'store_units' => 0],
                ],
                'nights' => [],
            ];
        }

        $sessions = NightSession::whereIn('id', $sessionIds)
            ->orderBy('session_date')
            ->get();

        $nightsData = [];
        $totalVentas = 0;
        $totalEfectivo = 0;
        $totalQrYasta = 0;
        $totalQrYape = 0;
        $totalQr = 0;
        $totalCardGross = 0;
        $totalCardCommission = 0;
        $totalCardNet = 0;
        $totalPersonal = 0;
        $totalGastos = 0;
        $totalGuardarropa = 0;
        $totalSnacks = 0;

        $principalVentas = 0;
        $principalQr = 0;
        $principalCard = 0;
        $principalCash = 0;

        $subteVentas = 0;
        $subteQr = 0;
        $subteCard = 0;
        $subteCash = 0;

        $tiendaVentas = 0;
        $tiendaQr = 0;
        $tiendaCard = 0;
        $tiendaCash = 0;
        $tiendaStoreOrdersCount = 0;
        $tiendaStoreItemsQty = 0;

        foreach ($sessions as $session) {
            $closing = $this->recalculateClosing($session);

            // 1. Desglose Barra Principal
            $sPrincipalSales = (float) $session->barSales()->whereIn('bar_name', ['Barra Principal', 'Barra Kelly (Principal)', 'Principal', 'Kelly'])->sum('subtotal');
            $sPrincipalQr = (float) $session->qrPayments()->whereIn('point_of_sale', ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'])->sum('amount');
            $sPrincipalCard = (float) $session->invoices()->whereIn('bar_name', ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'])->where('payment_method', 'tarjeta')->sum('amount');
            $sPrincipalCash = max(0, $sPrincipalSales - $sPrincipalQr - $sPrincipalCard);

            $principalVentas += $sPrincipalSales;
            $principalQr     += $sPrincipalQr;
            $principalCard   += $sPrincipalCard;
            $principalCash   += $sPrincipalCash;

            // 2. Desglose Barra Subterráneo
            $sSubteSales = (float) $session->barSales()->whereIn('bar_name', ['Barra Subterráneo', 'Barra Ariel (Subte)', 'Subterráneo', 'Subterraneo', 'Subte', 'Ariel'])->sum('subtotal');
            $sSubteQr = (float) $session->qrPayments()->whereIn('point_of_sale', ['Barra Subterráneo', 'Subte', 'Barra Subte', 'Subterráneo', 'Subterraneo', 'Barra Ariel (Subte)', 'Ariel'])->sum('amount');
            $sSubteCard = (float) $session->invoices()->whereIn('bar_name', ['Barra Subterráneo', 'Subterráneo', 'Subterraneo', 'Subte', 'Barra Ariel (Subte)', 'Ariel'])->where('payment_method', 'tarjeta')->sum('amount');
            $sSubteCash = max(0, $sSubteSales - $sSubteQr - $sSubteCard);

            $subteVentas += $sSubteSales;
            $subteQr     += $sSubteQr;
            $subteCard   += $sSubteCard;
            $subteCash   += $sSubteCash;

            // 3. Desglose Tienda
            $sTiendaStore = (float) $session->storeSales()->sum('total_price');
            $sTiendaBar = (float) $session->barSales()->whereIn('bar_name', ['Tienda', 'tienda'])->sum('subtotal');
            $sTiendaSales = $sTiendaBar + $sTiendaStore;
            $sTiendaQr = (float) $session->qrPayments()->whereIn('point_of_sale', ['Tienda', 'tienda'])->sum('amount');
            $sTiendaCard = (float) $session->invoices()->whereIn('bar_name', ['Tienda', 'tienda'])->where('payment_method', 'tarjeta')->sum('amount');
            $sTiendaCash = max(0, $sTiendaSales - $sTiendaQr - $sTiendaCard);

            $tiendaVentas += $sTiendaSales;
            $tiendaQr     += $sTiendaQr;
            $tiendaCard   += $sTiendaCard;
            $tiendaCash   += $sTiendaCash;
            $tiendaStoreOrdersCount += $session->storeSales()->count();
            $tiendaStoreItemsQty += (int) $session->storeSales()->sum('quantity');

            // Métricas nocturnas individuales
            $nightVentas = (float)$closing->total_bar_sales;
            $nightEfectivo = (float)$closing->total_cash_invoices;
            $nightQrYasta = (float)$closing->total_qr_yasta;
            $nightQrYape = (float)$closing->total_qr_yape;
            $nightQr = $nightQrYasta + $nightQrYape;
            $nightCardGross = (float)$closing->total_card;
            $nightCardCommission = (float)$closing->total_card_commission;
            $nightCardNet = (float)$closing->total_card_net;
            $nightPersonal = (float)$closing->total_staff_paid;
            $nightGastos = (float)$closing->total_expenses;
            $nightIngresos = $nightCardGross + $nightQr + $nightEfectivo;
            $nightEgresos = $nightPersonal + $nightGastos;
            $nightNeto = $nightIngresos - $nightEgresos;

            $totalVentas += $nightVentas;
            $totalEfectivo += $nightEfectivo;
            $totalQrYasta += $nightQrYasta;
            $totalQrYape += $nightQrYape;
            $totalQr += $nightQr;
            $totalCardGross += $nightCardGross;
            $totalCardCommission += $nightCardCommission;
            $totalCardNet += $nightCardNet;
            $totalPersonal += $nightPersonal;
            $totalGastos += $nightGastos;
            $totalGuardarropa += (float)$closing->total_guardarropa;
            $totalSnacks += (float)$closing->total_snacks;

            $nightsData[] = [
                'id' => $session->id,
                'date' => \Carbon\Carbon::parse($session->session_date)->format('d/m/Y'),
                'raw_date' => $session->session_date,
                'day_name' => $session->day_name,
                'status' => $session->status,
                'ventas' => $nightVentas,
                'efectivo' => $nightEfectivo,
                'qr' => $nightQr,
                'tarjeta_bruto' => $nightCardGross,
                'tarjeta_neto' => $nightCardNet,
                'personal' => $nightPersonal,
                'gastos' => $nightGastos,
                'ingresos' => $nightIngresos,
                'egresos' => $nightEgresos,
                'neto' => $nightNeto,
            ];
        }

        $totalIngresos = $totalCardGross + $totalQr + $totalEfectivo;
        $totalEgresos = $totalPersonal + $totalGastos;
        $totalNeto = $totalIngresos - $totalEgresos;

        $startDate = $sessions->first() ? \Carbon\Carbon::parse($sessions->first()->session_date)->format('d/m/Y') : '';
        $endDate = $sessions->last() ? \Carbon\Carbon::parse($sessions->last()->session_date)->format('d/m/Y') : '';
        $dateRange = $startDate === $endDate ? $startDate : "Del {$startDate} al {$endDate}";

        return [
            'sessions' => $sessions,
            'count' => $sessions->count(),
            'date_range' => $dateRange,
            'totals' => [
                'ventas' => $totalVentas,
                'ingresos' => $totalIngresos,
                'efectivo' => $totalEfectivo,
                'qr_total' => $totalQr,
                'qr_yasta' => $totalQrYasta,
                'qr_yape' => $totalQrYape,
                'card_gross' => $totalCardGross,
                'card_commission' => $totalCardCommission,
                'card_net' => $totalCardNet,
                'personal' => $totalPersonal,
                'gastos' => $totalGastos,
                'egresos' => $totalEgresos,
                'neto' => $totalNeto,
                'guardarropa' => $totalGuardarropa,
                'snacks' => $totalSnacks,
            ],
            'bars' => [
                'principal' => [
                    'name' => 'Barra Principal',
                    'ventas' => $principalVentas,
                    'efectivo' => $principalCash,
                    'qr' => $principalQr,
                    'card' => $principalCard,
                ],
                'subte' => [
                    'name' => 'Barra Subterráneo',
                    'ventas' => $subteVentas,
                    'efectivo' => $subteCash,
                    'qr' => $subteQr,
                    'card' => $subteCard,
                ],
                'tienda' => [
                    'name' => 'Tienda Oficial',
                    'ventas' => $tiendaVentas,
                    'efectivo' => $tiendaCash,
                    'qr' => $tiendaQr,
                    'card' => $tiendaCard,
                    'store_orders' => $tiendaStoreOrdersCount,
                    'store_units' => $tiendaStoreItemsQty,
                ],
            ],
            'nights' => $nightsData,
        ];
    }
}
