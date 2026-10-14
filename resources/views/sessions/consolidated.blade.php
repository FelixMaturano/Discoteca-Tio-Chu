@extends('layouts.app')

@section('title', 'Resumen Consolidado de Noches — ' . $summary['date_range'])

@section('content')
<style>
    /* Estilos de Impresión Ejecutiva y Administrativa */
    @media print {
        @page {
            size: letter portrait;
            margin: 12mm 15mm 15mm 15mm;
        }

        html, body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            font-size: 9.5pt !important;
            line-height: 1.35 !important;
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
        }

        /* Ocultar interfaz web, barra lateral y elementos no imprimibles */
        #main-sidebar, aside, nav, header, footer, .no-print, #flash-success-toast, button, a[href]:not(.print-include) {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        main {
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
            max-width: 100% !important;
            width: 100% !important;
            box-shadow: none !important;
            border: none !important;
        }

        /* Contenedor del documento oficial */
        .executive-doc-sheet {
            background: #ffffff !important;
            color: #000000 !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Tablas formales administrativas */
        table.exec-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 12px !important;
            page-break-inside: auto !important;
            font-size: 8.5pt !important;
        }

        table.exec-table tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }

        table.exec-table thead {
            display: table-header-group !important;
        }

        table.exec-table th, table.exec-table td {
            border: 1px solid #334155 !important;
            padding: 4.5px 7px !important;
            color: #000000 !important;
        }

        table.exec-table th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.03em !important;
            font-size: 8pt !important;
        }

        table.exec-table tfoot td {
            font-weight: 800 !important;
            background-color: #f8fafc !important;
            border-top: 2px solid #000000 !important;
            border-bottom: 2px solid #000000 !important;
        }

        /* Resaltados de balance */
        .double-underline {
            border-bottom: 3px double #000000 !important;
        }

        .avoid-break {
            page-break-inside: avoid !important;
        }
    }
</style>

