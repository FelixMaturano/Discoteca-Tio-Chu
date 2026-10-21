@extends('layouts.app')

@section('title', 'Resumen General')

@section('content')
@php
    $salesPrincipal = $session ? (float) $session->barSales()->whereIn('bar_name', ['Barra Principal', 'Barra Kelly (Principal)', 'Principal', 'Kelly'])->sum('subtotal') : 0;
    $salesSubte = $session ? (float) $session->barSales()->whereIn('bar_name', ['Barra Subterráneo', 'Barra Ariel (Subte)', 'Subterráneo', 'Subterraneo', 'Subte', 'Ariel'])->sum('subtotal') : 0;
    $salesTienda = $session ? ((float) $session->barSales()->whereIn('bar_name', ['Tienda', 'tienda'])->sum('subtotal') + (float) $session->storeSales()->sum('total_price')) : 0;
    $totalSales = $salesPrincipal + $salesSubte + $salesTienda;

    $totalExpenses = $closing->total_expenses ?? 0;

    $staffPaid = $closing->total_staff_paid ?? 0;

    $barPayments = [
        'principal' => [
            'qr' => $session ? (float) $session->qrPayments()->whereIn('point_of_sale', ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'])->sum('amount') : 0,
            'card' => $session ? (float) $session->invoices()->whereIn('bar_name', ['Principal', 'Barra Principal', 'Barra Kelly (Principal)', 'Kelly'])->where('payment_method', 'tarjeta')->sum('amount') : 0,
            'card_net' => $session ? (float) $session->invoices()->whereIn('bar_name', ['Principal', 'Barra Principal', 'Barra Kelly (Principal)', 'Kelly'])->where('payment_method', 'tarjeta')->sum('net_amount') : 0,
        ],
        'subte' => [
            'qr' => $session ? (float) $session->qrPayments()->whereIn('point_of_sale', ['Subte', 'Barra Subte', 'Subterráneo', 'Subterraneo', 'Barra Ariel (Subte)', 'Barra Subterráneo', 'Ariel'])->sum('amount') : 0,
            'card' => $session ? (float) $session->invoices()->whereIn('bar_name', ['Subterráneo', 'Subterraneo', 'Subte', 'Barra Ariel (Subte)', 'Barra Subterráneo', 'Ariel'])->where('payment_method', 'tarjeta')->sum('amount') : 0,
            'card_net' => $session ? (float) $session->invoices()->whereIn('bar_name', ['Subterráneo', 'Subterraneo', 'Subte', 'Barra Ariel (Subte)', 'Barra Subterráneo', 'Ariel'])->where('payment_method', 'tarjeta')->sum('net_amount') : 0,
        ],
        'tienda' => [
            'qr' => $session ? (float) $session->qrPayments()->whereIn('point_of_sale', ['Tienda', 'tienda'])->sum('amount') : 0,
            'card' => $session ? (float) $session->invoices()->whereIn('bar_name', ['Tienda', 'tienda'])->where('payment_method', 'tarjeta')->sum('amount') : 0,
            'card_net' => $session ? (float) $session->invoices()->whereIn('bar_name', ['Tienda', 'tienda'])->where('payment_method', 'tarjeta')->sum('net_amount') : 0,
        ],
    ];
    $barPayments['principal']['cash'] = max(0, $salesPrincipal - $barPayments['principal']['qr'] - $barPayments['principal']['card']);
    $barPayments['subte']['cash'] = max(0, $salesSubte - $barPayments['subte']['qr'] - $barPayments['subte']['card']);
    $barPayments['tienda']['cash'] = max(0, $salesTienda - $barPayments['tienda']['qr'] - $barPayments['tienda']['card']);

    $qrTotal = $barPayments['principal']['qr'] + $barPayments['subte']['qr'] + $barPayments['tienda']['qr'];
    $totalQrYasta = $session ? (float) $session->qrPayments()->where('bank_app', 'YASTA')->sum('amount') : 0;
    $totalQrYape = $session ? (float) $session->qrPayments()->where('bank_app', 'YAPE')->sum('amount') : 0;
    $posGross = $barPayments['principal']['card'] + $barPayments['subte']['card'] + $barPayments['tienda']['card'];
    $posNet = $barPayments['principal']['card_net'] + $barPayments['subte']['card_net'] + $barPayments['tienda']['card_net'];
    $posCommission = $posGross - $posNet;
    $barTotal = $totalSales;
    $netCash = $barPayments['principal']['cash'] + $barPayments['subte']['cash'] + $barPayments['tienda']['cash'];
    $totalIncome = $posGross + $qrTotal + $netCash;

    $beerPayments = $session ? $session->beerPayments()->orderByDesc('id')->get() : collect();
    $beerPaymentCash = $beerPayments->where('payment_method', 'efectivo')->sum('amount');
    $beerPaymentYasta = $beerPayments->where('payment_method', 'qr')->where('qr_provider', 'YASTA')->sum('amount');
    $beerPaymentYape = $beerPayments->where('payment_method', 'qr')->where('qr_provider', 'YAPE')->sum('amount');
    $beerPaymentQrTotal = $beerPaymentYasta + $beerPaymentYape;
    $beerPaymentTotal = $beerPaymentCash + $beerPaymentQrTotal;
    $qrRemaining = $qrTotal - $beerPaymentQrTotal;
    $yastaRemaining = $totalQrYasta - $beerPaymentYasta;
    $yapeRemaining = $totalQrYape - $beerPaymentYape;
    $reportedCash = $closing && isset($closing->reported_cash) ? (float) $closing->reported_cash : 0;
    $staffTotalDue = $session ? (float) $session->staffAttendances()->sum('pay_amount') : 0;
    $staffAlreadyPaid = $session ? (float) $session->staffAttendances()->where('is_paid', true)->sum('pay_amount') : 0;
    $cashBeforeBeer = $barTotal - ($posGross + $qrTotal);
    $netCash = $barTotal - ($posGross + $qrRemaining + ($beerPayments->isNotEmpty() ? $staffTotalDue : 0));
    $cashRemaining = $netCash - $beerPaymentCash;
    $cashComparisonTotal = $reportedCash + $posGross + $qrRemaining + $staffTotalDue;
    $cashDifference = $barTotal - $cashComparisonTotal;
@endphp

<div class="space-y-4 max-w-7xl mx-auto w-full pb-8">

    <!-- ========================================================
         1. CABECERA PRINCIPAL: ESTADO DE NOCHE & SELECTOR
         ======================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-3.5 rounded-2xl bg-[#09090b] border border-zinc-800/80 shadow-sm {{ session('animate_entrance') ? 'animate-entrance-header' : '' }}">
        
        <!-- Izquierda: Estado de la Noche & Selector -->
        <div class="flex flex-wrap items-center gap-3">
            @if($session)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800">
                    <span class="w-2 h-2 rounded-full {{ $session->isOpen() ? 'bg-emerald-500' : 'bg-zinc-600' }}"></span>
                    <span class="text-xs font-black uppercase tracking-wider {{ $session->isOpen() ? 'text-zinc-200' : 'text-zinc-500' }}">
                        {{ $session->isOpen() ? 'Noche Abierta' : 'Noche Cerrada' }}
                    </span>
                </div>

                <div class="flex items-center gap-1.5 text-sm font-black text-white uppercase tracking-tight font-sans">
                    <span>{{ $session->day_name }}</span>
                    <span class="text-zinc-500">•</span>
                    <span class="font-mono text-zinc-300">{{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}</span>
                </div>
            @endif

            <!-- Selector de Noche en Píldora -->
            @if($allSessions->isNotEmpty())
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs hover:border-[#F5B81C] transition-colors">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <select name="session_id" onchange="this.form.submit()" 
                                class="bg-transparent border-0 text-xs font-bold text-zinc-300 focus:outline-none cursor-pointer pr-1">
                            @foreach($allSessions as $s)
                                <option value="{{ $s->id }}" {{ $session && $session->id === $s->id ? 'selected' : '' }} class="bg-zinc-950 text-white">
                                    {{ $s->day_name }} {{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }} ({{ $s->isOpen() ? 'En Vivo' : 'Cerrada' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
        </div>

        <!-- Derecha: Botón Aperturar Noche -->
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('sessions.create') }}" 
               class="px-4 py-2 rounded-xl bg-[#F5B81C] text-black font-black text-xs hover:bg-[#e5ac18] transition-all flex items-center gap-1.5 shrink-0 shadow-sm active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Aperturar Noche</span>
            </a>
        </div>

    </div>

    @if(!$session)
        <!-- Estado Vacío -->
        <div class="p-12 text-center rounded-2xl bg-[#09090b] border border-zinc-800/80 flex flex-col items-center justify-center {{ session('animate_entrance') ? 'animate-entrance-card-1' : '' }}">
            <div class="w-12 h-12 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-600 mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                </svg>
            </div>
            <h3 class="text-sm font-black uppercase tracking-wider text-white">No hay ninguna noche abierta o seleccionada</h3>
            <p class="text-xs text-zinc-400 mt-1 mb-5">Apertura una nueva jornada para ver el resumen de caja, ventas y personal.</p>
            <a href="{{ route('sessions.create') }}" class="px-5 py-2.5 rounded-xl bg-[#F5B81C] text-black font-black text-xs hover:bg-[#e5ac18] transition-all active:scale-95 shadow-sm">
                + Aperturar Nueva Noche
            </a>
        </div>
    @else

        <!-- ========================================================
             2. 4 TARJETAS KPI RESUMEN: MINIMALISTAS & ELEGANTES
             ======================================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            
            <!-- TARJETA 1: Venta Total en Barras -->
            {{-- 
            <div class="theme-card p-4 rounded-2xl border theme-border flex items-center justify-between shadow-sm {{ session('animate_entrance') ? 'animate-entrance-card-1' : '' }}">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block truncate">
                        Venta en Barras
                    </span>
                    <div class="text-2xl font-black text-white font-mono tracking-tight mt-1 truncate">
                        <span class="text-xs font-sans font-bold text-[#F5B81C] mr-0.5">Bs.</span>{{ number_format($barTotal, 2) }}
                    </div>
                    <span class="text-[10px] text-zinc-500 font-medium block mt-0.5 truncate">Principal y Subterráneo</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-300 shrink-0 ml-3">
                    <svg class="w-5 h-5 text-[#F5B81C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 22h8M12 15v7M19 3l-7 8-7-8h14z"/>
                    </svg>
                </div>
            </div>

            <!-- TARJETA 2: Cobros por Tarjeta (POS) -->
            <div class="theme-card p-4 rounded-2xl border theme-border flex items-center justify-between shadow-sm {{ session('animate_entrance') ? 'animate-entrance-card-2' : '' }}">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block truncate">
                        Tarjetas (Bruto)
                    </span>
                    <div class="text-2xl font-black text-white font-mono tracking-tight mt-1 truncate">
                        <span class="text-xs font-sans font-bold text-[#F5B81C] mr-0.5">Bs.</span>{{ number_format($posGross, 2) }}
                    </div>
                    <span class="text-[10px] text-zinc-500 font-medium block mt-0.5 truncate">
                        Neto con descuento: Bs. {{ number_format($posNet, 2) }}
                    </span>
                    <span class="text-[9px] text-zinc-600 font-medium block mt-0.5 truncate">
                        Descuento: Bs. {{ number_format($posCommission, 2) }}
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-300 shrink-0 ml-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="20" height="14" x="2" y="5" rx="2"/>
                        <line x1="2" x2="22" y1="10" y2="10"/>
                    </svg>
                </div>
            </div>
            --}}

            <!-- TARJETA 3: Cobros por QR -->
            <div class="theme-card p-4 rounded-2xl border theme-border flex items-center justify-between shadow-sm {{ session('animate_entrance') ? 'animate-entrance-card-3' : '' }}">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block truncate">
                        Cobros por QR
                    </span>
                    <div class="text-2xl font-black text-white font-mono tracking-tight mt-1 truncate">
                        <span class="text-xs font-sans font-bold text-[#F5B81C] mr-0.5">Bs.</span>{{ number_format($qrTotal, 2) }}
                    </div>
                    <span class="text-[10px] text-zinc-500 font-medium block mt-0.5 truncate">Yasta y Yape</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-300 shrink-0 ml-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="6" height="6" x="3" y="3" rx="1"/>
                        <rect width="6" height="6" x="15" y="3" rx="1"/>
                        <rect width="6" height="6" x="3" y="15" rx="1"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 15h3m3 0h-3v3m0 3v-3m3 3v.01M10 7v3a2 2 0 01-2 2H5"/>
                    </svg>
                </div>
            </div>

            <!-- TARJETA 4: Dinero en Caja (Balance Neto) -->
            <div class="theme-card p-4 rounded-2xl border theme-border flex items-center justify-between shadow-sm {{ session('animate_entrance') ? 'animate-entrance-card-4' : '' }}">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block truncate">
                        Efectivo en Caja
                    </span>
                    <div class="text-2xl font-black font-mono tracking-tight mt-1 truncate {{ $netCash >= 0 ? 'text-white' : 'text-rose-400' }}">
                        <span class="text-xs font-sans font-bold mr-0.5 {{ $netCash >= 0 ? 'text-[#F5B81C]' : 'text-rose-400' }}">Bs.</span>{{ number_format($netCash, 2) }}
                    </div>
                    <span class="text-[10px] font-medium block mt-0.5 truncate {{ $netCash >= 0 ? 'text-zinc-500' : 'text-rose-400' }}">
                        {{ $netCash >= 0 ? 'Plata a favor en mano' : 'Faltante de caja' }}
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center shrink-0 ml-3 {{ $netCash >= 0 ? 'text-zinc-300' : 'text-rose-400' }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="3" rx="3"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.5 9.5-5 5"/>
                    </svg>
                </div>
            </div>

        </div>

        <!-- ========================================================
             3. CUADRO OPERATIVO: DISTRIBUCIÓN POR BARRA Y CUENTAS
             ======================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            <!-- Columna Izquierda (4 Cols): Ventas por Barra -->
            <div class="theme-card lg:col-span-4 p-5 rounded-2xl border theme-border flex flex-col justify-between shadow-sm {{ session('animate_entrance') ? 'animate-entrance-grid-left' : '' }}">
                <div class="flex items-center justify-between border-b theme-border pb-3 mb-3">
                    <h2 class="text-xs font-black text-white uppercase tracking-wider font-sans">
                        Ventas por Barra
                    </h2>
                    <a href="{{ route('sales.index', ['session_id' => $session->id]) }}" 
                       class="text-xs font-bold text-[#F5B81C] hover:underline flex items-center gap-1">
                        <span>Ver Barras</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="space-y-2.5 flex-1">
                    <!-- Barra Principal -->
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-xs text-white block">Barra Principal</span>
                            <span class="text-[10px] text-zinc-500 font-medium">Piso Principal</span>
                        </div>
                        <span class="font-black text-white font-mono text-sm">
                            Bs. {{ number_format($salesPrincipal, 2) }}
                        </span>
                    </div>
                    <div class="mt-1 text-[10px] text-zinc-500 font-mono">
                        QR Bs. {{ number_format($barPayments['principal']['qr'], 2) }} · Tarjeta Bs. {{ number_format($barPayments['principal']['card'], 2) }} · Efectivo Bs. {{ number_format($barPayments['principal']['cash'], 2) }}
                    </div>

                    <!-- Barra Subterráneo -->
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-xs text-white block">Barra Subterráneo</span>
                            <span class="text-[10px] text-zinc-500 font-medium">Subterráneo</span>
                        </div>
                        <span class="font-black text-white font-mono text-sm">
                            Bs. {{ number_format($salesSubte, 2) }}
                        </span>
                    </div>
                    <div class="mt-1 text-[10px] text-zinc-500 font-mono">
                        QR Bs. {{ number_format($barPayments['subte']['qr'], 2) }} · Tarjeta Bs. {{ number_format($barPayments['subte']['card'], 2) }} · Efectivo Bs. {{ number_format($barPayments['subte']['cash'], 2) }}
                    </div>

                    <!-- Tienda (Unidades Sueltas & Entrada) -->
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-xs text-white block">Tienda</span>
                            <span class="text-[10px] text-zinc-500 font-medium">Unidades Sueltas / Entrada</span>
                        </div>
                        <span class="font-black text-white font-mono text-sm">
                            Bs. {{ number_format($salesTienda, 2) }}
                        </span>
                    </div>
                    <div class="mt-1 text-[10px] text-zinc-500 font-mono">
                        QR Bs. {{ number_format($barPayments['tienda']['qr'], 2) }} · Tarjeta Bs. {{ number_format($barPayments['tienda']['card'], 2) }} · Efectivo Bs. {{ number_format($barPayments['tienda']['cash'], 2) }}
                    </div>
                </div>

                <!-- Total Barras -->
                <div class="pt-3.5 mt-3 border-t theme-border flex items-center justify-between">
                    <span class="text-zinc-400 font-bold uppercase tracking-wider text-[11px]">Total Barras</span>
                    <span class="font-black text-[#F5B81C] font-mono text-base">Bs. {{ number_format($barTotal, 2) }}</span>
                </div>
            </div>

            <!-- Columna Derecha (8 Cols): Balance de Noche (Ingresos vs Egresos) -->
            <div class="theme-card lg:col-span-8 p-5 rounded-2xl border theme-border flex flex-col justify-between shadow-sm {{ session('animate_entrance') ? 'animate-entrance-grid-right' : '' }}">
                
                <div class="flex items-center justify-between border-b border-zinc-800/80 pb-3 mb-3">
                    <h2 class="text-xs font-black text-white uppercase tracking-wider font-sans">
                        Cuentas de la Noche
                    </h2>
                    <a href="{{ route('closing.index', ['session_id' => $session->id]) }}" 
                       class="text-xs font-bold text-[#F5B81C] hover:underline flex items-center gap-1">
                        <span>Ver Cierre</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <!-- Dos Columnas: Entradas vs Salidas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 flex-1">
                    
                    <!-- Lado 1: Ingresos -->
                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex flex-col justify-between space-y-2.5">
                        <div class="flex items-center justify-between border-b border-zinc-800 pb-2">
                            <span class="text-[11px] font-bold text-zinc-300 uppercase tracking-wider">Ingresos de Noche</span>
                            <span class="font-black text-white font-mono text-xs">+ Bs. {{ number_format($totalIncome, 2) }}</span>
                        </div>

                        <div class="space-y-1.5 text-xs flex-1">
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Ventas en Barras</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($barTotal, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Tarjetas (Bruto)</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($posGross, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Tarjetas (Neta con descuento)</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($posNet, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Cobros QR Yape</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($totalQrYape, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Cobros QR Yasta</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($totalQrYasta, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">QR Total</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($qrTotal, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Efectivo en Caja</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($netCash, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Tienda / Entrada</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($salesTienda, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lado 2: Egresos y Pagos -->
                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex flex-col justify-between space-y-2.5">
                        <div class="flex items-center justify-between border-b border-zinc-800 pb-2">
                            <span class="text-[11px] font-bold text-zinc-300 uppercase tracking-wider">Egresos y Pagos</span>
                            <span class="font-black text-rose-400 font-mono text-xs">- Bs. {{ number_format($totalExpenses, 2) }}</span>
                        </div>

                        <div class="space-y-1.5 text-xs flex-1">
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Sueldos del Personal</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($staffTotalDue, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Gastos y Compras</span>
                                <span class="font-mono font-bold text-zinc-200">Bs. {{ number_format($totalExpenses, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-400 font-medium">Descuento Tarjetas</span>
                                <span class="font-mono font-bold text-zinc-400">Bs. {{ number_format($posCommission, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-0.5">
                                <span class="text-zinc-500 font-medium">Encargado</span>
                                <span class="font-bold text-zinc-400">{{ Auth::user()->name ?? 'Don Ludo' }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Barra Inferior de Efectivo en Mano -->
                <div class="pt-3.5 mt-3 border-t border-zinc-800/80 flex items-center justify-between">
                    <span class="font-bold text-zinc-400 uppercase tracking-wider text-[11px]">Efectivo Final en Caja</span>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $netCash >= 0 ? 'bg-zinc-950 text-zinc-300 border border-zinc-800' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }}">
                            {{ $netCash >= 0 ? 'En mano' : 'Faltante' }}
                        </span>
                        <span class="font-black font-mono text-base {{ $netCash >= 0 ? 'text-[#F5B81C]' : 'text-rose-400' }}">
                            Bs. {{ number_format($netCash, 2) }}
                        </span>
                    </div>
                </div>

            </div>

        </div>

        <!-- ========================================================
             4. PAGO DE CERVEZA & QR RESTANTE
             ======================================================== -->
        <div class="grid grid-cols-1 xl:grid-cols-[1.2fr_1fr] gap-4">
            {{--  <div class="theme-card p-5 rounded-2xl border theme-border shadow-sm {{ session('animate_entrance') ? 'animate-entrance-card-4' : '' }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-zinc-800/80">
                    <div>
                        <h2 class="text-xs font-black text-white uppercase tracking-wider font-sans">Pago de Cerveza</h2>
                        <p class="text-[10px] text-zinc-500 mt-1">Descuenta del efectivo o QR según el método seleccionado</p>
                    </div>
                    <button type="button" data-open-beer-modal class="px-3.5 py-2 rounded-xl bg-[#F5B81C] text-black font-black text-[11px] hover:bg-[#e5ac18] transition-all">
                        Pagar Cerveza
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Pago Efectivo</div>
                        <div class="mt-2 text-xl font-black text-white font-mono">Bs. {{ number_format($beerPaymentCash, 2) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Pago Yasta</div>
                        <div class="mt-2 text-xl font-black text-white font-mono">Bs. {{ number_format($beerPaymentYasta, 2) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Pago Yape</div>
                        <div class="mt-2 text-xl font-black text-white font-mono">Bs. {{ number_format($beerPaymentYape, 2) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">QR Restante</div>
                        <div class="mt-2 text-xl font-black text-[#F5B81C] font-mono">Bs. {{ number_format($qrRemaining, 2) }}</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Efectivo restante</div>
                        <div class="mt-2 text-lg font-black text-white font-mono">Bs. {{ number_format($cashRemaining, 2) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Yasta restante</div>
                        <div class="mt-2 text-lg font-black text-white font-mono">Bs. {{ number_format($yastaRemaining, 2) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Yape restante</div>
                        <div class="mt-2 text-lg font-black text-white font-mono">Bs. {{ number_format($yapeRemaining, 2) }}</div>
                    </div>
                </div>

                @if($beerPayments->isNotEmpty())
                    <div class="mt-5 space-y-2">
                        <div class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Historial</div>
                        @foreach($beerPayments as $beerPayment)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-zinc-950 border border-zinc-800/80 px-3 py-2.5">
                                <div>
                                    <div class="text-sm font-bold text-white">
                                        {{ $beerPayment->payment_method === 'qr' ? ($beerPayment->qr_provider ?? 'QR') : 'Efectivo' }}
                                    </div>
                                    <div class="text-[10px] text-zinc-500">
                                        {{ $beerPayment->payment_method === 'qr' ? 'Pago QR' : 'Pago en efectivo' }} ·
                                        Bs. {{ number_format($beerPayment->amount, 2) }}
                                        @if($beerPayment->notes)
                                            · {{ $beerPayment->notes }}
                                        @endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('beerPayments.destroy', $beerPayment) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[10px] font-bold text-rose-400 hover:text-rose-300 transition-colors">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-5 rounded-xl border border-dashed border-zinc-700 bg-zinc-950/60 px-3 py-3 text-[11px] text-zinc-500 text-center">
                        Sin pagos de cerveza registrados aún.
                    </div>
                @endif
            </div>--}}

            <div class="theme-card p-5 rounded-2xl border theme-border shadow-sm {{ session('animate_entrance') ? 'animate-entrance-card-4' : '' }}">
                <div class="flex items-center justify-between gap-3 pb-3 border-b border-zinc-800/80">
                    <div>
                        <h2 class="text-xs font-black text-white uppercase tracking-wider font-sans">Efectivo en mano</h2>
                        <p class="text-[10px] text-zinc-500 mt-1">Con este valor se compara el cierre final</p>
                    </div>
                    <button type="button" data-open-cash-modal class="px-3.5 py-2 rounded-xl bg-[#F5B81C] text-black font-black text-[11px] hover:bg-[#e5ac18] transition-all">
                        Efectivo real
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Efectivo reportado</div>
                        <div class="mt-2 text-2xl font-black text-white font-mono">Bs. {{ number_format($reportedCash, 2) }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                            <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Tarjetas</div>
                            <div class="mt-2 text-lg font-black text-white font-mono">Bs. {{ number_format($posGross, 2) }}</div>
                            <div class="mt-1 text-[10px] text-zinc-500">Neta: Bs. {{ number_format($posNet, 2) }}</div>
                        </div>
                        <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                            <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">QR restante</div>
                            <div class="mt-2 text-lg font-black text-white font-mono">Bs. {{ number_format($qrRemaining, 2) }}</div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Personal</div>
                        <div class="mt-2 text-lg font-black text-white font-mono">Bs. {{ number_format($staffTotalDue, 2) }}</div>
                        <div class="mt-1 text-[10px] text-zinc-500">Ya pagado: Bs. {{ number_format($staffAlreadyPaid, 2) }}</div>
                    </div>

                    <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-bold">Total cierre calculado</div>
                        <div class="mt-2 text-xl font-black text-[#F5B81C] font-mono">Bs. {{ number_format($cashComparisonTotal, 2) }}</div>
                    </div>

                    <div class="p-3 rounded-xl {{ $cashDifference > 0 ? 'bg-rose-500/10 border border-rose-500/30' : 'bg-emerald-500/10 border border-emerald-500/30' }}">
                        <div class="text-[10px] uppercase tracking-wider font-bold {{ $cashDifference > 0 ? 'text-rose-300' : 'text-emerald-300' }}">
                            {{ $cashDifference > 0 ? 'Faltante' : 'Sobrante' }}
                        </div>
                        <div class="mt-2 text-2xl font-black font-mono {{ $cashDifference > 0 ? 'text-rose-300' : 'text-emerald-300' }}">
                            Bs. {{ number_format(abs($cashDifference), 2) }}
                        </div>
                        <div class="text-[10px] {{ $cashDifference > 0 ? 'text-rose-300' : 'text-emerald-300' }} mt-1">
                            Comparado con ventas de barras: Bs. {{ number_format($barTotal, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div id="beer-payment-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4">
            <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-[#09090b] p-5 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-800/80">
                    <h3 class="text-sm font-black uppercase tracking-wider text-white">Pagar cerveza</h3>
                    <button type="button" data-close-beer-modal class="text-zinc-400 hover:text-white">✕</button>
                </div>

                <form method="POST" action="{{ route('beerPayments.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="night_session_id" value="{{ $session->id }}">

                    <div>
                        <label for="beer-payment-method" class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Método de pago</label>
                        <select id="beer-payment-method" name="payment_method" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-[#F5B81C] focus:outline-none">
                            <option value="efectivo">Efectivo</option>
                            <option value="qr">QR</option>
                        </select>
                    </div>

                    <div id="beer-payment-qr-provider" class="hidden">
                        <label for="beer-qr-provider" class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Aplicación QR</label>
                        <select id="beer-qr-provider" name="qr_provider" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-[#F5B81C] focus:outline-none">
                            <option value="YASTA">YASTA</option>
                            <option value="YAPE">YAPE</option>
                        </select>
                    </div>

                    <div>
                        <label for="beer-payment-amount" class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Monto</label>
                        <input id="beer-payment-amount" type="number" name="amount" min="0.01" step="0.01" required class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-[#F5B81C] focus:outline-none" placeholder="0.00">
                    </div>

                    <div>
                        <label for="beer-payment-notes" class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Observación</label>
                        <input id="beer-payment-notes" type="text" name="notes" maxlength="255" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-[#F5B81C] focus:outline-none" placeholder="Ej: cerveza para staff">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" data-close-beer-modal class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-xs font-bold text-zinc-300 hover:border-zinc-600">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#F5B81C] px-3 py-2 text-xs font-black text-black hover:bg-[#e5ac18]">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div> --}}

        <div id="cash-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4">
            <div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-[#09090b] p-5 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-800/80">
                    <h3 class="text-sm font-black uppercase tracking-wider text-white">Efectivo en mano</h3>
                    <button type="button" data-close-cash-modal class="text-zinc-400 hover:text-white">✕</button>
                </div>

                <form method="POST" action="{{ route('cash.reported.update') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="night_session_id" value="{{ $session->id }}">
                    <div>
                        <label for="reported-cash-amount" class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Monto real en caja</label>
                        <input id="reported-cash-amount" type="number" name="reported_cash" min="0" step="0.01" value="{{ number_format($reportedCash, 2, '.', '') }}" required class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-[#F5B81C] focus:outline-none" placeholder="0.00">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" data-close-cash-modal class="rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2 text-xs font-bold text-zinc-300 hover:border-zinc-600">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-xl bg-[#F5B81C] px-3 py-2 text-xs font-black text-black hover:bg-[#e5ac18]">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const beerModal = document.getElementById('beer-payment-modal');
                const cashModal = document.getElementById('cash-modal');
                {{--  const openBeerButtons = document.querySelectorAll('[data-open-beer-modal]');
                const closeBeerButtons = document.querySelectorAll('[data-close-beer-modal]');--}}
                const openCashButtons = document.querySelectorAll('[data-open-cash-modal]');
                const closeCashButtons = document.querySelectorAll('[data-close-cash-modal]');
                {{--  const methodSelect = document.getElementById('beer-payment-method');
                const qrProviderWrap = document.getElementById('beer-payment-qr-provider');

                function toggleBeerPaymentFields() {
                    const isQr = methodSelect.value === 'qr';
                    qrProviderWrap.classList.toggle('hidden', !isQr);
                    if (!isQr) {
                        const provider = document.getElementById('beer-qr-provider');
                        provider.value = 'YASTA';
                    }
                }

                openBeerButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        beerModal.classList.remove('hidden');
                        beerModal.classList.add('flex');
                    });
                });

                closeBeerButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        beerModal.classList.add('hidden');
                        beerModal.classList.remove('flex');
                    });
                });--}}

                openCashButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        cashModal.classList.remove('hidden');
                        cashModal.classList.add('flex');
                    });
                });

                closeCashButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        cashModal.classList.add('hidden');
                        cashModal.classList.remove('flex');
                    });
                });

                {{--  methodSelect.addEventListener('change', toggleBeerPaymentFields);
                toggleBeerPaymentFields();--}}
            });
        </script>

    @endif

</div>
@endsection
