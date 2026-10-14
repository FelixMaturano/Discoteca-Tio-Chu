@extends('layouts.app')

@section('title', 'Historial de Pagos y Deudas del Personal')

@section('content')
<div class="space-y-4 max-w-7xl mx-auto pb-10 font-sans">

    <!-- ==========================================
         1. CABECERA: TÍTULO, SELECTOR DE NOCHE Y ACCIONES
         ========================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:px-4 sm:py-3 rounded-xl bg-[#09090b] border border-zinc-800/80 shadow-sm">
        
        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#F5B81C]/10 border border-[#F5B81C]/30 flex items-center justify-center text-[#F5B81C] shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <h1 class="text-base sm:text-xl font-black tracking-tight text-white truncate">
                    Historial de Pagos y Deudas
                </h1>
            </div>

            @if($session)
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-zinc-950 border border-zinc-800">
                    <span class="w-2 h-2 rounded-full {{ $session->isOpen() ? 'bg-emerald-500' : 'bg-zinc-600' }}"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $session->isOpen() ? 'text-zinc-300' : 'text-zinc-500' }}">
                        {{ $session->isOpen() ? 'Abierta' : 'Cerrada' }}
                    </span>
                </div>

                <div class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-zinc-400">
                    <span class="uppercase text-zinc-200 tracking-wide">{{ $session->day_name }}</span>
                    <span class="text-zinc-600">•</span>
                    <span class="font-mono text-zinc-300">{{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}</span>
                </div>
            @endif

            @if($allSessions->isNotEmpty())
                <form method="GET" action="{{ route('paymentHistory.index') }}" class="flex items-center w-full sm:w-auto">
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-zinc-950 border border-zinc-800 text-xs hover:border-[#F5B81C] transition-colors shadow-sm w-full sm:w-auto">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <select name="session_id" id="session_id" onchange="this.form.submit()" 
                                class="bg-transparent border-0 text-xs font-semibold text-zinc-200 focus:outline-none cursor-pointer pr-1 w-full">
                            @foreach($allSessions as $s)
                                <option value="{{ $s->id }}" {{ $session && $session->id === $s->id ? 'selected' : '' }} class="bg-zinc-950 text-white">
                                    {{ $s->day_name }} {{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }} ({{ $s->status === 'open' ? 'Abierta' : 'Cerrada' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:flex items-center gap-2 shrink-0 w-full sm:w-auto">
            @if($session)
                <a href="{{ route('staffPayments.index', ['session_id' => $session->id]) }}" 
                   class="justify-center px-3 py-1.5 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-[#F5B81C] text-xs font-semibold text-zinc-300 hover:text-white transition-all flex items-center gap-1.5 shadow-sm cursor-pointer active:scale-95 text-center">
                    <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                    <span>Planilla</span>
                </a>
            @endif

            <button type="button" onclick="openNightsHistoryDrawer()" 
                    class="justify-center px-3 py-1.5 rounded-lg bg-[#F5B81C]/10 border border-[#F5B81C]/40 hover:border-[#F5B81C] hover:bg-[#F5B81C]/20 text-[#F5B81C] text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm cursor-pointer active:scale-95 text-center">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Historial Global</span>
            </button>
        </div>
    </div>

    @if(!$session)
        <div class="rounded-xl p-10 text-center shadow-xl bg-[#09090b] border border-zinc-800/80">
            <h3 class="text-base font-bold text-white">No hay jornadas registradas</h3>
            <p class="text-xs text-zinc-400 mt-1 mb-5">Apertura una noche para consultar el historial de pagos y deudas.</p>
            <a href="{{ route('sessions.create') }}" class="inline-flex items-center px-5 py-2.5 bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs font-black rounded-lg transition-all shadow-md">
                + Crear Noche
            </a>
        </div>
    @else

        <!-- ==========================================
             2. 4 TARJETAS KPI RESUMEN (DISTINCIÓN VISUAL ABSOLUTA)
             ========================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- TARJETA 1: Deuda Pendiente de la Noche -->
            <div class="rounded-xl p-3.5 sm:p-4 border border-[#F5B81C]/30 bg-[#09090b] shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-[11px] font-black text-[#F5B81C] uppercase tracking-wider">Deuda de la Noche</span>
                    <span id="badge-deuda" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $totalDeuda > 0 ? 'bg-[#F5B81C]/15 border border-[#F5B81C]/40 text-[#F5B81C]' : 'bg-zinc-950 border border-zinc-800 text-zinc-400' }}">
                        {{ $totalDeuda > 0 ? 'Por Pagar' : 'Al Día' }}
                    </span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-black font-mono text-[#F5B81C] tracking-tight">
                    <span class="text-xs mr-0.5">Bs.</span><span id="kpi-deuda">{{ number_format($totalDeuda, 2) }}</span>
                </div>
                <p class="text-[11px] text-zinc-400 mt-1">
                    <span id="kpi-deuda-count" class="font-bold text-[#F5B81C]">{{ $conDeudaCount }}</span> de {{ $totalTrabajadores }} con saldo pendiente
                </p>
            </div>

            <!-- TARJETA 2: Pagado esa Noche -->
            <div class="rounded-xl p-3.5 sm:p-4 border border-emerald-500/30 bg-[#09090b] shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Pagado esa Noche</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">Liquidado</span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-black font-mono text-emerald-400 tracking-tight">
                    <span class="text-xs mr-0.5">Bs.</span><span id="kpi-pagado">{{ number_format($totalPagado, 2) }}</span>
                </div>
                <p class="text-[11px] text-zinc-400 mt-1">
                    <span id="kpi-pagado-count" class="font-bold text-emerald-400">{{ $pagadosCount }}</span> cobraron jornal
                </p>
            </div>

            <!-- TARJETA 3: Total Planilla -->
            <div class="rounded-xl p-3.5 sm:p-4 border border-zinc-800/80 bg-[#09090b] shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-[11px] font-bold text-zinc-300 uppercase tracking-wider">Planilla de Turno</span>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase bg-zinc-950 border border-zinc-800 px-2 py-0.5 rounded-md font-mono">{{ $totalTrabajadores }} pers.</span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-black font-mono text-white tracking-tight">
                    <span class="text-xs text-zinc-500 mr-0.5">Bs.</span>{{ number_format($totalPlanilla, 2) }}
                </div>
                <p class="text-[11px] text-zinc-500 mt-1">
                    Total asignado a la noche
                </p>
            </div>

            <!-- TARJETA 4: Deuda Global del Local -->
            <div onclick="openNightsHistoryDrawer()" 
                 class="rounded-xl p-3.5 sm:p-4 border border-zinc-800/80 bg-[#09090b] hover:border-[#F5B81C] cursor-pointer transition-all group shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-[11px] font-black text-[#F5B81C] uppercase tracking-wider">Deuda Global</span>
                    <span class="text-xs font-bold text-[#F5B81C] group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-black font-mono text-[#F5B81C] tracking-tight">
                    <span class="text-xs mr-0.5">Bs.</span>{{ number_format($globalSummary['total_deuda_historica'], 2) }}
                </div>
                <p class="text-[11px] text-zinc-400 mt-1">
                    En {{ $globalSummary['noches_con_deuda'] }} noches pendientes
                </p>
            </div>

        </div>

        <!-- ==========================================
             3. BARRA DE FILTROS & BÚSQUEDA COMPACTA
             ========================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 p-3 sm:px-4 sm:py-2.5 rounded-xl bg-[#09090b] border border-zinc-800/80 shadow-sm">
            
            <!-- Switch Segmentado -->
            <div class="grid grid-cols-3 sm:inline-flex p-1 bg-zinc-950 border border-zinc-800 rounded-lg gap-1 text-xs w-full sm:w-auto">
                <button type="button" onclick="setCardFilter('all')" id="filter-btn-all"
                        class="filter-pill-btn px-2 sm:px-3 py-1.5 sm:py-1 rounded-md font-black transition-all bg-[#F5B81C] text-black shadow-sm cursor-pointer text-center">
                    Todos ({{ $totalTrabajadores }})
                </button>
                <button type="button" onclick="setCardFilter('unpaid')" id="filter-btn-unpaid"
                        class="filter-pill-btn px-2 sm:px-3 py-1.5 sm:py-1 rounded-md font-semibold transition-all text-zinc-400 hover:text-white hover:bg-zinc-900 cursor-pointer text-center">
                    Deuda ({{ $conDeudaCount }})
                </button>
                <button type="button" onclick="setCardFilter('paid')" id="filter-btn-paid"
                        class="filter-pill-btn px-2 sm:px-3 py-1.5 sm:py-1 rounded-md font-semibold transition-all text-zinc-400 hover:text-white hover:bg-zinc-900 cursor-pointer text-center">
                    Al Día ({{ $pagadosCount }})
                </button>
            </div>

            <!-- Buscador en tiempo real -->
            <div class="relative w-full sm:w-64">
                <svg class="w-3.5 h-3.5 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="worker-search-input" oninput="applyCardFilters()" placeholder="Buscar personal o cargo..." 
                       class="w-full text-xs bg-zinc-950 border border-zinc-800 rounded-lg pl-8 pr-7 py-1.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C] transition-all">
                <button type="button" id="clear-search-btn" onclick="clearWorkerSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-white text-xs cursor-pointer">
                    &times;
                </button>
            </div>
        </div>

        <!-- ==========================================
             4. MALLA DE TARJETAS DE PERSONAL (DISTINCIÓN PERSONAL vs MONTOS)
             ========================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5" id="worker-cards-grid">
            @forelse($attendances as $att)
                @php
                    $hasDebt = !$att->is_paid;
                    $roleName = str_ireplace(['mozo', 'mozos'], ['MESERO', 'MESEROS'], $att->staff->role ?? 'Staff');
                @endphp
                <div class="worker-card rounded-2xl bg-[#09090b] border {{ $hasDebt ? 'border-[#F5B81C]/30 shadow-sm' : 'border-zinc-800/80' }} hover:border-zinc-700/80 p-4 shadow-sm transition-all flex flex-col justify-between gap-3.5"
                     id="attendance-card-{{ $att->id }}"
                     data-paid="{{ $att->is_paid ? 'true' : 'false' }}"
                     data-search="{{ strtolower(($att->staff->name ?? '') . ' ' . $roleName) }}">
                    
                    <!-- BLOQUE 1: IDENTIDAD DEL PERSONAL (Nombre, Rol y Estado Claro) -->
                    <div class="flex items-center justify-between gap-2.5 pb-2.5 border-b border-zinc-800/80">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl {{ $hasDebt ? 'bg-[#F5B81C]/10 text-[#F5B81C] border border-[#F5B81C]/30' : 'bg-zinc-900 text-zinc-300 border border-zinc-800' }} flex items-center justify-center text-xs font-black shrink-0">
                                {{ substr($att->staff->name ?? 'T', 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-white text-sm truncate uppercase tracking-tight leading-tight">
                                    {{ $att->staff->name }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wide">
                                        {{ strtoupper($roleName) }}
                                    </span>
                                    @if(!empty($att->staff->phone))
                                        <span class="text-zinc-600 text-[10px]">•</span>
                                        <span class="font-mono text-[10px] text-zinc-400">{{ $att->staff->phone }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Pill de Estado: Inconfundible Por Pagar vs Pagado -->
                        <div class="status-container shrink-0">
                            @if($att->is_paid)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Pagado esa noche
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase bg-[#F5B81C]/10 border border-[#F5B81C]/30 text-[#F5B81C]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#F5B81C]"></span>
                                    Por Pagar
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- BLOQUE 2: DESGLOSE DE MONTOS (Jornal vs Deuda / Saldo Separados) -->
                    <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-zinc-950 border border-zinc-800/80">
                        <!-- Jornal Fijo del Turno -->
                        <div class="border-r border-zinc-800/80 pr-2">
                            <span class="text-[9px] font-bold text-zinc-500 uppercase tracking-wider block">Jornal Fijo</span>
                            <div class="font-mono font-bold text-zinc-200 text-sm mt-0.5">
                                <span class="text-[10px] text-zinc-500 font-sans mr-0.5">Bs.</span>{{ number_format($att->pay_amount, 2) }}
                            </div>
                            <span class="text-[9px] text-zinc-600 block mt-0.5">Tarifa pactada</span>
                        </div>

                        <!-- Estado de Deuda / Saldo -->
                        <div class="pl-1 text-right debt-container">
                            @if($hasDebt)
                                <span class="text-[9px] font-black text-[#F5B81C] uppercase tracking-wider block">Se le debe:</span>
                                <div class="font-mono font-black text-[#F5B81C] text-sm mt-0.5">
                                    <span class="text-[10px] font-sans mr-0.5">Bs.</span>{{ number_format($att->debt_amount, 2) }}
                                </div>
                                <span class="text-[9px] text-[#F5B81C]/80 font-bold block mt-0.5">Falta abonar</span>
                            @else
                                <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-wider block">Deuda: Bs. 0.00</span>
                                <div class="font-mono font-bold text-emerald-400 text-sm mt-0.5">
                                    <span class="text-[10px] font-sans mr-0.5">Bs.</span>0.00
                                </div>
                                <span class="text-[9px] text-zinc-500 block mt-0.5">Liquidado</span>
                            @endif
                        </div>
                    </div>

                    <!-- BLOQUE 3: ACCIÓN DE PAGO (Botón Claro y Explícito) -->
                    <div class="action-container pt-0.5">
                        @if($hasDebt)
                            <button type="button" 
                                    onclick="paySingleWorker({{ $att->id }}, '{{ route('paymentHistory.paySingle', $att) }}', '{{ addslashes($att->staff->name) }}', {{ $att->pay_amount }})"
                                    class="w-full py-2 px-3 bg-[#F5B81C] hover:bg-[#e5ac18] text-black font-black text-xs uppercase tracking-wider rounded-xl shadow-md active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-8-6h16"/>
                                </svg>
                                <span>Pagar Jornal (Bs. {{ number_format($att->pay_amount, 2) }})</span>
                            </button>
                        @else
                            <div class="w-full py-2 px-3 rounded-xl bg-zinc-950 border border-zinc-800/80 text-zinc-400 text-xs font-bold text-center flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Jornal Liquidado al Día</span>
                            </div>
                        @endif
                    </div>

                </div>
            @empty
                <div class="col-span-full py-12 text-center text-xs text-zinc-500 rounded-xl bg-[#09090b] border border-zinc-800">
                    No hay personal asignado para la noche seleccionada.
                </div>
            @endforelse
        </div>

    @endif

</div>

<!-- TOAST FLOTANTE DISCRETO -->
<div id="discreet-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-8 opacity-0 pointer-events-none transition-all duration-300 ease-out flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700/80 shadow-2xl text-xs font-medium text-white backdrop-blur-md">
    <div id="discreet-toast-icon" class="w-2 h-2 rounded-full bg-[#F5B81C]"></div>
    <span id="discreet-toast-msg" class="tracking-wide">Notificación</span>
</div>

<!-- DRAWER: HISTORIAL DE NOCHES & DEUDAS GLOBALES -->
<div id="nights-history-drawer-overlay" class="drawer-overlay" onclick="closeNightsHistoryDrawer()"></div>

<div id="nights-history-drawer" class="drawer-panel" style="z-index: 60;">
    <div class="drawer-header">
        <div>
            <h2 class="text-base font-black text-white tracking-tight">Historial de Noches y Deudas</h2>
            <p class="text-xs text-zinc-400 mt-0.5">Selecciona cualquier noche para auditar y liquidar deudas pendientes.</p>
        </div>
        <button type="button" onclick="closeNightsHistoryDrawer()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="drawer-body space-y-3">
        <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase text-zinc-400 block tracking-wider">Deuda Acumulada Total</span>
                <span class="text-lg font-black font-mono text-[#F5B81C]">Bs. {{ number_format($globalSummary['total_deuda_historica'], 2) }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-zinc-400 block font-bold uppercase tracking-wider">Noches con Deuda</span>
                <span class="text-sm font-bold font-mono text-zinc-300">{{ $globalSummary['noches_con_deuda'] }} noches</span>
            </div>
        </div>

        <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Selecciona una noche:</p>

        <div class="space-y-2">
            @forelse($nightsWithDebts as $item)
                @php
                    $sItem = $item['session'];
                    $isSelected = $session && $session->id === $sItem->id;
                @endphp
                <a href="{{ route('paymentHistory.index', ['session_id' => $sItem->id]) }}" 
                   class="block p-3 rounded-xl border {{ $isSelected ? 'border-[#F5B81C] bg-[#F5B81C]/5' : 'border-zinc-800/80 bg-zinc-950 hover:border-zinc-700' }} transition-all group">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sItem->isOpen() ? 'bg-emerald-500' : 'bg-zinc-600' }}"></span>
                            <span class="text-xs font-bold text-white font-sans uppercase">
                                {{ $sItem->day_name }} {{ \Carbon\Carbon::parse($sItem->session_date)->format('d/m/Y') }}
                            </span>
                            @if($isSelected)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-[#F5B81C] text-black">Viendo</span>
                            @endif
                        </div>
                        <span class="text-xs font-mono {{ $item['has_debt'] ? 'text-[#F5B81C] font-bold' : 'text-zinc-400' }}">
                            {{ $item['has_debt'] ? 'Deuda: Bs. ' . number_format($item['total_debt'], 2) : 'Al Día' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-[10px] text-zinc-400 pt-1.5 border-t border-zinc-800/60 font-sans">
                        <div>
                            <span class="text-zinc-500 block text-[9px] uppercase font-semibold">Planilla</span>
                            <span class="font-bold text-zinc-300 font-mono">Bs. {{ number_format($item['total_payroll'], 2) }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 block text-[9px] uppercase font-semibold">Pagado</span>
                            <span class="font-bold text-zinc-300 font-mono">Bs. {{ number_format($item['total_paid'], 2) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-zinc-500 block text-[9px] uppercase font-semibold">Sin Cobrar</span>
                            <span class="font-bold text-zinc-300 font-mono">
                                {{ $item['unpaid_count'] }} pers.
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-6 text-center text-xs text-zinc-500">
                    No hay noches registradas en el sistema.
                </div>
            @endforelse
        </div>
    </div>
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

    let currentFilter = 'all';

    function setCardFilter(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-pill-btn').forEach(btn => {
            btn.className = 'filter-pill-btn px-3 py-1 rounded-md font-semibold transition-all text-zinc-400 hover:text-white hover:bg-zinc-900 cursor-pointer';
        });

        const activeBtn = document.getElementById('filter-btn-' + filter);
        if (activeBtn) {
            activeBtn.className = 'filter-pill-btn px-3 py-1 rounded-md font-black transition-all bg-[#F5B81C] text-black shadow-sm cursor-pointer';
        }

        applyCardFilters();
    }

    function applyCardFilters() {
        const query = (document.getElementById('worker-search-input')?.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.worker-card');
        const clearBtn = document.getElementById('clear-search-btn');

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', query.length === 0);
        }

        cards.forEach(card => {
            const isPaid = card.getAttribute('data-paid') === 'true';
            const searchData = card.getAttribute('data-search') || '';

            let matchesFilter = true;
            if (currentFilter === 'unpaid' && isPaid) matchesFilter = false;
            if (currentFilter === 'paid' && !isPaid) matchesFilter = false;

            let matchesSearch = true;
            if (query && !searchData.includes(query)) matchesSearch = false;

            if (matchesFilter && matchesSearch) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function clearWorkerSearch() {
        const input = document.getElementById('worker-search-input');
        if (input) {
            input.value = '';
            applyCardFilters();
            input.focus();
        }
    }

    async function paySingleWorker(attId, payUrl, staffName, amount) {
        if (!confirm(`¿Registrar el pago de Bs. ${amount.toFixed(2)} para ${staffName}?`)) {
            return;
        }

        const card = document.getElementById(`attendance-card-${attId}`);
        if (card) {
            card.style.transition = 'all 0.2s ease-out';
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('input[name="_token"]')?.value;

            const res = await fetch(payUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && (data.success || data.success === undefined)) {
                if (card) {
                    card.setAttribute('data-paid', 'true');
                    card.classList.remove('border-[#F5B81C]/30');
                    card.classList.add('border-zinc-800/80');

                    // Actualizar status a formato limpio y sobrio
                    const statusEl = card.querySelector('.status-container');
                    if (statusEl) {
                        statusEl.innerHTML = `
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Pagado
                            </span>
                        `;
                    }

                    // Actualizar deuda
                    const debtEl = card.querySelector('.debt-container');
                    if (debtEl) {
                        debtEl.innerHTML = `
                            <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-wider block">Saldo Actual</span>
                            <div class="font-mono font-bold text-emerald-400 text-sm mt-0.5">
                                <span class="text-[10px] font-sans mr-0.5">Bs.</span>0.00
                            </div>
                            <span class="text-[9px] text-zinc-500 block mt-0.5">Liquidado</span>
                        `;
                    }

                    // Actualizar acción
                    const actionEl = card.querySelector('.action-container');
                    if (actionEl) {
                        actionEl.innerHTML = `
                            <div class="w-full py-2 px-3 rounded-xl bg-zinc-950 border border-zinc-800/80 text-zinc-400 text-xs font-bold text-center flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Jornal Liquidado al Día</span>
                            </div>
                        `;
                    }

                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';

                    // Actualizar KPIs
                    const formatMoney = (val) => Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    if (data.totalDeuda !== undefined) {
                        const elDeuda = document.getElementById('kpi-deuda');
                        if (elDeuda) elDeuda.textContent = formatMoney(data.totalDeuda);

                        const badgeDeuda = document.getElementById('badge-deuda');
                        if (badgeDeuda) {
                            badgeDeuda.textContent = data.totalDeuda > 0 ? 'Por Pagar' : 'Al Día';
                            badgeDeuda.className = data.totalDeuda > 0 
                                ? 'px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-[#F5B81C]/15 border border-[#F5B81C]/40 text-[#F5B81C]'
                                : 'px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-zinc-950 border border-zinc-800 text-zinc-400';
                        }
                    }
                    if (data.totalPagado !== undefined) {
                        const elPagado = document.getElementById('kpi-pagado');
                        if (elPagado) elPagado.textContent = formatMoney(data.totalPagado);
                    }

                    showToast(`Pago registrado para ${staffName}.`);
                    applyCardFilters();
                } else {
                    window.location.reload();
                }
            } else {
                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
                showToast(data.message || 'No se pudo procesar el pago.', 'error');
            }
        } catch (err) {
            console.error('Error al registrar pago:', err);
            if (card) {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
            showToast('Error de conexión al procesar el pago.', 'error');
        }
    }

    function openNightsHistoryDrawer() {
        const overlay = document.getElementById('nights-history-drawer-overlay');
        const drawer = document.getElementById('nights-history-drawer');
        if (overlay && drawer) {
            overlay.classList.add('is-open');
            drawer.classList.add('is-open');
            document.body.classList.add('drawer-open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeNightsHistoryDrawer() {
        const overlay = document.getElementById('nights-history-drawer-overlay');
        const drawer = document.getElementById('nights-history-drawer');
        if (overlay && drawer) {
            overlay.classList.remove('is-open');
            drawer.classList.remove('is-open');
            document.body.classList.remove('drawer-open');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeNightsHistoryDrawer();
    });
</script>
@endsection
