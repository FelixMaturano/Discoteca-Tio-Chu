@extends('layouts.app')

@section('title', 'Cierre de Caja')

@section('content')

<style>
    /* Ocultar flechas de números nativas */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
    }
    input[type=number] {
        -moz-appearance: textfield !important;
        appearance: textfield !important;
    }

    @media print {
        #main-sidebar, header, nav, .print-hide,
        button, form, select,
        #staff-drawer, #staff-drawer-backdrop {
            display: none !important;
        }

        body, main, .max-w-7xl {
            background: #ffffff !important;
            color: #000000 !important;
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .print-only {
            display: block !important;
        }

        .theme-card, .bg-zinc-950, .bg-\[\#09090b\] {
            background: #ffffff !important;
            border: 1px solid #e4e4e7 !important;
            color: #000000 !important;
            box-shadow: none !important;
        }

        .text-white, .text-zinc-200, .text-zinc-300, .text-zinc-400 {
            color: #18181b !important;
        }

        .text-\[\#F5B81C\] {
            color: #b45309 !important;
        }

        .text-emerald-400 {
            color: #047857 !important;
        }

        .text-rose-400 {
            color: #b91c1c !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto w-full space-y-4 pb-10">

    <!-- ========================================================
         1. CABECERA: TÍTULO, SELECTOR DE NOCHE Y ACCIÓN DE CIERRE
         ======================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-5 py-4 rounded-2xl bg-[#09090b] border border-zinc-800/80 shadow-sm">
        
        <!-- Izquierda: Título y Noche Activa -->
        <div class="flex flex-wrap items-center gap-3.5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-[#F5B81C]/10 border border-[#F5B81C]/30 flex items-center justify-center text-[#F5B81C]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="3" rx="3"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.5 9.5-5 5"/>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white font-sans">
                    Cierre de Caja
                </h1>
            </div>

            @if($session)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800">
                    <span class="w-2 h-2 rounded-full {{ $session->isOpen() ? 'bg-emerald-500' : 'bg-zinc-600' }}"></span>
                    <span class="text-xs font-black uppercase tracking-wider {{ $session->isOpen() ? 'text-zinc-200' : 'text-zinc-500' }}">
                        {{ $session->isOpen() ? 'Noche Abierta' : 'Noche Cerrada' }}
                    </span>
                </div>

                <div class="flex items-center gap-2 text-sm font-bold text-zinc-300 font-sans">
                    <span class="uppercase text-white tracking-wide">{{ $session->day_name }}</span>
                    <span class="text-zinc-600 font-bold">•</span>
                    <span class="font-mono text-zinc-300 font-bold text-sm">{{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}</span>
                </div>
            @endif

            <!-- Selector de Noche en Píldora -->
            @if($allSessions->isNotEmpty())
                <form method="GET" action="{{ route('closing.index') }}" class="flex items-center">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs hover:border-[#F5B81C] transition-colors shadow-sm">
                        <svg class="w-4 h-4 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <select name="session_id" id="session_id" onchange="this.form.submit()" 
                                class="bg-transparent border-0 text-xs sm:text-sm font-bold text-zinc-200 focus:outline-none cursor-pointer pr-1">
                            @foreach($allSessions as $s)
                                <option value="{{ $s->id }}" {{ $session && $session->id === $s->id ? 'selected' : '' }} class="bg-zinc-950 text-white font-sans">
                                    {{ $s->day_name }} {{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }} ({{ $s->isOpen() ? 'En Vivo' : 'Cerrada' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
        </div>

        <!-- Derecha: Botones Imprimir y Cerrar/Reabrir Noche -->
        <div class="flex items-center gap-2 shrink-0">
            @if($session)
                <a href="{{ route('sessions.index') }}" 
                   class="px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-white font-bold text-xs sm:text-sm transition-all flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="hidden md:inline">Historial & Consolidado</span>
                </a>

                <button type="button" onclick="window.print()" 
                        class="px-3.5 py-2 rounded-xl bg-zinc-950 border border-zinc-800 hover:border-[#F5B81C] text-zinc-300 hover:text-white font-bold text-xs sm:text-sm transition-all cursor-pointer active:scale-95 flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-[#F5B81C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Imprimir Cierre</span>
                </button>

                @if($session->isOpen())
                    <form method="POST" action="{{ route('closing.close', $session) }}" onsubmit="return confirm('¿Seguro que deseas dar por cerrada esta noche de atención?');">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 hover:bg-rose-500 hover:text-white font-bold text-xs sm:text-sm transition-all cursor-pointer active:scale-95 flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>Cerrar Noche Definitiva</span>
                        </button>
                    </form>
                @else
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs sm:text-sm text-zinc-400 font-semibold">
                            <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>Noche Cerrada</span>
                        </div>
                        <form method="POST" action="{{ route('closing.reopen', $session) }}" onsubmit="return confirm('¿Deseas reabrir esta noche para editar inventario o ventas?');">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 rounded-xl bg-zinc-950 border border-zinc-800 hover:border-[#F5B81C] text-zinc-300 hover:text-white font-bold text-xs sm:text-sm transition-all cursor-pointer active:scale-95 shadow-sm">
                                Reabrir Noche
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        </div>

    </div>

    @if($session)
        <!-- CABECERA EXCLUSIVA PARA IMPRESIÓN (OCULTA EN PANTALLA) -->
        <div class="hidden print-only text-center pb-4 mb-4 border-b-2 border-black font-sans">
            <h1 class="text-2xl font-black uppercase tracking-tight text-black">DISCOTECA TÍO CHU</h1>
            <h2 class="text-sm font-bold uppercase tracking-wider text-zinc-700 mt-0.5">ACTA OFICIAL DE CIERRE DE CAJA</h2>
            <div class="flex items-center justify-center gap-3 text-xs text-zinc-600 mt-1">
                <span>Noche: <strong class="uppercase text-black">{{ $session->day_name }}</strong></span>
                <span>•</span>
                <span>Fecha: <strong class="text-black">{{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}</strong></span>
                <span>•</span>
                <span>Estado: <strong class="text-black">{{ $session->isOpen() ? 'EN VIVO / ABIERTA' : 'CERRADA' }}</strong></span>
            </div>
        </div>
    @endif

    @if(!$session)
        <!-- Estado Vacío -->
        <div class="p-12 text-center rounded-2xl bg-[#09090b] border border-zinc-800/80 flex flex-col items-center justify-center">
            <div class="w-14 h-14 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-600 mb-4">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                </svg>
            </div>
            <h3 class="text-base font-black uppercase tracking-wider text-white">No hay ninguna noche abierta o seleccionada</h3>
            <p class="text-sm text-zinc-400 mt-1 mb-6">Apertura una noche para ver el resumen del cierre de caja.</p>
            <a href="{{ route('sessions.create') }}" class="px-6 py-3 rounded-xl bg-[#F5B81C] text-black font-black text-sm hover:bg-[#e5ac18] transition-all active:scale-95 shadow-md">
                + Aperturar Nueva Noche
            </a>
        </div>
    @else

        @php
            $netClosing = $closing->net_cash_closing ?? 0;
            $totalIngresado = ($closing->total_card_net ?? 0) + ($closing->total_qr_yasta ?? 0) + ($closing->total_qr_yape ?? 0) + ($closing->total_cash_invoices ?? 0);
            $totalEgresos = ($closing->total_staff_paid ?? 0) + ($closing->total_expenses ?? 0);

            $paidStaffCount = $attendances->where('is_paid', true)->count();
            $totalStaffCount = $attendances->count();
            $pendingAmount = max(0, $attendances->sum('pay_amount') - ($closing->total_staff_paid ?? 0));
        @endphp

        <!-- ========================================================
             2. CUADRÍCULA PRINCIPAL: ARQUEO DE CAJA VS OPERACIONES
             (Cómodo para Don Ludo, letras grandes y toques dorados)
             ======================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- ==========================================
                 COLUMNA IZQUIERDA: ARQUEO DE CAJA (7/12)
                 ========================================== -->
            <div class="theme-card lg:col-span-7 rounded-2xl border theme-border p-5 sm:p-6 shadow-sm space-y-4">
                
                <!-- Encabezado del Arqueo: Plata en Caja (Prominente y con Dorado) -->
                <div class="p-5 rounded-2xl bg-zinc-950 border border-[#F5B81C]/25 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-sm">
                    <div>
                        <span class="text-xs sm:text-sm font-black uppercase tracking-wider text-[#F5B81C] flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#F5B81C]"></span>
                            Plata que debe quedar en caja
                        </span>
                        <div class="text-3xl sm:text-4xl lg:text-5xl font-black font-mono tracking-tight mt-1 {{ $netClosing >= 0 ? 'text-white' : 'text-rose-400' }}">
                            <span class="text-2xl sm:text-3xl font-sans font-black mr-1 {{ $netClosing >= 0 ? 'text-[#F5B81C]' : 'text-rose-400' }}">Bs.</span>{{ number_format($netClosing, 2) }}
                        </div>
                    </div>
                    
                    <div class="sm:text-right pt-2 sm:pt-0 border-t sm:border-t-0 border-zinc-800/80">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 block">
                            Balance del Turno
                        </span>
                        <div class="flex items-center sm:justify-end gap-2.5 mt-1.5">
                            <span class="text-sm sm:text-base font-mono font-black text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20">
                                +Bs. {{ number_format($totalIngresado, 2) }}
                            </span>
                            <span class="text-sm sm:text-base font-mono font-black text-rose-400 bg-rose-500/10 px-2.5 py-1 rounded-lg border border-rose-500/20">
                                -Bs. {{ number_format($totalEgresos, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Desglose de Entradas y Salidas en Paralelo con Espaciado Cómodo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    
                    <!-- ENTRADAS -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                <span class="text-sm font-black text-white uppercase tracking-wider">Entradas</span>
                            </div>
                            <span class="text-sm sm:text-base font-mono font-black text-emerald-400">
                                Bs. {{ number_format($totalIngresado, 2) }}
                            </span>
                        </div>

                        <!-- Tarjetas Netas -->
                        <div class="px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between hover:border-zinc-700 transition-colors">
                            <div>
                                <span class="text-sm font-bold text-white block">Tarjetas (Neto)</span>
                                <span class="text-[11px] text-zinc-400 font-medium">Lector POS banco</span>
                            </div>
                            <span class="font-mono font-black text-white text-base">
                                Bs. {{ number_format($closing->total_card_net ?? 0, 2) }}
                            </span>
                        </div>

                        <!-- QR Yasta -->
                        <div class="px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between hover:border-zinc-700 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ asset('images/LogosQR/yasta.png') }}" alt="Yasta" class="h-4 w-auto object-contain rounded">
                                <div>
                                    <span class="text-sm font-bold text-white block">QR Yasta</span>
                                    <span class="text-[11px] text-zinc-400 font-medium">Banco Unión</span>
                                </div>
                            </div>
                            <span class="font-mono font-black text-white text-base">
                                Bs. {{ number_format($closing->total_qr_yasta ?? 0, 2) }}
                            </span>
                        </div>

                        <!-- QR Yape -->
                        <div class="px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between hover:border-zinc-700 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ asset('images/LogosQR/yape.png') }}" alt="Yape" class="h-4 w-auto object-contain rounded">
                                <div>
                                    <span class="text-sm font-bold text-white block">QR Yape</span>
                                    <span class="text-[11px] text-zinc-400 font-medium">Banco BCP</span>
                                </div>
                            </div>
                            <span class="font-mono font-black text-white text-base">
                                Bs. {{ number_format($closing->total_qr_yape ?? 0, 2) }}
                            </span>
                        </div>

                        <!-- Efectivo en Mano -->
                        <div class="px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between hover:border-zinc-700 transition-colors">
                            <div>
                                <span class="text-sm font-bold text-white block">Efectivo en Mano</span>
                                <span class="text-[11px] text-zinc-400 font-medium">Barras y Tienda</span>
                            </div>
                            <span class="font-mono font-black text-white text-base">
                                Bs. {{ number_format($closing->total_cash_invoices ?? 0, 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- SALIDAS -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                                <span class="text-sm font-black text-white uppercase tracking-wider">Salidas</span>
                            </div>
                            <span class="text-sm sm:text-base font-mono font-black text-rose-400">
                                -Bs. {{ number_format($totalEgresos, 2) }}
                            </span>
                        </div>

                        <!-- Pago Personal -->
                        <div class="px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between hover:border-zinc-700 transition-colors">
                            <div>
                                <span class="text-sm font-bold text-white block">Pago Personal</span>
                                <span class="text-[11px] text-zinc-400 font-medium">Turno de noche</span>
                            </div>
                            <span class="font-mono font-black text-rose-400 text-base">
                                -Bs. {{ number_format($closing->total_staff_paid ?? 0, 2) }}
                            </span>
                        </div>

                        <!-- Gastos de Turno -->
                        <div class="px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between hover:border-zinc-700 transition-colors">
                            <div>
                                <span class="text-sm font-bold text-white block">Gastos de Turno</span>
                                <span class="text-[11px] text-zinc-400 font-medium">Insumos y compras</span>
                            </div>
                            <span class="font-mono font-black text-rose-400 text-base">
                                -Bs. {{ number_format($closing->total_expenses ?? 0, 2) }}
                            </span>
                        </div>

                        <!-- Saldo Neto Caja Final (Destacado con Dorado) -->
                        <div class="px-4 py-3 rounded-xl bg-[#F5B81C]/10 border border-[#F5B81C]/40 flex items-center justify-between mt-3 shadow-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-[#F5B81C]"></div>
                                <span class="font-black text-white text-sm sm:text-base">Saldo en Caja</span>
                            </div>
                            <span class="font-mono font-black text-lg sm:text-xl {{ $netClosing >= 0 ? 'text-[#F5B81C]' : 'text-rose-400' }}">
                                Bs. {{ number_format($netClosing, 2) }}
                            </span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ==========================================
                 COLUMNA DERECHA: PERSONAL Y GASTOS (5/12)
                 ========================================== -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- TARJETA: PERSONAL DE LA NOCHE -->
                <div class="theme-card p-5 rounded-2xl border theme-border shadow-sm space-y-3.5">
                    <div class="flex items-center justify-between pb-2 border-b theme-border">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#F5B81C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="text-sm font-black uppercase tracking-wider text-white">
                                Personal ({{ $totalStaffCount }})
                            </span>
                        </div>
                        <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-full {{ $paidStaffCount === $totalStaffCount && $totalStaffCount > 0 ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-zinc-950 text-zinc-300 border border-zinc-800' }}">
                            {{ $paidStaffCount }}/{{ $totalStaffCount }} pagados
                        </span>
                    </div>

                    <!-- 3 Métricas Grandes para Don Ludo -->
                    <div class="grid grid-cols-3 gap-2.5 text-xs">
                        <div class="px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800">
                            <span class="text-[11px] text-zinc-400 uppercase font-black block tracking-wider">Pagado</span>
                            <span class="font-mono font-black text-white block mt-1 truncate text-sm sm:text-base">
                                <span class="text-xs text-[#F5B81C] font-sans mr-0.5">Bs.</span>{{ number_format($closing->total_staff_paid ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800">
                            <span class="text-[11px] text-zinc-400 uppercase font-black block tracking-wider">Planilla</span>
                            <span class="font-mono font-black text-zinc-300 block mt-1 truncate text-sm sm:text-base">
                                <span class="text-xs text-zinc-500 font-sans mr-0.5">Bs.</span>{{ number_format($attendances->sum('pay_amount'), 2) }}
                            </span>
                        </div>
                        <div class="px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800">
                            <span class="text-[11px] text-zinc-400 uppercase font-black block tracking-wider">Por Pagar</span>
                            <span class="font-mono font-black block mt-1 truncate text-sm sm:text-base {{ $pendingAmount > 0 ? 'text-amber-400' : 'text-zinc-500' }}">
                                <span class="text-xs font-sans mr-0.5">Bs.</span>{{ number_format($pendingAmount, 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Botón para Abrir Drawer Lateral (Destacado con Dorado) -->
                    <button type="button" onclick="openStaffDrawer()" 
                            class="w-full py-2.5 px-4 rounded-xl bg-[#F5B81C]/10 border border-[#F5B81C]/40 hover:border-[#F5B81C] hover:bg-[#F5B81C]/20 text-white font-bold text-xs sm:text-sm flex items-center justify-between transition-all cursor-pointer active:scale-95 group shadow-sm">
                        <span class="font-bold text-zinc-200 group-hover:text-white">Ver lista de personal y pagos</span>
                        <div class="flex items-center gap-1.5 text-[#F5B81C] font-black text-xs sm:text-sm">
                            <span>Ver ({{ $totalStaffCount }})</span>
                            <span class="group-hover:translate-x-1 transition-transform font-bold">&rarr;</span>
                        </div>
                    </button>
                </div>

                <!-- TARJETA: GASTOS DE LA NOCHE -->
                <div class="theme-card p-5 rounded-2xl border theme-border shadow-sm space-y-3.5">
                    <div class="flex items-center justify-between pb-2 border-b theme-border">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#F5B81C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="text-sm font-black uppercase tracking-wider text-white">
                                Gastos ({{ $expenses->count() }})
                            </span>
                        </div>
                        <span class="text-sm font-mono font-black text-rose-400 bg-rose-500/10 px-2.5 py-1 rounded-lg border border-rose-500/20">
                            Total: Bs. {{ number_format($closing->total_expenses ?? 0, 2) }}
                        </span>
                    </div>

                    <!-- Formulario de Gasto (Cómodo y Fácil de Ver) -->
                    <form method="POST" action="{{ route('closing.expenses.store') }}" 
                          class="space-y-2.5 {{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }}">
                        @csrf
                        <input type="hidden" name="night_session_id" value="{{ $session->id }}">

                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
                            <!-- Switch Interno / Externo -->
                            <div class="grid grid-cols-2 p-1 rounded-xl bg-zinc-950 border border-zinc-800 text-xs shrink-0 w-36">
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="interno" class="peer sr-only" checked>
                                    <div class="py-1.5 text-center rounded-lg font-bold text-zinc-400 transition-all peer-checked:bg-[#F5B81C] peer-checked:text-black peer-checked:font-black peer-checked:shadow-[0_0_10px_rgba(245,184,28,0.3)]">
                                        Interno
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="externo" class="peer sr-only">
                                    <div class="py-1.5 text-center rounded-lg font-bold text-zinc-400 transition-all peer-checked:bg-[#F5B81C] peer-checked:text-black peer-checked:font-black peer-checked:shadow-[0_0_10px_rgba(245,184,28,0.3)]">
                                        Externo
                                    </div>
                                </label>
                            </div>

                            <!-- Descripción -->
                            <input type="text" name="description" id="expense_description" required placeholder="Hielo, taxis, compras..."
                                   class="flex-1 min-w-[130px] text-xs sm:text-sm rounded-xl px-3 py-2 bg-zinc-950 border border-zinc-800 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C]">

                            <!-- Monto & Botón -->
                            <div class="flex items-center gap-2 shrink-0">
                                <div class="relative w-28">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 font-sans font-bold text-xs text-[#F5B81C]">Bs.</span>
                                    <input type="number" step="any" min="0.01" name="amount" id="expense_amount" required placeholder="0.00"
                                           class="w-full text-xs sm:text-sm font-mono font-black rounded-xl pl-7 pr-2.5 py-2 bg-zinc-950 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] placeholder:text-zinc-600">
                                </div>
                                <button type="submit" title="Registrar gasto"
                                        class="p-2 bg-[#F5B81C] text-black font-black text-sm rounded-xl hover:bg-[#e5ac18] active:scale-95 transition-all shadow-md shrink-0 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla de Gastos con Filas Cómodas -->
                    <div class="max-h-[160px] overflow-y-auto rounded-xl border border-zinc-800/80">
                        <table class="w-full text-xs text-left border-collapse">
                            <tbody class="divide-y border-zinc-800/60 font-sans">
                                @forelse($expenses as $exp)
                                    <tr id="expense-row-{{ $exp->id }}" class="hover:bg-zinc-900/30 transition-colors">
                                        <td class="px-3.5 py-2.5 font-bold text-white text-xs sm:text-sm truncate max-w-[150px]">{{ $exp->description }}</td>
                                        <td class="px-2.5 py-2.5 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $exp->category === 'interno' ? 'bg-zinc-950 border border-zinc-800 text-zinc-300' : 'bg-amber-500/10 border border-amber-500/30 text-amber-300' }}">
                                                {{ ucfirst($exp->category) }}
                                            </span>
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right font-mono font-black text-rose-400 text-xs sm:text-sm">
                                            Bs. {{ number_format($exp->amount, 2) }}
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right w-9">
                                            <form method="POST" action="{{ route('closing.expenses.destroy', $exp) }}" 
                                                  class="delete-expense-form {{ !$session->isOpen() ? 'pointer-events-none opacity-40' : '' }}"
                                                  data-expense-id="{{ $exp->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        title="Eliminar gasto"
                                                        class="text-zinc-500 hover:text-rose-400 p-1 rounded-lg hover:bg-zinc-900 transition-colors cursor-pointer active:scale-95">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"/>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-zinc-500 text-xs sm:text-sm">Sin gastos registrados</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        @if($session)
            <!-- FIRMAS PARA REPORTE IMPRESO (OCULTO EN PANTALLA) -->
            <div class="hidden print-only pt-10 mt-8 border-t border-black text-center text-xs font-sans">
                <div class="grid grid-cols-2 gap-12">
                    <div>
                        <div class="border-t border-black w-48 mx-auto pt-2 font-bold uppercase text-black">Cajero / Administrador</div>
                        <span class="text-[10px] text-zinc-600 block mt-0.5">Firma y Aclaración</span>
                    </div>
                    <div>
                        <div class="border-t border-black w-48 mx-auto pt-2 font-bold uppercase text-black">Don Ludo / Gerencia</div>
                        <span class="text-[10px] text-zinc-600 block mt-0.5">Visto Bueno y Aprobación</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- ========================================================
             3. SLIDE-OVER DRAWER LATERAL: LISTA DE PERSONAL
             ======================================================== -->
        <!-- Backdrop Oscuro con Desenfoque -->
        <div id="staff-drawer-backdrop" 
             onclick="closeStaffDrawer()" 
             class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300"></div>

        <!-- Panel Lateral Deslizante -->
        <aside id="staff-drawer" 
               class="fixed inset-y-0 right-0 z-50 w-full sm:w-[450px] bg-[#09090b] border-l border-zinc-800 shadow-2xl flex flex-col justify-between transform translate-x-full transition-transform duration-300 ease-out">
            
            <!-- Cabecera del Drawer -->
            <div class="p-5 border-b border-zinc-800/80 bg-zinc-950 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#F5B81C]/10 border border-[#F5B81C]/30 flex items-center justify-center text-[#F5B81C]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-white font-sans">
                            Personal de Turno
                        </h3>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            {{ $session->day_name }} {{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }} • {{ $attendances->count() }} trabajadores
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeStaffDrawer()" 
                        class="w-8 h-8 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white hover:border-[#F5B81C] transition-all flex items-center justify-center cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Buscador de Personal -->
            <div class="px-5 py-3 border-b border-zinc-800/80 bg-[#09090b]">
                <div class="relative">
                    <svg class="w-4 h-4 text-zinc-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="staff-drawer-search" oninput="filterDrawerStaff(this.value)" placeholder="Buscar por nombre o cargo..." 
                           class="w-full text-xs sm:text-sm bg-zinc-950 border border-zinc-800 rounded-xl pl-10 pr-3.5 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C]">
                </div>
            </div>

            <!-- Lista de Trabajadores Agrupados por Rol Oficial -->
            <div class="flex-1 overflow-y-auto p-5 space-y-2.5" id="drawer-staff-list">
                @php
                    $drawerLastRank = null;
                    $drawerCatNames = [
                        1 => '1. SEGURIDAD',
                        2 => '2. BARRA',
                        3 => '3. MOZOS / MESEROS',
                        4 => '4. LIMPIEZA',
                        5 => '5. OTROS',
                    ];
                @endphp
                @forelse($attendances as $att)
                    @php
                        $workerRole = str_ireplace(['mozo', 'mozos'], ['MESERO', 'MESEROS'], $att->staff->role ?? 'Staff');
                        $rank = $att->staff ? $att->staff->getRoleCategoryRank() : 5;
                    @endphp

                    @if($rank !== $drawerLastRank)
                        @php $drawerLastRank = $rank; @endphp
                        <div class="px-2 py-1 mt-2.5 flex items-center justify-between text-zinc-400 font-mono text-[11px] font-black uppercase tracking-wider border-b border-zinc-800">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#F5B81C]"></span>
                                <span class="text-zinc-300">{{ $drawerCatNames[$rank] ?? 'PERSONAL' }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="drawer-staff-item p-3 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between gap-3 hover:border-zinc-700 transition-colors"
                         data-search="{{ strtolower(($att->staff->name ?? '') . ' ' . $workerRole) }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-sm font-black text-zinc-300 shrink-0">
                                {{ substr($att->staff->name ?? 'T', 0, 1) }}
                            </div>
                            <div class="min-w-0 truncate">
                                <span class="text-xs sm:text-sm font-bold text-white block truncate uppercase">{{ $att->staff->name }}</span>
                                <span class="text-[11px] text-zinc-400 font-semibold block truncate">{{ strtoupper($workerRole) }}</span>
                            </div>
                        </div>

                        <div class="text-right shrink-0 flex flex-col items-end gap-1">
                            <span class="text-sm sm:text-base font-mono font-black text-white">
                                <span class="text-xs text-[#F5B81C] font-sans mr-0.5">Bs.</span>{{ number_format($att->pay_amount, 2) }}
                            </span>
                            @if($att->is_paid)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-[10px] font-bold text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Pagado</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-zinc-900 border border-zinc-800 text-[10px] font-bold text-zinc-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    <span>Pendiente</span>
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-zinc-500 text-sm">
                        No hay personal asignado a este turno.
                    </div>
                @endforelse
            </div>

            <!-- Pie del Drawer -->
            <div class="p-5 border-t border-zinc-800/80 bg-zinc-950 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 block">Total Pagado</span>
                    <span class="text-base font-black font-mono text-[#F5B81C]">Bs. {{ number_format($closing->total_staff_paid ?? 0, 2) }}</span>
                </div>
                <a href="{{ route('staffPayments.index', ['session_id' => $session->id]) }}" 
                   class="px-4 py-2.5 rounded-xl bg-[#F5B81C] text-black font-black text-xs sm:text-sm hover:bg-[#e5ac18] transition-all active:scale-95 shadow-md flex items-center gap-1.5">
                    <span>Ir a Pagos</span>
                    <span>&rarr;</span>
                </a>
            </div>

        </aside>
    @endif

</div>

<!-- Script para drawer y eliminación asíncrona de gastos -->
<script>
function openStaffDrawer() {
    const backdrop = document.getElementById('staff-drawer-backdrop');
    const drawer = document.getElementById('staff-drawer');
    if (!drawer || !backdrop) return;
    backdrop.classList.remove('hidden');
    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');
    });
    const search = document.getElementById('staff-drawer-search');
    if (search) {
        search.value = '';
        filterDrawerStaff('');
        setTimeout(() => search.focus(), 250);
    }
}

function closeStaffDrawer() {
    const backdrop = document.getElementById('staff-drawer-backdrop');
    const drawer = document.getElementById('staff-drawer');
    if (!drawer || !backdrop) return;
    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    drawer.classList.remove('translate-x-0');
    drawer.classList.add('translate-x-full');
    setTimeout(() => {
        backdrop.classList.add('hidden');
    }, 280);
}

function filterDrawerStaff(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('.drawer-staff-item').forEach(item => {
        const searchData = item.getAttribute('data-search') || '';
        if (searchData.includes(q)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Cerrar drawer con tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeStaffDrawer();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-expense-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!confirm('¿Eliminar este gasto?')) return;

            const expenseId = this.getAttribute('data-expense-id');
            const row = document.getElementById('expense-row-' + expenseId);
            const actionUrl = this.getAttribute('action');
            const token = this.querySelector('input[name="_token"]').value;

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-HTTP-Method-Override': 'DELETE',
                        'Accept': 'application/json'
                    },
                    body: new URLSearchParams({
                        '_method': 'DELETE',
                        '_token': token
                    })
                });

                if (response.ok) {
                    if (row) {
                        row.classList.add('transition-all', 'duration-300', 'opacity-0', 'scale-95');
                        setTimeout(() => {
                            row.remove();
                            window.location.reload();
                        }, 200);
                    } else {
                        window.location.reload();
                    }
                } else {
                    this.submit();
                }
            } catch (err) {
                this.submit();
            }
        });
    });
});
</script>
@endsection
