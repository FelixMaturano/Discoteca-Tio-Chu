@extends('layouts.app')

@section('title', 'Pagos al Personal')

@section('content')

<style>
@media print {
    #main-sidebar, header, nav, .print-hide,
    button, #wage-batch-drawer, #wage-batch-drawer-overlay,
    #payroll-toast, a, .payroll-role-pill,
    .no-print, [type="button"], input[type="checkbox"],
    .sticky {
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
    .payroll-cat-divider {
        background: #f3f4f6 !important;
        color: #000000 !important;
        font-weight: bold !important;
    }
    table {
        display: table !important;
        width: 100% !important;
        border-collapse: collapse !important;
    }
    thead {
        display: table-header-group !important;
    }
    tbody {
        display: table-row-group !important;
    }
    tr.payroll-table-row {
        display: table-row !important;
        background: #ffffff !important;
        border: none !important;
    }
    td {
        display: table-cell !important;
        border: 1px solid #d1d5db !important;
        padding: 6px 10px !important;
        color: #000000 !important;
    }
    th {
        border: 1px solid #d1d5db !important;
        padding: 6px 10px !important;
        background: #f3f4f6 !important;
        color: #000000 !important;
        font-weight: bold !important;
    }
    .pay-amount-input {
        border: none !important;
        background: transparent !important;
        color: #000000 !important;
        font-weight: bold !important;
        font-size: 13px !important;
        text-align: right !important;
        padding: 0 !important;
    }
    .text-white, .text-zinc-200, .text-zinc-300, .text-zinc-400 {
        color: #000000 !important;
    }
    .text-\[\#F5B81C\] {
        color: #000000 !important;
    }
}
</style>

<div class="space-y-5 max-w-7xl mx-auto pb-10 w-full min-w-0 max-w-full overflow-x-hidden">

    <!-- ==========================================
         CABECERA (iOS PURE DARK - ESTILO TÍO CHU)
         ========================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-3.5 sm:px-5 sm:py-4 rounded-2xl theme-card border theme-border shadow-sm">
        
        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3.5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-[#F5B81C] shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white font-sans">
                    Pagos al Personal
                </h1>
            </div>

            @if($session)
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-zinc-900 border border-zinc-800">
                    <span class="w-2 h-2 rounded-full {{ $session->isOpen() ? 'bg-emerald-500' : 'bg-zinc-600' }}"></span>
                    <span class="text-[11px] font-black uppercase tracking-wider {{ $session->isOpen() ? 'text-zinc-200' : 'text-zinc-500' }}">
                        {{ $session->isOpen() ? 'En Vivo' : 'Cerrada' }}
                    </span>
                </div>

                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-bold text-zinc-300 font-sans">
                    <span class="uppercase text-white">{{ $session->day_name }}</span>
                    <span class="text-zinc-600">•</span>
                    <span class="font-mono text-zinc-300 text-xs sm:text-sm">{{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}</span>
                </div>
            @endif

            @if($allSessions->isNotEmpty())
                <form method="GET" action="{{ route('staffPayments.index') }}" class="flex items-center w-full sm:w-auto">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-xs hover:border-[#F5B81C] transition-colors shadow-sm w-full sm:w-auto">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <select name="session_id" id="session_id" onchange="this.form.submit()" 
                                class="bg-transparent border-0 text-xs sm:text-sm font-bold text-zinc-200 focus:outline-none cursor-pointer pr-1 w-full">
                            @foreach($allSessions as $s)
                                <option value="{{ $s->id }}" {{ $session && $session->id === $s->id ? 'selected' : '' }} class="bg-zinc-900 text-white font-sans">
                                    {{ $s->day_name }} {{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }} ({{ $s->isOpen() ? 'En Vivo' : 'Cerrada' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end shrink-0">
            @if($session)
                <button type="button" onclick="window.print()" 
                        class="w-full sm:w-auto justify-center px-3.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-xs sm:text-sm font-bold text-zinc-300 hover:text-white transition-all flex items-center gap-2 shadow-sm cursor-pointer active:scale-95 text-center">
                    <svg class="w-4 h-4 text-[#F5B81C] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Imprimir Planilla</span>
                </button>
            @endif
            <a href="{{ route('paymentHistory.index', $session ? ['session_id' => $session->id] : []) }}" 
               class="w-full sm:w-auto justify-center px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-xs sm:text-sm font-bold text-zinc-200 hover:text-white transition-all flex items-center gap-2 shadow-sm cursor-pointer active:scale-95 text-center">
                <svg class="w-4 h-4 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Historial de Pagos</span>
            </a>
        </div>
    </div>

    @if($session)
        <!-- CABECERA EXCLUSIVA PARA IMPRESIÓN (OCULTA EN PANTALLA) -->
        <div class="hidden print-only text-center pb-4 mb-4 border-b-2 border-black font-sans">
            <h1 class="text-2xl font-black uppercase tracking-tight text-black">DISCOTECA TÍO CHU</h1>
            <h2 class="text-sm font-bold uppercase tracking-wider text-zinc-700 mt-0.5">PLANILLA OFICIAL DE PAGOS AL PERSONAL</h2>
            <div class="flex items-center justify-center gap-3 text-xs text-zinc-600 mt-1">
                <span>Noche: <strong class="uppercase text-black">{{ $session->day_name }} ({{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }})</strong></span>
                <span>•</span>
                <span>Personal: <strong class="text-black">{{ $attendances->count() }} integrantes</strong></span>
                <span>•</span>
                <span>Total: <strong class="text-black">Bs. {{ number_format($totalPlanilla, 2) }}</strong></span>
                <span>•</span>
                <span>Pagado: <strong class="text-black">Bs. {{ number_format($totalPagado, 2) }}</strong></span>
            </div>
        </div>
    @endif

    @if(!$session)
        <div class="rounded-2xl p-8 sm:p-12 text-center shadow-sm theme-card border theme-border">
            <h3 class="text-base font-bold text-white">No hay ninguna noche abierta o seleccionada</h3>
            <p class="text-sm text-zinc-400 mt-1 mb-6">Crea o selecciona una noche para registrar la planilla de pagos.</p>
            <a href="{{ route('sessions.create') }}" class="inline-flex items-center px-6 py-3 bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-sm font-black rounded-xl transition-all shadow-md">
                + Crear Noche
            </a>
        </div>
    @else

        @if(!$session->isOpen())
            <div class="px-4 sm:px-5 py-3 rounded-xl border border-zinc-800 bg-zinc-900/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-zinc-500"></span>
                    <span class="text-xs sm:text-sm font-bold text-zinc-300">Noche Cerrada (Solo lectura)</span>
                </div>
                <a href="{{ route('closing.index', ['session_id' => $session->id]) }}" 
                   class="text-xs sm:text-sm font-bold text-[#F5B81C] hover:underline">
                    Ver en Cierre de Caja &rarr;
                </a>
            </div>
        @endif

        <!-- ==========================================
             KPIs DE LA PLANILLA (LEGIBLES Y CON DORADO)
             ========================================== -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-4">
            
            <div class="col-span-2 sm:col-span-1 rounded-2xl p-3.5 sm:p-5 theme-card border theme-border shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-bold text-zinc-400 uppercase tracking-wider">Total Planilla</span>
                    <span class="text-[11px] sm:text-xs font-bold text-zinc-500 font-mono">{{ $attendances->count() }} pers.</span>
                </div>
                <div class="mt-1 sm:mt-2 text-2xl sm:text-3xl font-black font-mono text-white tracking-tight">
                    <span class="text-base sm:text-lg text-[#F5B81C] mr-0.5">Bs.</span><span id="kpi-total-planilla">{{ number_format($totalPlanilla, 2) }}</span>
                </div>
            </div>

            <div class="rounded-2xl p-3.5 sm:p-5 theme-card border border-emerald-500/25 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-bold text-emerald-400 uppercase tracking-wider truncate">Total Pagado</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                </div>
                <div class="mt-1 sm:mt-2 text-xl sm:text-3xl font-black font-mono text-emerald-400 tracking-tight truncate">
                    <span class="text-sm sm:text-lg text-emerald-500 mr-0.5">Bs.</span><span id="kpi-total-pagado">{{ number_format($totalPagado, 2) }}</span>
                </div>
            </div>

            <div class="rounded-2xl p-3.5 sm:p-5 theme-card border theme-border shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-bold text-zinc-400 uppercase tracking-wider truncate">Por Pagar</span>
                    <span class="w-2 h-2 rounded-full {{ $totalPendiente > 0 ? 'bg-amber-400' : 'bg-zinc-600' }} shrink-0"></span>
                </div>
                <div class="mt-1 sm:mt-2 text-xl sm:text-3xl font-black font-mono tracking-tight truncate {{ $totalPendiente > 0 ? 'text-amber-400' : 'text-zinc-500' }}">
                    <span class="text-sm sm:text-lg mr-0.5">Bs.</span><span id="kpi-total-pendiente">{{ number_format($totalPendiente, 2) }}</span>
                </div>
            </div>

        </div>

        <!-- Desglose por Cargo -->
        <div class="rounded-2xl p-3.5 sm:p-5 theme-card border theme-border shadow-sm space-y-2.5">
            <span class="text-xs font-black uppercase tracking-wider text-white block font-sans">Desglose por Cargo</span>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 text-xs">
                <div class="p-2.5 sm:p-3 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between">
                    <span class="font-bold text-white text-xs">Meseros & Limpieza</span>
                    <span class="font-black text-xs sm:text-sm font-mono text-[#F5B81C]">
                        {{ $summaryByType['meseros']['count'] }} p. — Bs. {{ number_format($summaryByType['meseros']['total'], 2) }}
                    </span>
                </div>

                <div class="p-2.5 sm:p-3 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between">
                    <span class="font-bold text-white text-xs">Bartenders</span>
                    <span class="font-black text-xs sm:text-sm font-mono text-[#F5B81C]">
                        {{ $summaryByType['bartenders']['count'] }} p. — Bs. {{ number_format($summaryByType['bartenders']['total'], 2) }}
                    </span>
                </div>

                <div class="p-2.5 sm:p-3 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between">
                    <span class="font-bold text-white text-xs">Seguridad</span>
                    <span class="font-black text-xs sm:text-sm font-mono text-[#F5B81C]">
                        {{ $summaryByType['seguridad']['count'] }} p. — Bs. {{ number_format($summaryByType['seguridad']['total'], 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Barra de Acciones Rápidas -->
        <!-- Barra de Acciones Rápidas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3.5 sm:p-4 rounded-2xl theme-card border theme-border shadow-sm {{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }}">
            @if($availableStaff->isNotEmpty())
                <form method="POST" action="{{ route('staffPayments.store') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1">
                    @csrf
                    <input type="hidden" name="night_session_id" value="{{ $session->id }}">
                    <select name="staff_id" required class="bg-zinc-900 border border-zinc-800 text-xs sm:text-sm rounded-xl px-3.5 py-2 text-zinc-100 cursor-pointer flex-1 focus:outline-none focus:border-[#F5B81C]">
                        <option value="" disabled selected class="bg-zinc-900">+ Agregar trabajador a la noche...</option>
                        @foreach($availableStaff as $as)
                            @php
                                $roleDisplay = str_ireplace(['mozo', 'mozos'], ['MESERO', 'MESEROS'], $as->role);
                            @endphp
                            <option value="{{ $as->id }}" class="bg-zinc-900">
                                {{ $as->name }} ({{ strtoupper($roleDisplay) }}) — Bs. {{ number_format($as->getPayForDay($session->day_name), 2) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs sm:text-sm font-black rounded-xl active:scale-95 transition-all whitespace-nowrap cursor-pointer shadow-sm text-center">
                        + Agregar
                    </button>
                </form>
            @else
                <span class="text-xs text-zinc-500 font-medium">Todo el personal activo está incluido en esta noche.</span>
            @endif

            <div class="grid grid-cols-2 sm:flex items-center gap-2 shrink-0 w-full sm:w-auto">
                <!-- Botón Editar Sueldo General -->
                <button type="button" onclick="openWageBatchDrawer()" 
                        class="justify-center px-3 sm:px-4 py-2 bg-zinc-900 border border-zinc-800 hover:border-[#F5B81C] text-zinc-200 rounded-xl text-xs sm:text-sm font-bold hover:text-white transition-all cursor-pointer flex items-center gap-1.5 shadow-sm active:scale-95 text-center">
                    <svg class="w-4 h-4 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <span>Editar por Área</span>
                </button>

                <!-- Marcar Todos como Pagados -->
                <form method="POST" action="{{ route('staffPayments.markAllPaid', $session) }}" onsubmit="return confirm('¿Marcar a todo el personal como PAGADO?');" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full justify-center px-3 sm:px-4 py-2 rounded-xl bg-zinc-900 border border-emerald-500/30 text-xs sm:text-sm font-bold text-emerald-400 hover:bg-emerald-500/10 transition-colors cursor-pointer flex items-center gap-1.5 shadow-sm active:scale-95 text-center">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Marcar Pagados</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Filtros de Rol Oficiales de Don Ludo (1. Seguridad, 2. Barra, 3. Mozos, 4. Limpieza) -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs font-mono font-bold">
            <button type="button" onclick="filterPayrollByRole('all')" id="payroll-role-pill-all" class="payroll-role-pill px-3 py-1.5 rounded-xl border border-[#F5B81C] bg-[#F5B81C] text-black font-black transition-all cursor-pointer">
                Todos ({{ $attendances->count() }})
            </button>
            <button type="button" onclick="filterPayrollByRole('seguridad')" id="payroll-role-pill-seguridad" class="payroll-role-pill px-3 py-1.5 rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700 hover:text-white transition-all cursor-pointer">
                1. Seguridad ({{ $attendances->filter(fn($a) => $a->staff && $a->staff->getRoleCategory() === 'seguridad')->count() }})
            </button>
            <button type="button" onclick="filterPayrollByRole('barra')" id="payroll-role-pill-barra" class="payroll-role-pill px-3 py-1.5 rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700 hover:text-white transition-all cursor-pointer">
                2. Barra ({{ $attendances->filter(fn($a) => $a->staff && $a->staff->getRoleCategory() === 'barra')->count() }})
            </button>
            <button type="button" onclick="filterPayrollByRole('mozos')" id="payroll-role-pill-mozos" class="payroll-role-pill px-3 py-1.5 rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700 hover:text-white transition-all cursor-pointer">
                3. Mozos ({{ $attendances->filter(fn($a) => $a->staff && $a->staff->getRoleCategory() === 'mozos')->count() }})
            </button>
            <button type="button" onclick="filterPayrollByRole('limpieza')" id="payroll-role-pill-limpieza" class="payroll-role-pill px-3 py-1.5 rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700 hover:text-white transition-all cursor-pointer">
                4. Limpieza ({{ $attendances->filter(fn($a) => $a->staff && $a->staff->getRoleCategory() === 'limpieza')->count() }})
            </button>
        </div>

        <!-- ==========================================
             TABLA DE PLANILLA OFICIAL (LETRAS GRANDES & CONTRAPUNTO)
             ========================================== -->
        <form id="payroll-form" data-ajax="true" method="POST" action="{{ route('staffPayments.update') }}" class="{{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="night_session_id" value="{{ $session->id }}">

            <div class="rounded-2xl overflow-hidden theme-card border theme-border shadow-sm">
                <!-- Encabezado de la planilla -->
                <div class="px-4 sm:px-5 py-3.5 bg-zinc-900/90 border-b border-zinc-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="text-xs sm:text-sm font-bold text-white uppercase tracking-wider font-sans">
                        Planilla: {{ mb_strtoupper($session->day_name) }} {{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}
                    </h3>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
                        <!-- Buscador por Nombre en Planilla -->
                        <div class="relative w-full sm:w-64">
                            <svg class="w-3.5 h-3.5 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input type="text" 
                                   id="payroll-search-input" 
                                   oninput="filterPayrollTable(this.value)" 
                                   placeholder="Buscar por nombre..." 
                                   class="w-full pl-8 pr-7 py-2 rounded-xl text-xs bg-zinc-900 border border-zinc-800 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C] transition-all font-sans">
                            <button type="button" 
                                    id="clear-payroll-search" 
                                    onclick="clearPayrollSearch()" 
                                    class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-white text-sm leading-none cursor-pointer">
                                &times;
                            </button>
                        </div>

                        <div class="text-xs sm:text-sm font-mono font-black text-[#F5B81C] whitespace-nowrap text-right sm:text-left">
                            TOTAL: Bs. <span id="header-total-planilla">{{ number_format($totalPlanilla, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tabla Responsive (Cards en Móvil, Tabla en Desktop) -->
                <div class="p-3 md:p-0">
                    <table class="w-full text-xs sm:text-sm text-left block md:table" id="payroll-table">
                        <thead class="hidden md:table-header-group bg-zinc-900/80 border-b border-zinc-800 text-zinc-400 uppercase text-[10px] tracking-wider font-bold">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">N°</th>
                                <th class="px-4 py-3">Personal</th>
                                <th class="px-4 py-3">Cargo</th>
                                <th class="px-4 py-3 w-40 text-right">Monto (Bs.)</th>
                                <th class="px-4 py-3 w-28 text-center">Pagado</th>
                                <th class="px-4 py-3 w-24 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="payroll-tbody" class="block md:table-row-group divide-y-0 md:divide-y md:divide-zinc-800/60 font-sans space-y-3 md:space-y-0">
                            @php
                                $payrollLastRank = null;
                                $payrollCatNames = [
                                    1 => '1. SEGURIDAD',
                                    2 => '2. BARRA',
                                    3 => '3. MOZOS / MESEROS',
                                    4 => '4. LIMPIEZA',
                                    5 => '5. OTROS',
                                ];
                            @endphp
                            @forelse($attendances as $index => $att)
                                @php
                                    $roleName = str_ireplace(['mozo', 'mozos'], ['MESERO', 'MESEROS'], $att->staff->role ?? 'Staff');
                                    $rank = $att->staff ? $att->staff->getRoleCategoryRank() : 5;
                                    $cat = $att->staff ? $att->staff->getRoleCategory() : 'otros';
                                @endphp

                                @if($rank !== $payrollLastRank)
                                    @php $payrollLastRank = $rank; @endphp
                                    <tr class="payroll-cat-divider block md:table-row bg-zinc-900/80 border-y border-zinc-800 text-zinc-300 font-mono text-[11px] font-black uppercase tracking-wider" data-category="{{ $cat }}">
                                        <td colspan="6" class="px-4 py-2 block md:table-cell">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#F5B81C]"></span>
                                                    <span class="text-white">{{ $payrollCatNames[$rank] ?? 'PERSONAL' }}</span>
                                                </div>
                                                <span class="text-zinc-500 font-mono text-[10px]">
                                                    {{ $attendances->filter(fn($a) => $a->staff && $a->staff->getRoleCategoryRank() === $rank)->count() }} pers.
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                <tr id="attendance-row-{{ $att->id }}" 
                                    class="payroll-table-row block md:table-row p-3.5 md:p-0 rounded-2xl md:rounded-none bg-zinc-900/60 md:bg-transparent border md:border-0 border-zinc-800 hover:bg-zinc-900/40 transition-colors shadow-sm md:shadow-none space-y-2.5 md:space-y-0" 
                                    data-name="{{ strtolower($att->staff->name) }}" 
                                    data-role="{{ strtolower($roleName) }}"
                                    data-category="{{ $cat }}">
                                    
                                    <td class="hidden md:table-cell px-4 py-3 text-center text-zinc-500 font-mono font-bold row-index">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="block md:table-cell md:px-4 md:py-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-bold text-white uppercase text-sm sm:text-base leading-tight">
                                                    {{ $att->staff->name }}
                                                </div>
                                                @if(!empty($att->staff->phone))
                                                    <div class="text-zinc-500 font-mono text-xs mt-0.5 flex items-center gap-1">
                                                        <span>{{ $att->staff->phone }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="md:hidden inline-flex px-2 py-0.5 rounded-lg text-[10px] font-bold bg-zinc-900 border border-zinc-800 text-zinc-200">
                                                {{ strtoupper($roleName) }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="hidden md:table-cell px-4 py-3 text-zinc-400">
                                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-zinc-900 border border-zinc-800 text-zinc-200">
                                            {{ strtoupper($roleName) }}
                                        </span>
                                    </td>

                                    <td class="block md:table-cell md:px-4 md:py-2.5 pt-2 border-t md:border-0 border-zinc-800/60">
                                        <div class="flex items-center justify-between md:justify-end gap-2">
                                            <span class="md:hidden text-xs text-zinc-400 font-bold uppercase font-mono">Sueldo:</span>
                                            <div class="inline-flex items-center gap-1">
                                                <span class="text-xs text-[#F5B81C] font-bold">Bs.</span>
                                                <input type="number" inputmode="numeric" step="5" min="0" 
                                                       name="attendances[{{ $att->id }}][pay_amount]" 
                                                       value="{{ $att->pay_amount }}" 
                                                       data-attendance-id="{{ $att->id }}"
                                                       oninput="recalculateTotals()"
                                                       class="pay-amount-input bg-zinc-900 border border-zinc-800 w-28 text-right rounded-xl px-3 py-2 md:py-1.5 text-sm sm:text-base font-mono font-black text-white focus:outline-none focus:border-[#F5B81C] transition-colors">
                                            </div>
                                        </div>
                                    </td>

                                    <td class="block md:table-cell md:px-4 md:py-3 pt-2 border-t md:border-0 border-zinc-800/60">
                                        <div class="flex items-center justify-between md:justify-center gap-2">
                                            <span class="md:hidden text-xs text-zinc-400 font-bold uppercase font-mono">Estado:</span>
                                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                                <input type="checkbox" 
                                                       name="attendances[{{ $att->id }}][is_paid]" 
                                                       value="1" 
                                                       data-attendance-id="{{ $att->id }}"
                                                       onchange="recalculateTotals()"
                                                       {{ $att->is_paid ? 'checked' : '' }}
                                                       class="is-paid-checkbox rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 w-5 h-5 cursor-pointer accent-[#F5B81C]">
                                                <span class="md:hidden text-xs font-bold {{ $att->is_paid ? 'text-emerald-400' : 'text-zinc-400' }}">
                                                    {{ $att->is_paid ? 'Pagado' : 'Por Pagar' }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>

                                    <td class="block md:table-cell md:px-4 md:py-3 pt-2 border-t md:border-0 border-zinc-800/60 text-right">
                                        <div class="flex items-center justify-end">
                                            <button type="button" 
                                                    onclick="removeAttendance({{ $att->id }}, '{{ route('staffPayments.destroy', $att) }}', '{{ addslashes($att->staff->name) }}')"
                                                    class="w-full md:w-auto px-3 py-1.5 rounded-lg text-xs font-bold text-rose-400 hover:text-rose-300 bg-rose-500/10 md:bg-transparent border border-rose-500/20 md:border-transparent hover:border-rose-500/20 transition-all cursor-pointer text-center">
                                                Quitar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="empty-payroll-row" class="block md:table-row">
                                    <td colspan="6" class="px-4 py-10 text-center text-zinc-500 font-sans block md:table-cell">
                                        No hay personal asignado a esta noche.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="payroll-search-empty" class="hidden">
                                <td colspan="6" class="px-4 py-10 text-center text-zinc-500 font-sans block md:table-cell">
                                    No se encontró ningún integrante en la planilla que coincida con la búsqueda.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="hidden md:table-footer-group bg-zinc-900/90 border-t border-zinc-800 font-bold text-xs sm:text-sm text-white">
                            <tr>
                                <td colspan="3" class="px-4 py-3.5 text-right uppercase tracking-wider font-mono">TOTAL PLANILLA:</td>
                                <td class="px-4 py-3.5 text-right font-mono font-black text-[#F5B81C] text-base">
                                    Bs. <span id="footer-total-planilla">{{ number_format($totalPlanilla, 2) }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-mono text-emerald-400 text-xs sm:text-sm font-black">
                                    Pagado: Bs. <span id="footer-total-pagado">{{ number_format($totalPagado, 2) }}</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Barra de Guardar Cambios (Sticky en Móvil) -->
                <div class="p-3.5 sm:p-4 bg-zinc-900/90 border-t border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 sticky bottom-0 z-10 shadow-lg">
                    <span id="payroll-autosave-feedback" class="text-xs text-zinc-500 font-mono hidden">
                        Cambios sincronizados
                    </span>
                    <div class="w-full sm:w-auto sm:ml-auto flex items-center gap-3">
                        <button type="submit" id="btn-save-payroll" 
                                class="w-full sm:w-auto justify-center px-6 py-3 bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs sm:text-sm font-black rounded-xl active:scale-95 transition-all shadow-md cursor-pointer uppercase tracking-wider flex items-center gap-2">
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- FIRMAS PARA REPORTE IMPRESO DE PLANILLA (OCULTO EN PANTALLA) -->
        <div class="hidden print-only pt-10 mt-8 border-t border-black text-center text-xs font-sans">
            <div class="grid grid-cols-2 gap-12">
                <div>
                    <div class="border-t border-black w-48 mx-auto pt-2 font-bold uppercase text-black">Cajero / Encargado de Pagos</div>
                    <span class="text-[10px] text-zinc-600 block mt-0.5">Firma y Aclaración</span>
                </div>
                <div>
                    <div class="border-t border-black w-48 mx-auto pt-2 font-bold uppercase text-black">Don Ludo / Gerencia</div>
                    <span class="text-[10px] text-zinc-600 block mt-0.5">Visto Bueno y Aprobación</span>
                </div>
            </div>
        </div>

    @endif

</div>

<!-- TOAST FLOTANTE DISCRETO -->
<div id="discreet-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-8 opacity-0 pointer-events-none transition-all duration-300 ease-out flex items-center gap-2.5 px-4 py-3 rounded-2xl bg-zinc-900 border border-zinc-700/80 shadow-2xl text-xs font-medium text-white backdrop-blur-md">
    <div id="discreet-toast-icon" class="w-2 h-2 rounded-full bg-[#F5B81C]"></div>
    <span id="discreet-toast-msg" class="tracking-wide">Notificación</span>
</div>

@if($session)
<!-- DRAWER: EDITAR SUELDO POR ÁREA (LIMPIO DE IA) -->
<div id="wage-batch-drawer-overlay" class="drawer-overlay" onclick="closeWageBatchDrawer()"></div>

<div id="wage-batch-drawer" class="drawer-panel" style="z-index: 60;">
    <div class="drawer-header">
        <div>
            <h2 class="text-base font-black text-white tracking-tight">Editar Sueldos por Área</h2>
            <span class="text-xs font-bold text-[#F5B81C] tracking-wide block mt-0.5">
                {{ $session->day_name }} {{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}
            </span>
        </div>
        <button type="button" onclick="closeWageBatchDrawer()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <form method="POST" action="{{ route('staffPayments.updateBatchWage') }}" class="drawer-body space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="night_session_id" value="{{ $session->id }}">

        <div class="space-y-3">
            <!-- Meseros -->
            <div class="p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between gap-4">
                <label for="wage_meseros" class="text-sm font-bold text-white block">Meseros</label>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-[#F5B81C] font-bold text-xs font-mono">Bs.</span>
                    <input type="number" step="5" min="0" name="wages[meseros]" id="wage_meseros" placeholder="100"
                           class="bg-zinc-900 border border-zinc-800 w-24 text-right rounded-xl px-3 py-2 text-sm font-bold font-mono text-[#F5B81C] focus:outline-none focus:border-[#F5B81C]">
                </div>
            </div>

            <!-- Limpieza -->
            <div class="p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between gap-4">
                <label for="wage_limpieza" class="text-sm font-bold text-white block">Limpieza</label>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-[#F5B81C] font-bold text-xs font-mono">Bs.</span>
                    <input type="number" step="5" min="0" name="wages[limpieza]" id="wage_limpieza" placeholder="100"
                           class="bg-zinc-900 border border-zinc-800 w-24 text-right rounded-xl px-3 py-2 text-sm font-bold font-mono text-[#F5B81C] focus:outline-none focus:border-[#F5B81C]">
                </div>
            </div>

            <!-- Seguridades -->
            <div class="p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between gap-4">
                <label for="wage_seguridades" class="text-sm font-bold text-white block">Seguridad</label>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-[#F5B81C] font-bold text-xs font-mono">Bs.</span>
                    <input type="number" step="5" min="0" name="wages[seguridades]" id="wage_seguridades" placeholder="120"
                           class="bg-zinc-900 border border-zinc-800 w-24 text-right rounded-xl px-3 py-2 text-sm font-bold font-mono text-[#F5B81C] focus:outline-none focus:border-[#F5B81C]">
                </div>
            </div>

            <!-- Barra -->
            <div class="p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between gap-4">
                <label for="wage_barra" class="text-sm font-bold text-white block">Bartenders</label>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-[#F5B81C] font-bold text-xs font-mono">Bs.</span>
                    <input type="number" step="5" min="0" name="wages[barra]" id="wage_barra" placeholder="100"
                           class="bg-zinc-900 border border-zinc-800 w-24 text-right rounded-xl px-3 py-2 text-sm font-bold font-mono text-[#F5B81C] focus:outline-none focus:border-[#F5B81C]">
                </div>
            </div>
        </div>

        <div class="pt-5 border-t border-zinc-800 flex items-center justify-end gap-2.5 mt-6 w-full">
            <button type="button" onclick="closeWageBatchDrawer()" 
                    class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl border border-zinc-800 text-zinc-400 hover:text-white text-xs sm:text-sm font-bold text-center transition-all cursor-pointer">
                Cancelar
            </button>
            <button type="submit" 
                    class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs sm:text-sm font-black uppercase tracking-wider text-center transition-all cursor-pointer shadow-md">
                Aplicar Tarifas
            </button>
        </div>
    </form>
</div>

<script>
    let toastTimeout = null;
    function showToast(message, type = 'success') {
        const toast = document.getElementById('discreet-toast');
        const msgEl = document.getElementById('discreet-toast-msg');
        const iconEl = document.getElementById('discreet-toast-icon');
        if (!toast || !msgEl || !iconEl) return;

        clearTimeout(toastTimeout);
        msgEl.textContent = message;

        if (type === 'error') {
            iconEl.className = 'w-2 h-2 rounded-full bg-rose-500';
            toast.classList.add('border-rose-500/40');
            toast.classList.remove('border-zinc-700/80');
        } else {
            iconEl.className = 'w-2 h-2 rounded-full bg-[#F5B81C]';
            toast.classList.remove('border-rose-500/40');
            toast.classList.add('border-zinc-700/80');
        }

        toast.classList.remove('translate-y-8', 'opacity-0', 'pointer-events-none');
        toast.classList.add('translate-y-0', 'opacity-100');

        toastTimeout = setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-8', 'opacity-0', 'pointer-events-none');
        }, 2200);
    }

    function recalculateTotals() {
        const rows = document.querySelectorAll('#payroll-tbody tr[id^="attendance-row-"]');
        let totalPlanilla = 0;
        let totalPagado = 0;
        let workerCount = 0;

        rows.forEach(row => {
            const inputAmount = row.querySelector('.pay-amount-input');
            const checkboxPaid = row.querySelector('.is-paid-checkbox');

            if (inputAmount) {
                workerCount++;
                const amount = parseFloat(inputAmount.value) || 0;
                totalPlanilla += amount;
                if (checkboxPaid && checkboxPaid.checked) {
                    totalPagado += amount;
                }
            }
        });

        const totalPendiente = Math.max(0, totalPlanilla - totalPagado);
        const formatMoney = (val) => val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const kpiPlanilla = document.getElementById('kpi-total-planilla');
        if (kpiPlanilla) kpiPlanilla.textContent = formatMoney(totalPlanilla);

        const kpiPagado = document.getElementById('kpi-total-pagado');
        if (kpiPagado) kpiPagado.textContent = formatMoney(totalPagado);

        const kpiPendiente = document.getElementById('kpi-total-pendiente');
        if (kpiPendiente) kpiPendiente.textContent = formatMoney(totalPendiente);

        const headerPlanilla = document.getElementById('header-total-planilla');
        if (headerPlanilla) headerPlanilla.textContent = formatMoney(totalPlanilla);

        const footerPlanilla = document.getElementById('footer-total-planilla');
        if (footerPlanilla) footerPlanilla.textContent = formatMoney(totalPlanilla);

        const footerPagado = document.getElementById('footer-total-pagado');
        if (footerPagado) footerPagado.textContent = formatMoney(totalPagado);
    }

    function renumberRows() {
        const rows = document.querySelectorAll('#payroll-tbody tr[id^="attendance-row-"]');
        rows.forEach((row, index) => {
            const indexCol = row.querySelector('.row-index');
            if (indexCol) indexCol.textContent = index + 1;
        });

        if (rows.length === 0) {
            const tbody = document.getElementById('payroll-tbody');
            if (tbody && !document.getElementById('empty-payroll-row')) {
                tbody.innerHTML = `
                    <tr id="empty-payroll-row">
                        <td colspan="6" class="px-4 py-10 text-center text-zinc-500 font-sans">
                            No hay personal asignado a esta noche.
                        </td>
                    </tr>
                `;
            }
        }
    }

    async function removeAttendance(attId, deleteUrl, staffName) {
        if (!confirm(`¿Quitar a ${staffName} de la planilla de esta noche?`)) {
            return;
        }

        const row = document.getElementById(`attendance-row-${attId}`);
        if (row) {
            row.style.transition = 'all 0.22s ease-out';
            row.style.opacity = '0.4';
            row.style.pointerEvents = 'none';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('input[name="_token"]')?.value;

            const res = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && (data.success || data.success === undefined)) {
                if (row) {
                    row.style.transform = 'translateX(12px)';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        renumberRows();
                        recalculateTotals();
                        showToast(`Se retiró a ${staffName} de la planilla.`);
                    }, 220);
                }
            } else {
                if (row) {
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'auto';
                }
                showToast(data.message || 'No se pudo retirar el personal.', 'error');
            }
        } catch (err) {
            console.error('Error al quitar personal:', err);
            if (row) {
                row.style.opacity = '1';
                row.style.pointerEvents = 'auto';
            }
            showToast('Error de conexión al retirar personal.', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('payroll-form');
        const btnSave = document.getElementById('btn-save-payroll');

        if (form && btnSave) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const originalBtnHtml = btnSave.innerHTML;
                btnSave.disabled = true;
                btnSave.classList.add('opacity-75');
                btnSave.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-black" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Guardando...</span>
                `;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                        || document.querySelector('input[name="_token"]')?.value;

                    const formData = new FormData(form);

                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await res.json().catch(() => ({}));

                    if (res.ok && (data.success || data.success === undefined)) {
                        btnSave.innerHTML = `
                            <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                            <span>Guardado</span>
                        `;
                        showToast('Planilla de pagos actualizada.');
                        recalculateTotals();

                        setTimeout(() => {
                            btnSave.disabled = false;
                            btnSave.classList.remove('opacity-75');
                            btnSave.innerHTML = originalBtnHtml;
                        }, 1800);
                    } else {
                        btnSave.disabled = false;
                        btnSave.classList.remove('opacity-75');
                        btnSave.innerHTML = originalBtnHtml;
                        showToast(data.message || 'Error al guardar los cambios.', 'error');
                    }
                } catch (err) {
                    console.error('Error al guardar planilla:', err);
                    btnSave.disabled = false;
                    btnSave.classList.remove('opacity-75');
                    btnSave.innerHTML = originalBtnHtml;
                    showToast('Error de conexión al guardar.', 'error');
                }
            });
        }
    });

    function openWageBatchDrawer() {
        const overlay = document.getElementById('wage-batch-drawer-overlay');
        const drawer = document.getElementById('wage-batch-drawer');
        if (overlay && drawer) {
            overlay.classList.add('is-open');
            drawer.classList.add('is-open');
            document.body.classList.add('drawer-open');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                const input = document.getElementById('wage_meseros');
                if (input) input.focus();
            }, 300);
        }
    }

    function closeWageBatchDrawer() {
        const overlay = document.getElementById('wage-batch-drawer-overlay');
        const drawer = document.getElementById('wage-batch-drawer');
        if (overlay && drawer) {
            overlay.classList.remove('is-open');
            drawer.classList.remove('is-open');
            document.body.classList.remove('drawer-open');
            document.body.style.overflow = '';
        }
    }

    let payrollRoleFilter = 'all';

    function filterPayrollByRole(role) {
        payrollRoleFilter = role;
        document.querySelectorAll('.payroll-role-pill').forEach(btn => {
            btn.className = 'payroll-role-pill px-3 py-1.5 rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700 hover:text-white transition-all cursor-pointer';
        });
        const activeBtn = document.getElementById('payroll-role-pill-' + role);
        if (activeBtn) {
            activeBtn.className = 'payroll-role-pill px-3 py-1.5 rounded-xl border border-[#F5B81C] bg-[#F5B81C] text-black font-black transition-all cursor-pointer';
        }
        applyPayrollFilters();
    }

    function filterPayrollTable(query) {
        applyPayrollFilters();
    }

    function applyPayrollFilters() {
        const q = (document.getElementById('payroll-search-input')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.payroll-table-row');
        const clearBtn = document.getElementById('clear-payroll-search');
        let visibleCount = 0;
        const visibleCats = new Set();

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', q.length === 0);
        }

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const role = row.getAttribute('data-role') || '';
            const cat = row.getAttribute('data-category') || '';

            const matchesQuery = !q || name.includes(q) || role.includes(q);
            const matchesRole = (payrollRoleFilter === 'all') || (cat === payrollRoleFilter);
            const isVisible = matchesQuery && matchesRole;

            row.style.display = isVisible ? '' : 'none';
            if (isVisible) {
                visibleCount++;
                visibleCats.add(cat);
            }
        });

        document.querySelectorAll('.payroll-cat-divider').forEach(divider => {
            const cat = divider.getAttribute('data-category');
            divider.style.display = visibleCats.has(cat) ? '' : 'none';
        });

        const emptyRow = document.getElementById('payroll-search-empty');
        if (emptyRow) {
            emptyRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }
    }

    function clearPayrollSearch() {
        const input = document.getElementById('payroll-search-input');
        if (input) {
            input.value = '';
            applyPayrollFilters();
            input.focus();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeWageBatchDrawer();
            clearPayrollSearch();
        }
    });
</script>
@endif
@endsection