<div class="space-y-4 max-w-5xl mx-auto pb-16">

    <!-- BARRA SUPERIOR DE ACCIONES (NO IMPRIMIBLE) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 bg-zinc-900 border border-zinc-800 rounded-2xl no-print shadow-lg">
        <div class="flex items-center gap-3">
            <a href="{{ route('sessions.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white rounded-xl text-xs font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                <span>Volver a Historial</span>
            </a>
            <div>
                <h2 class="text-sm font-bold text-white tracking-tight">Informe de Auditoría Consolidada</h2>
                <p class="text-[11px] text-zinc-400 font-mono">{{ $summary['date_range'] }} • {{ $summary['count'] }} noches seleccionadas</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs font-black tracking-wider uppercase rounded-xl transition-all cursor-pointer shadow-md shadow-[#F5B81C]/15 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir Reporte Oficial (PDF)</span>
            </button>
        </div>
    </div>

    <!-- FOLIO OFICIAL DE AUDITORÍA EJECUTIVA (BLANCO, FORMAL Y SERIO) -->
    <div class="executive-doc-sheet bg-white text-zinc-900 p-6 sm:p-10 rounded-2xl shadow-2xl border border-zinc-200">
        
        <!-- MEMBRETE INSTITUCIONAL EJECUTIVO -->
        <div class="border-b-2 border-black pb-4 mb-5">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-wider uppercase text-black font-sans">
                        DISCOTECA TÍO CHU
                    </h1>
                    <p class="text-[11px] uppercase tracking-widest text-zinc-700 font-bold mt-0.5">
                        Departamento de Administración, Finanzas & Control Operativo
                    </p>
                    <p class="text-[10px] text-zinc-600 mt-0.5">
                        Av. Principal, Zona Central — Sucre, Bolivia • Sistema Integrado de Gestión
                    </p>
                </div>
                <div class="text-right border border-black p-2.5 rounded bg-zinc-50">
                    <span class="block text-[9px] font-black uppercase tracking-wider text-zinc-600">Documento Oficial</span>
                    <span class="block text-xs font-black text-black font-mono">INF-CONS-{{ date('Ymd') }}</span>
                    <span class="block text-[9px] text-zinc-600 font-mono mt-0.5">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-zinc-300 text-center">
                <h2 class="text-base sm:text-lg font-black uppercase tracking-wide text-black underline decoration-2 underline-offset-4">
                    Resumen Consolidado de Jornadas Nocturnas
                </h2>
                <p class="text-xs font-bold text-zinc-800 mt-1 font-sans">
                    Acta de Liquidación y Auditoría Multisesión de Caja
                </p>
            </div>
        </div>

        <!-- CUADRO DE METADATOS ADMINISTRATIVOS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-3 bg-zinc-50 border border-zinc-300 rounded-lg text-xs mb-5">
            <div>
                <span class="block text-[9px] font-bold text-zinc-500 uppercase">Período Auditado</span>
                <span class="font-bold text-black font-mono text-[11px]">{{ $summary['date_range'] }}</span>
            </div>
            <div>
                <span class="block text-[9px] font-bold text-zinc-500 uppercase">Jornadas Consolidadas</span>
                <span class="font-bold text-black font-mono text-[11px]">{{ $summary['count'] }} noches</span>
            </div>
            <div>
                <span class="block text-[9px] font-bold text-zinc-500 uppercase">Emisión & Fecha</span>
                <span class="font-bold text-black font-mono text-[11px]">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="block text-[9px] font-bold text-zinc-500 uppercase">Moneda de Registro</span>
                <span class="font-bold text-black font-mono text-[11px]">Bolivianos (BOB - Bs.)</span>
            </div>
        </div>

        <!-- 1. RESUMEN EJECUTIVO DE RESULTADOS Y BALANCE -->
        <div class="mb-6 avoid-break">
            <div class="flex items-center justify-between pb-1.5 mb-2 border-b-2 border-black">
                <h3 class="text-xs font-black uppercase tracking-wider text-black">
                    1. Estado Financiero Consolidado (Balance Ejecutivo)
                </h3>
                <span class="text-[10px] text-zinc-600 italic">Cifras expresadas en Bolivianos</span>
            </div>

            <table class="exec-table w-full text-xs border border-black border-collapse">
                <thead>
                    <tr class="bg-zinc-100 text-black">
                        <th class="p-2 text-left border border-black font-bold uppercase text-[10px]">Concepto Contable / Rubro</th>
                        <th class="p-2 text-right border border-black font-bold uppercase text-[10px] w-36">Importe (Bs.)</th>
                        <th class="p-2 text-left border border-black font-bold uppercase text-[10px] w-56">Detalle / Composición</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-300 text-zinc-900 font-mono">
                    <tr>
                        <td class="p-2 border border-black font-bold font-sans">
                            Total Ingresos
                        </td>
                        <td class="p-2 border border-black text-right font-black text-sm">
                            Bs. {{ number_format($summary['totals']['ingresos'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-700 font-sans">
                            Efectivo en Bóveda + Cobros QR + Tarjetas POS
                        </td>
                    </tr>
                    <tr class="bg-zinc-50/50">
                        <td class="p-2 border border-black pl-5 font-sans text-zinc-800">
                            ( - ) Planilla de Pagos al Personal
                        </td>
                        <td class="p-2 border border-black text-right text-rose-700 font-bold">
                            - Bs. {{ number_format($summary['totals']['personal'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            Sueldos liquidados a trabajadores por turno
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2 border border-black pl-5 font-sans text-zinc-800">
                            ( - ) Gastos Operativos y Salidas de Caja Chica
                        </td>
                        <td class="p-2 border border-black text-right text-rose-700 font-bold">
                            - Bs. {{ number_format($summary['totals']['gastos'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            Insumos, compras de emergencia y operaciones
                        </td>
                    </tr>
                    <tr class="bg-zinc-50/50">
                        <td class="p-2 border border-black pl-5 font-sans text-zinc-800">
                            ( - ) Retención Comisión Bancaria POS (1.30%)
                        </td>
                        <td class="p-2 border border-black text-right text-rose-700 font-bold">
                            - Bs. {{ number_format($summary['totals']['card_commission'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            Comisión descontada por entidad adquirente POS
                        </td>
                    </tr>
                    <tr class="bg-zinc-100 font-bold">
                        <td class="p-2 border border-black font-sans uppercase text-[11px]">
                            Total Egresos y Retenciones Operativas:
                        </td>
                        <td class="p-2 border border-black text-right font-black text-rose-800">
                            - Bs. {{ number_format($summary['totals']['egresos'] + $summary['totals']['card_commission'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            Sueldos + Gastos + Comisiones Bancarias
                        </td>
                    </tr>
                    <tr class="bg-emerald-50 text-emerald-950 font-black border-t-2 border-black">
                        <td class="p-2.5 border border-black uppercase text-xs font-sans tracking-wide">
                            (=) Ganancia Neta en Caja (Saldo Líquido Disponible):
                        </td>
                        <td class="p-2.5 border border-black text-right text-base text-emerald-900 font-black double-underline">
                            Bs. {{ number_format($summary['totals']['neto'], 2) }}
                        </td>
                        <td class="p-2.5 border border-black text-[10px] text-emerald-800 font-sans font-bold">
                            Superávit operativo líquido disponible para gerencia
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 2. ARQUEO POR MÉTODOS DE PAGO Y DISPONIBILIDAD -->
        <div class="mb-6 avoid-break">
            <div class="flex items-center justify-between pb-1.5 mb-2 border-b-2 border-black">
                <h3 class="text-xs font-black uppercase tracking-wider text-black">
                    2. Arqueo y Conciliación por Formas de Cobro
                </h3>
                <span class="text-[10px] text-zinc-600 italic">Desglose de medios financieros</span>
            </div>

            <table class="exec-table w-full text-xs border border-black border-collapse">
                <thead>
                    <tr class="bg-zinc-100 text-black">
                        <th class="p-2 text-left border border-black font-bold uppercase text-[10px]">Canal / Método de Pago</th>
                        <th class="p-2 text-right border border-black font-bold uppercase text-[10px] w-36">Subtotal (Bs.)</th>
                        <th class="p-2 text-left border border-black font-bold uppercase text-[10px]">Detalle de Conciliación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-300 text-zinc-900 font-mono">
                    <!-- Efectivo -->
                    <tr>
                        <td class="p-2 border border-black font-bold font-sans">
                            Efectivo Físico Total en Bóveda / Caja
                        </td>
                        <td class="p-2 border border-black text-right font-black">
                            Bs. {{ number_format($summary['totals']['efectivo'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-700 font-sans">
                            B. Principal (Bs. {{ number_format($summary['bars']['principal']['efectivo'], 2) }}) • B. Subte (Bs. {{ number_format($summary['bars']['subte']['efectivo'], 2) }}) • Tienda (Bs. {{ number_format($summary['bars']['tienda']['efectivo'], 2) }})
                        </td>
                    </tr>
                    <!-- QR Yasta -->
                    <tr class="bg-zinc-50/50">
                        <td class="p-2 border border-black pl-5 font-sans">
                            Transferencias QR YASTA (Banco Unión S.A.)
                        </td>
                        <td class="p-2 border border-black text-right">
                            Bs. {{ number_format($summary['totals']['qr_yasta'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            Comprobantes verificados en cuenta Banco Unión
                        </td>
                    </tr>
                    <!-- QR Yape -->
                    <tr>
                        <td class="p-2 border border-black pl-5 font-sans">
                            Transferencias QR YAPE (Banco BCP S.A.)
                        </td>
                        <td class="p-2 border border-black text-right">
                            Bs. {{ number_format($summary['totals']['qr_yape'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            Comprobantes verificados en cuenta Banco BCP
                        </td>
                    </tr>
                    <!-- Total QR -->
                    <tr class="bg-zinc-100 font-bold">
                        <td class="p-2 border border-black font-sans uppercase text-[10px]">
                            Subtotal Recaudación por QR Electrónico:
                        </td>
                        <td class="p-2 border border-black text-right font-black text-blue-900">
                            Bs. {{ number_format($summary['totals']['qr_total'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            YASTA + YAPE acreditados
                        </td>
                    </tr>
                    <!-- Tarjeta POS Bruto -->
                    <tr>
                        <td class="p-2 border border-black font-bold font-sans">
                            Tarjetas de Débito / Crédito (Terminales POS)
                        </td>
                        <td class="p-2 border border-black text-right font-black">
                            Bs. {{ number_format($summary['totals']['card_gross'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-700 font-sans">
                            Facturación bruta emitida con vouchers POS
                        </td>
                    </tr>
                    <!-- Tarjeta POS Comisión -->
                    <tr class="bg-zinc-50/50 text-rose-800">
                        <td class="p-2 border border-black pl-5 font-sans">
                            ( - ) Tasa de Comisión Bancaria (1.30%)
                        </td>
                        <td class="p-2 border border-black text-right font-bold">
                            - Bs. {{ number_format($summary['totals']['card_commission'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            Deducción automática por uso de terminal de pago
                        </td>
                    </tr>
                    <!-- Tarjeta POS Neto -->
                    <tr class="bg-zinc-100 font-bold">
                        <td class="p-2 border border-black font-sans uppercase text-[10px]">
                            Subtotal Neto Acreditado en Cuenta Bancaria (POS):
                        </td>
                        <td class="p-2 border border-black text-right font-black text-purple-900">
                            Bs. {{ number_format($summary['totals']['card_net'], 2) }}
                        </td>
                        <td class="p-2 border border-black text-[10px] text-zinc-600 font-sans">
                            Monto efectivamente liquidado a favor de la empresa
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 3. RENDIMIENTO POR PUNTO DE VENTA (BARRAS Y TIENDA) -->
        <div class="mb-6 avoid-break">
            <div class="flex items-center justify-between pb-1.5 mb-2 border-b-2 border-black">
                <h3 class="text-xs font-black uppercase tracking-wider text-black">
                    3. Rendimiento Operativo por Punto de Venta
                </h3>
                <span class="text-[10px] text-zinc-600 italic">Desglose por barra y salón</span>
            </div>

            <table class="exec-table w-full text-xs border border-black border-collapse">
                <thead>
                    <tr class="bg-zinc-100 text-black">
                        <th class="p-2 text-left border border-black font-bold uppercase text-[10px]">Punto de Venta</th>
                        <th class="p-2 text-right border border-black font-bold uppercase text-[10px]">Efectivo (Bs.)</th>
                        <th class="p-2 text-right border border-black font-bold uppercase text-[10px]">QR (Bs.)</th>
                        <th class="p-2 text-right border border-black font-bold uppercase text-[10px]">POS Tarjeta (Bs.)</th>
                        <th class="p-2 text-right border border-black font-bold uppercase text-[10px]">Total Ventas (Bs.)</th>
                        <th class="p-2 text-center border border-black font-bold uppercase text-[10px] w-20">% Aporte</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-300 text-zinc-900 font-mono">
                    @php
                        $totV = max(0.01, (float)$summary['totals']['ventas']);
                    @endphp
                    <!-- Barra Principal -->
                    <tr>
                        <td class="p-2 border border-black font-bold font-sans">
                            Barra Principal
                            <span class="block text-[9px] text-zinc-500 font-normal">Planta Baja / Salón Principal</span>
                        </td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['principal']['efectivo'], 2) }}</td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['principal']['qr'], 2) }}</td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['principal']['card'], 2) }}</td>
                        <td class="p-2 border border-black text-right font-black">Bs. {{ number_format($summary['bars']['principal']['ventas'], 2) }}</td>
                        <td class="p-2 border border-black text-center font-bold text-zinc-700">
                            {{ number_format(($summary['bars']['principal']['ventas'] / $totV) * 100, 1) }}%
                        </td>
                    </tr>
                    <!-- Barra Subterráneo -->
                    <tr class="bg-zinc-50/50">
                        <td class="p-2 border border-black font-bold font-sans">
                            Barra Subterráneo
                            <span class="block text-[9px] text-zinc-500 font-normal">Nivel Inferior / Pista Principal</span>
                        </td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['subte']['efectivo'], 2) }}</td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['subte']['qr'], 2) }}</td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['subte']['card'], 2) }}</td>
                        <td class="p-2 border border-black text-right font-black">Bs. {{ number_format($summary['bars']['subte']['ventas'], 2) }}</td>
                        <td class="p-2 border border-black text-center font-bold text-zinc-700">
                            {{ number_format(($summary['bars']['subte']['ventas'] / $totV) * 100, 1) }}%
                        </td>
                    </tr>
                    <!-- Tienda Oficial -->
                    <tr>
                        <td class="p-2 border border-black font-bold font-sans">
                            Tienda Oficial
                            <span class="block text-[9px] text-zinc-500 font-normal">{{ $summary['bars']['tienda']['store_units'] }} unidades sueltas despachadas</span>
                        </td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['tienda']['efectivo'], 2) }}</td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['tienda']['qr'], 2) }}</td>
                        <td class="p-2 border border-black text-right">Bs. {{ number_format($summary['bars']['tienda']['card'], 2) }}</td>
                        <td class="p-2 border border-black text-right font-black">Bs. {{ number_format($summary['bars']['tienda']['ventas'], 2) }}</td>
                        <td class="p-2 border border-black text-center font-bold text-zinc-700">
                            {{ number_format(($summary['bars']['tienda']['ventas'] / $totV) * 100, 1) }}%
                        </td>
                    </tr>
                </tbody>
                <tfoot class="bg-zinc-100 font-black border-t-2 border-black font-mono">
                    <tr>
                        <td class="p-2.5 border border-black font-sans uppercase text-[10px]">
                            Total Ventas de Bebidas:
                        </td>
                        <td class="p-2.5 border border-black text-right">Bs. {{ number_format($summary['totals']['efectivo'], 2) }}</td>
                        <td class="p-2.5 border border-black text-right">Bs. {{ number_format($summary['totals']['qr_total'], 2) }}</td>
                        <td class="p-2.5 border border-black text-right">Bs. {{ number_format($summary['totals']['card_gross'], 2) }}</td>
                        <td class="p-2.5 border border-black text-right text-sm double-underline">Bs. {{ number_format($summary['totals']['ventas'], 2) }}</td>
                        <td class="p-2.5 border border-black text-center">100.0%</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- 4. PLANILLA CRONOLÓGICA NOCHE POR NOCHE -->
        <div class="mb-6 avoid-break">
            <div class="flex items-center justify-between pb-1.5 mb-2 border-b-2 border-black">
                <h3 class="text-xs font-black uppercase tracking-wider text-black">
                    4. Planilla Cronológica Detallada Noche por Noche
                </h3>
                <span class="text-[10px] text-zinc-600 italic">Auditoría individual de cada jornada nocturna</span>
            </div>

            <table class="exec-table w-full text-xs border border-black border-collapse">
                <thead>
                    <tr class="bg-zinc-100 text-black">
                        <th class="p-1.5 text-center border border-black font-bold uppercase text-[9px] w-20">Fecha</th>
                        <th class="p-1.5 text-left border border-black font-bold uppercase text-[9px] w-20">Día</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">Ventas (Bs.)</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">Efectivo (Bs.)</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">QR (Bs.)</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">POS (Bs.)</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">Personal (Bs.)</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">Gastos (Bs.)</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">Total Ingresos</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">Total Egresos</th>
                        <th class="p-1.5 text-right border border-black font-bold uppercase text-[9px]">Neto Caja</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-300 text-zinc-900 font-mono text-[10px]">
                    @foreach($summary['nights'] as $n)
                        <tr class="hover:bg-zinc-50">
                            <td class="p-1.5 border border-black text-center font-bold text-black">{{ $n['date'] }}</td>
                            <td class="p-1.5 border border-black font-sans font-semibold text-zinc-800">{{ $n['day_name'] }}</td>
                            <td class="p-1.5 border border-black text-right font-bold">{{ number_format($n['ventas'], 2) }}</td>
                            <td class="p-1.5 border border-black text-right">{{ number_format($n['efectivo'], 2) }}</td>
                            <td class="p-1.5 border border-black text-right">{{ number_format($n['qr'], 2) }}</td>
                            <td class="p-1.5 border border-black text-right">{{ number_format($n['tarjeta_bruto'], 2) }}</td>
                            <td class="p-1.5 border border-black text-right text-rose-700">{{ number_format($n['personal'], 2) }}</td>
                            <td class="p-1.5 border border-black text-right text-rose-700">{{ number_format($n['gastos'], 2) }}</td>
                            <td class="p-1.5 border border-black text-right font-semibold">{{ number_format($n['ingresos'], 2) }}</td>
                            <td class="p-1.5 border border-black text-right text-rose-800">{{ number_format($n['egresos'], 2) }}</td>
                            <td class="p-1.5 border border-black text-right font-black text-emerald-900">{{ number_format($n['neto'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-zinc-100 font-bold border-t-2 border-black font-mono text-[10px]">
                    <tr>
                        <td colspan="2" class="p-1.5 border border-black font-sans uppercase font-black">
                            TOTALES CONSOLIDADOS:
                        </td>
                        <td class="p-1.5 border border-black text-right font-black">{{ number_format($summary['totals']['ventas'], 2) }}</td>
                        <td class="p-1.5 border border-black text-right font-black">{{ number_format($summary['totals']['efectivo'], 2) }}</td>
                        <td class="p-1.5 border border-black text-right font-black">{{ number_format($summary['totals']['qr_total'], 2) }}</td>
                        <td class="p-1.5 border border-black text-right font-black">{{ number_format($summary['totals']['card_gross'], 2) }}</td>
                        <td class="p-1.5 border border-black text-right font-black text-rose-800">{{ number_format($summary['totals']['personal'], 2) }}</td>
                        <td class="p-1.5 border border-black text-right font-black text-rose-800">{{ number_format($summary['totals']['gastos'], 2) }}</td>
                        <td class="p-1.5 border border-black text-right font-black">{{ number_format($summary['totals']['ingresos'], 2) }}</td>
                        <td class="p-1.5 border border-black text-right font-black text-rose-800">{{ number_format($summary['totals']['egresos'], 2) }}</td>
                        <td class="p-1.5 border border-black text-right font-black text-emerald-950 double-underline">{{ number_format($summary['totals']['neto'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- 5. FIRMAS DE RESPONSABILIDAD Y CONFORMIDAD ADMINISTRATIVA -->
        <div class="mt-8 pt-4 border-t-2 border-black avoid-break">
            <p class="text-[10px] text-zinc-600 uppercase tracking-wider text-center font-bold mb-6 font-sans">
                Conformidad y Cierre de Auditoría — Discoteca Tío Chu
            </p>

            <div class="grid grid-cols-3 gap-6 text-center text-xs">
                <!-- Firma 1: Caja -->
                <div class="flex flex-col items-center">
                    <div class="w-full border-b border-black mb-1.5 h-12"></div>
                    <span class="font-bold text-black text-[11px] block">RESPONSABLE DE CAJA</span>
                    <span class="text-[9px] text-zinc-600 block">Recepción y Cuadre de Arqueos</span>
                </div>

                <!-- Firma 2: Administración -->
                <div class="flex flex-col items-center">
                    <div class="w-full border-b border-black mb-1.5 h-12"></div>
                    <span class="font-bold text-black text-[11px] block">ADMINISTRACIÓN OPERATIVA</span>
                    <span class="text-[9px] text-zinc-600 block">Validación de Egresos & Planillas</span>
                </div>

                <!-- Firma 3: Propietario -->
                <div class="flex flex-col items-center">
                    <div class="w-full border-b border-black mb-1.5 h-12"></div>
                    <span class="font-bold text-black text-[11px] block">DIRECCIÓN GENERAL</span>
                    <span class="text-[9px] text-zinc-600 block">V°B° Don Ludo / Propietario</span>
                </div>
            </div>

            <!-- Cláusula legal final -->
            <p class="text-[9px] text-zinc-500 text-center mt-6 italic font-sans border-t border-zinc-300 pt-2">
                Documento oficial generado automáticamente por el Sistema Operativo de Discoteca Tío Chu. Todos los datos financieros han sido cruzados y conciliados directamente con las cuentas bancarias de Banco Unión (YASTA), Banco BCP (YAPE), terminales POS y el libro de ingresos físicos en bóveda.
            </p>
        </div>

    </div>

</div>
@endsection
