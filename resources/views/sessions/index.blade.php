@extends('layouts.app')

@section('title', 'Historial de Noches & Consolidado')

@section('content')
<style>
    /* Color amarillo dorado oficial Tío Chu para los checkboxes al marcarse */
    input[type="checkbox"].session-chk,
    #check-all-desktop {
        accent-color: #F5B81C !important;
        cursor: pointer;
    }
</style>
<div class="space-y-6 max-w-7xl mx-auto pb-24">

    @php
        $openSession = $sessions->firstWhere('status', 'open');
        $closedCount = $sessions->where('status', 'closed')->count();
        $totalCount = $sessions->count();
    @endphp

    <!-- ==========================================
         CABECERA MINIMALISTA (iOS PURE DARK)
         ========================================== -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-zinc-800/80">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono font-bold text-[#F5B81C] uppercase tracking-wider">
                    Control Operativo
                </span>
                <span class="text-zinc-600 text-xs">•</span>
                <span class="text-xs text-zinc-400 font-mono">Jornadas Nocturnas</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white font-sans">
                Historial de Noches de Atención
            </h1>
            <p class="text-xs text-zinc-400 mt-1">
                Selecciona noches individuales o fines de semana para consolidar arqueos y métodos de pago.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full lg:w-auto">
            <!-- Botón Generar Consolidado -->
            <button type="button" onclick="openConsolidatedModal()" id="btn-header-consolidated"
                    class="flex-1 sm:flex-none justify-center inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-zinc-200 hover:text-[#F5B81C] border border-zinc-800 text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                <svg class="w-4 h-4 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Ver Resumen (<span id="header-selected-count">0</span>)</span>
            </button>

            <a href="{{ route('sessions.create') }}" 
               class="w-full sm:w-auto justify-center inline-flex items-center gap-2 px-4 py-2.5 bg-[#F5B81C] hover:bg-[#e5ac18] text-zinc-950 text-xs font-black tracking-wider uppercase rounded-xl shadow-sm active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Aperturar Nueva Noche</span>
            </a>
        </div>
    </div>

    <!-- ==========================================
         KPIs RESUMEN DE JORNADAS
         ========================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Total Noches -->
        <div class="theme-card rounded-2xl p-3.5 sm:p-5 border border-zinc-800/80 bg-[#09090b]">
            <span class="text-[11px] sm:text-xs font-mono font-semibold text-zinc-400 uppercase tracking-wider">Total Noches</span>
            <div class="mt-1 sm:mt-2 text-xl sm:text-2xl font-black font-mono text-white tracking-tight">
                {{ $totalCount }}
            </div>
            <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-0.5 sm:mt-1 font-mono truncate">Jornadas registradas</p>
        </div>

        <!-- Noche Activa -->
        <div class="theme-card rounded-2xl p-3.5 sm:p-5 border {{ $openSession ? 'border-emerald-500/30' : 'border-zinc-800/80' }} bg-[#09090b]">
            <span class="text-[11px] sm:text-xs font-mono font-semibold {{ $openSession ? 'text-emerald-400' : 'text-zinc-400' }} uppercase tracking-wider">Noche en Curso</span>
            <div class="mt-1 sm:mt-2 text-xl sm:text-2xl font-black font-mono {{ $openSession ? 'text-emerald-400' : 'text-zinc-500' }} tracking-tight truncate">
                @if($openSession)
                    {{ $openSession->day_name }}
                @else
                    Ninguna
                @endif
            </div>
            <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-0.5 sm:mt-1 font-mono truncate">
                @if($openSession)
                    {{ \Carbon\Carbon::parse($openSession->session_date)->format('d/m/Y') }} (Abierta)
                @else
                    Todas cerradas
                @endif
            </p>
        </div>

        <!-- Noches Cerradas -->
        <div class="theme-card rounded-2xl p-3.5 sm:p-5 border border-zinc-800/80 bg-[#09090b]">
            <span class="text-[11px] sm:text-xs font-mono font-semibold text-zinc-400 uppercase tracking-wider">Noches Cerradas</span>
            <div class="mt-1 sm:mt-2 text-xl sm:text-2xl font-black font-mono text-zinc-200 tracking-tight">
                {{ $closedCount }}
            </div>
            <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-0.5 sm:mt-1 font-mono truncate">Arqueadas en Cierre</p>
        </div>

        <!-- Comisión POS Promedio -->
        <div class="theme-card rounded-2xl p-3.5 sm:p-5 border border-zinc-800/80 bg-[#09090b]">
            <span class="text-[11px] sm:text-xs font-mono font-semibold text-zinc-400 uppercase tracking-wider">Comisión POS</span>
            <div class="mt-1 sm:mt-2 text-xl sm:text-2xl font-black font-mono text-[#F5B81C] tracking-tight">
                {{ number_format(($sessions->avg('pos_commission_rate') ?? 0.013) * 100, 2) }}%
            </div>
            <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-0.5 sm:mt-1 font-mono truncate">Tasa promedio</p>
        </div>

    </div>

    <!-- ==========================================
         TARJETAS MÓVILES (md:hidden)
         ========================================== -->
    <div class="md:hidden space-y-3">
        <div class="flex items-center justify-between px-1">
            <span class="text-xs font-bold text-zinc-400 font-mono uppercase tracking-wider">Jornadas ({{ $totalCount }})</span>
            <span class="text-[11px] text-zinc-500">Toca las casillas para consolidar</span>
        </div>
        
        @forelse($sessions as $s)
            @php
                $isOpen = $s->isOpen();
            @endphp
            <div class="theme-card rounded-2xl p-4 border {{ $isOpen ? 'border-emerald-500/40 bg-emerald-950/10' : 'border-zinc-800/80 bg-[#09090b]' }} space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" value="{{ $s->id }}" data-day="{{ $s->day_name }}" data-date="{{ $s->session_date }}"
                               class="session-chk mt-1 w-5 h-5 rounded-lg border-zinc-700 bg-black text-[#F5B81C] cursor-pointer"
                               style="accent-color: #F5B81C !important;"
                               onchange="onSessionCheckChange(this)">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $isOpen ? 'bg-emerald-500 animate-pulse' : 'bg-zinc-600' }}"></span>
                                <span class="text-base font-bold font-mono text-white">{{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }}</span>
                                <span class="text-xs text-zinc-400 font-medium">({{ $s->day_name }})</span>
                            </div>
                            @if($s->notes)
                                <p class="text-xs text-zinc-400 mt-1 italic">{{ $s->notes }}</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if($isOpen)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Abierta
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-900 text-zinc-400 border border-zinc-800">
                                Cerrada
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-zinc-800/60 font-mono">
                    <span class="text-zinc-500">Comisión POS:</span>
                    <span class="font-bold text-zinc-300">{{ number_format($s->pos_commission_rate * 100, 2) }}%</span>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('dashboard', ['session_id' => $s->id]) }}" 
                       class="flex-1 justify-center inline-flex items-center gap-1.5 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700/80 active:bg-zinc-800 text-xs font-mono font-bold text-[#F5B81C] transition-all">
                        <span>Operar Noche</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>

                    <form method="POST" action="{{ route('sessions.destroy', $s) }}" class="inline" 
                          onsubmit="return confirm('¿Estás seguro de eliminar esta noche del {{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }}? Se borrarán sus inventarios, facturas y cobros asociados.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2.5 text-xs text-rose-400 active:bg-rose-500/20 font-semibold rounded-xl bg-rose-500/10 border border-rose-500/20 transition-all cursor-pointer">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-zinc-500 theme-card rounded-2xl border border-zinc-800/80 bg-[#09090b]">
                <p class="text-sm font-semibold text-zinc-400">No hay noches registradas</p>
                <p class="text-xs text-zinc-500 mt-1">Apertura una nueva noche para comenzar.</p>
            </div>
        @endforelse
    </div>

    <!-- ==========================================
         TABLA DE NOCHES (DESKTOP: hidden md:block)
         ========================================== -->
    <div class="hidden md:block theme-card rounded-2xl border border-zinc-800/80 bg-[#09090b] shadow-xl overflow-hidden">
        <div class="px-5 py-3.5 bg-zinc-950/60 border-b border-zinc-800/80 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-xs font-bold text-zinc-200 uppercase tracking-wider font-mono">
                    Registro de Jornadas Nocturnas
                </h3>
                <span class="text-xs font-mono text-zinc-500">
                    ({{ $totalCount }} noches en total)
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="selectWeekend()" title="Seleccionar Fin de Semana" class="text-xs text-[#F5B81C] hover:underline font-mono">
                    + Marcar Fin de Semana
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-zinc-950/40 border-b border-zinc-800/80 text-zinc-400 font-mono uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">
                            <input type="checkbox" id="check-all-desktop" onchange="toggleSelectAll(this)" title="Marcar todas" 
                                   class="w-4 h-4 rounded border-zinc-700 bg-black text-[#F5B81C] cursor-pointer"
                                   style="accent-color: #F5B81C !important;">
                        </th>
                        <th class="px-5 py-3.5">Fecha</th>
                        <th class="px-5 py-3.5">Día</th>
                        <th class="px-5 py-3.5 text-center">Comisión POS</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5">Observaciones</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 font-sans">
                    @forelse($sessions as $s)
                        @php
                            $isOpen = $s->isOpen();
                        @endphp
                        <tr class="hover:bg-zinc-900/40 transition-colors {{ $isOpen ? 'bg-[#F5B81C]/[0.02]' : '' }}" data-session-row="{{ $s->id }}">
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" value="{{ $s->id }}" data-day="{{ $s->day_name }}" data-date="{{ $s->session_date }}"
                                       class="session-chk w-4 h-4 rounded border-zinc-700 bg-black text-[#F5B81C] cursor-pointer"
                                       style="accent-color: #F5B81C !important;"
                                       onchange="onSessionCheckChange(this)">
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-white text-sm">
                                <div class="flex items-center gap-2.5">
                                    @if($isOpen)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-zinc-600"></span>
                                    @endif
                                    <span>{{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }}</span>
                                    @if($isOpen)
                                        <span class="text-[9px] font-bold text-[#F5B81C] uppercase tracking-wider bg-[#F5B81C]/10 px-2 py-0.5 rounded-md border border-[#F5B81C]/30">
                                            En curso
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-zinc-200">
                                {{ $s->day_name }}
                            </td>
                            <td class="px-5 py-4 text-center font-mono font-bold text-zinc-300">
                                {{ number_format($s->pos_commission_rate * 100, 2) }}%
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($isOpen)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Abierta
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-900 text-zinc-400 border border-zinc-800">
                                        Cerrada
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-zinc-400 font-sans">
                                {{ $s->notes ?? 'Sin observaciones' }}
                            </td>
                            <td class="px-5 py-4 text-right space-x-2.5 whitespace-nowrap">
                                <a href="{{ route('dashboard', ['session_id' => $s->id]) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-xs font-mono font-bold text-[#F5B81C] hover:text-amber-300 transition-all">
                                    <span>Operar</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('sessions.destroy', $s) }}" class="inline" 
                                      onsubmit="return confirm('¿Estás seguro de eliminar esta noche del {{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }}? Se borrarán sus inventarios, facturas y cobros asociados.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 text-xs text-rose-400 hover:text-rose-300 font-semibold rounded-lg hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 transition-all cursor-pointer">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-zinc-500">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <p class="text-sm font-semibold text-zinc-400">No hay noches registradas</p>
                                    <p class="text-xs text-zinc-500">Apertura una nueva noche para comenzar a operar inventarios, personal y cobros.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==========================================
         FLOATING ACTION BAR (STICKY BOTTOM PILL)
         ========================================== -->
    <div id="floating-consolidated-bar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-40 max-w-lg w-[92%] sm:w-auto">
        <div class="theme-card bg-[#09090b]/95 backdrop-blur-md border border-[#F5B81C]/50 rounded-2xl p-2.5 sm:px-5 sm:py-3 shadow-2xl flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#F5B81C] animate-pulse"></span>
                <span class="font-bold text-white font-sans">
                    <span id="floating-selected-count" class="font-mono text-[#F5B81C] text-sm">0</span> noches seleccionadas
                </span>
                <button type="button" onclick="deselectAll()" class="text-[11px] text-zinc-400 hover:text-white underline ml-1 cursor-pointer">
                    Desmarcar
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="openConsolidatedModal()" 
                        class="px-4 py-2 bg-[#F5B81C] hover:bg-[#e5ac18] text-black font-extrabold text-xs rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-1.5 dilemo-btn">
                    <span>Ver Resumen</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </button>

                <button type="button" onclick="goToPrintConsolidated()" title="Imprimir Comprobante Oficial"
                        class="p-2 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-300 hover:text-white hover:border-zinc-500 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ==========================================
         MODAL INTERACTIVO DE RESUMEN CONSOLIDADO
         ========================================== -->
    <div id="modal-consolidated-summary" class="hidden fixed inset-0 z-50 bg-black/85 flex items-center justify-center p-3 sm:p-5 backdrop-blur-sm overflow-y-auto">
        <div class="theme-card p-4 sm:p-6 rounded-2xl border border-zinc-800 bg-[#09090b] max-w-4xl w-full shadow-2xl max-h-[92vh] overflow-y-auto space-y-5 my-auto">
            
            <!-- Encabezado Modal -->
            <div class="flex items-start justify-between border-b border-zinc-800 pb-3">
                <div>
                    <span class="text-[10px] uppercase tracking-wider text-[#F5B81C] font-mono font-bold block">
                        Liquidación Multisesión • Tío Chu
                    </span>
                    <h2 class="text-lg sm:text-xl font-bold text-white font-sans mt-0.5">
                        Resumen Consolidado de Jornadas Nocturnas
                    </h2>
                    <p class="text-xs text-zinc-400 mt-0.5" id="modal-consolidated-dates">
                        Cargando información...
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="goToPrintConsolidated()" title="Abrir versión para impresión"
                            class="px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs font-bold text-zinc-200 hover:text-white flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span class="hidden sm:inline">Imprimir Oficial</span>
                    </button>
                    <button type="button" onclick="closeConsolidatedModal()" class="text-zinc-400 hover:text-white text-xl font-bold cursor-pointer p-1">&times;</button>
                </div>
            </div>

            <!-- Loader Spinner -->
            <div id="modal-consolidated-loading" class="py-16 text-center space-y-3">
                <div class="w-10 h-10 border-4 border-[#F5B81C] border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-xs font-mono text-zinc-400">Calculando recaudación y consolidando noches...</p>
            </div>

            <!-- Contenido Dinámico de Datos -->
            <div id="modal-consolidated-data" class="hidden space-y-5">
                
                <!-- 1. 4 KPIs Globales -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800">
                        <span class="text-[10px] text-zinc-400 uppercase font-mono tracking-wider block">Total Ingresos</span>
                        <div class="mt-1 text-lg sm:text-xl font-black font-mono text-[#F5B81C]" id="m-kpi-ingresos">Bs. 0.00</div>
                        <span class="text-[10px] text-zinc-500 font-mono">Efectivo + QR + Tarjetas</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800">
                        <span class="text-[10px] text-rose-400 uppercase font-mono tracking-wider block">Total Egresos</span>
                        <div class="mt-1 text-lg sm:text-xl font-black font-mono text-rose-400" id="m-kpi-egresos">Bs. 0.00</div>
                        <span class="text-[10px] text-zinc-500 font-mono">Personal + Gastos</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-emerald-500/30">
                        <span class="text-[10px] text-emerald-400 uppercase font-mono tracking-wider block">Ganancia Neta</span>
                        <div class="mt-1 text-lg sm:text-xl font-black font-mono text-emerald-400" id="m-kpi-neto">Bs. 0.00</div>
                        <span class="text-[10px] text-zinc-500 font-mono">Saldo neto en caja</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800">
                        <span class="text-[10px] text-zinc-400 uppercase font-mono tracking-wider block">Ventas Totales</span>
                        <div class="mt-1 text-lg sm:text-xl font-black font-mono text-white" id="m-kpi-ventas">Bs. 0.00</div>
                        <span class="text-[10px] text-zinc-500 font-mono">Barras y Tienda</span>
                    </div>
                </div>

                <!-- 2. Desglose Métodos de Pago -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800 space-y-2">
                        <div class="flex items-center justify-between border-b border-zinc-900 pb-1.5">
                            <span class="text-xs font-bold text-emerald-400 uppercase font-mono">Efectivo Total</span>
                            <span class="font-bold font-mono text-white text-xs" id="m-pay-cash">Bs. 0.00</span>
                        </div>
                        <div class="text-[11px] space-y-1 text-zinc-400">
                            <div class="flex justify-between">
                                <span>Barra Principal:</span>
                                <span class="font-mono text-zinc-300" id="m-pay-cash-principal">Bs. 0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Barra Subterráneo:</span>
                                <span class="font-mono text-zinc-300" id="m-pay-cash-subte">Bs. 0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tienda:</span>
                                <span class="font-mono text-zinc-300" id="m-pay-cash-tienda">Bs. 0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800 space-y-2">
                        <div class="flex items-center justify-between border-b border-zinc-900 pb-1.5">
                            <span class="text-xs font-bold text-blue-400 uppercase font-mono">Cobros QR</span>
                            <span class="font-bold font-mono text-white text-xs" id="m-pay-qr">Bs. 0.00</span>
                        </div>
                        <div class="text-[11px] space-y-1 text-zinc-400">
                            <div class="flex justify-between">
                                <span>YASTA (Unión):</span>
                                <span class="font-mono text-zinc-300" id="m-pay-qr-yasta">Bs. 0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>YAPE (BCP):</span>
                                <span class="font-mono text-zinc-300" id="m-pay-qr-yape">Bs. 0.00</span>
                            </div>
                            <div class="flex justify-between text-zinc-500 pt-1 border-t border-zinc-900">
                                <span>Total QR:</span>
                                <span class="font-mono font-bold text-blue-400" id="m-pay-qr-total">Bs. 0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-800 space-y-2">
                        <div class="flex items-center justify-between border-b border-zinc-900 pb-1.5">
                            <span class="text-xs font-bold text-purple-400 uppercase font-mono">Tarjetas POS</span>
                            <span class="font-bold font-mono text-white text-xs" id="m-pay-card-gross">Bs. 0.00</span>
                        </div>
                        <div class="text-[11px] space-y-1 text-zinc-400">
                            <div class="flex justify-between">
                                <span>Monto Bruto:</span>
                                <span class="font-mono text-zinc-300" id="m-pay-card-bruto">Bs. 0.00</span>
                            </div>
                            <div class="flex justify-between text-rose-400/80">
                                <span>Comisión POS:</span>
                                <span class="font-mono" id="m-pay-card-comision">- Bs. 0.00</span>
                            </div>
                            <div class="flex justify-between text-emerald-400 font-bold pt-1 border-t border-zinc-900">
                                <span>Neto Banco:</span>
                                <span class="font-mono" id="m-pay-card-neto">Bs. 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Rendimiento por Punto de Venta -->
                <div class="rounded-xl border border-zinc-800 overflow-hidden">
                    <div class="px-4 py-2 bg-zinc-950 border-b border-zinc-800 text-xs font-bold text-zinc-300 font-mono uppercase">
                        Ventas por Punto de Venta (Barras & Tienda)
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-black/60 text-zinc-500 font-mono uppercase text-[10px] border-b border-zinc-800">
                                <tr>
                                    <th class="px-4 py-2">Barra / Tienda</th>
                                    <th class="px-4 py-2 text-right">Ventas Totales</th>
                                    <th class="px-4 py-2 text-right">Efectivo</th>
                                    <th class="px-4 py-2 text-right">QR</th>
                                    <th class="px-4 py-2 text-right">Tarjetas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-900 font-mono text-zinc-300" id="m-bars-tbody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. Comparativa Noche a Noche -->
                <div class="rounded-xl border border-zinc-800 overflow-hidden">
                    <div class="px-4 py-2 bg-zinc-950 border-b border-zinc-800 text-xs font-bold text-zinc-300 font-mono uppercase">
                        Detalle Comparativo Noche por Noche
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-black/60 text-zinc-500 font-mono uppercase text-[10px] border-b border-zinc-800">
                                <tr>
                                    <th class="px-3 py-2">Fecha</th>
                                    <th class="px-3 py-2">Día</th>
                                    <th class="px-3 py-2 text-right">Ventas</th>
                                    <th class="px-3 py-2 text-right">Efectivo</th>
                                    <th class="px-3 py-2 text-right">QR</th>
                                    <th class="px-3 py-2 text-right">POS Bruto</th>
                                    <th class="px-3 py-2 text-right">Personal</th>
                                    <th class="px-3 py-2 text-right">Gastos</th>
                                    <th class="px-3 py-2 text-right">Neto Caja</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-900 font-mono text-zinc-300" id="m-nights-tbody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Footer Acciones -->
            <div class="flex flex-col sm:flex-row justify-end gap-2 pt-3 border-t border-zinc-800">
                <button type="button" onclick="closeConsolidatedModal()" class="px-4 py-2 rounded-xl text-xs text-zinc-400 font-semibold bg-zinc-950 border border-zinc-800 hover:text-white cursor-pointer w-full sm:w-auto">
                    Cerrar
                </button>
                <button type="button" onclick="goToPrintConsolidated()" class="px-5 py-2.5 bg-[#F5B81C] text-black font-bold text-xs rounded-xl hover:bg-[#e5ac18] cursor-pointer dilemo-btn w-full sm:w-auto flex items-center justify-center gap-1.5 shadow-md shadow-[#F5B81C]/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Imprimir Acta Oficial</span>
                </button>
            </div>

        </div>
    </div>

</div>

<script>
    function getSelectedSessionIds() {
        const checked = Array.from(document.querySelectorAll('.session-chk:checked'));
        return Array.from(new Set(checked.map(chk => String(chk.value))));
    }

    function onSessionCheckChange(chk) {
        const val = String(chk.value);
        const isChecked = chk.checked;
        // Sincronizar checkbox equivalente en móvil y escritorio
        document.querySelectorAll('.session-chk[value="' + val + '"]').forEach(c => c.checked = isChecked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selected = getSelectedSessionIds();
        const count = selected.length;
        
        const countSpan = document.getElementById('header-selected-count');
        if (countSpan) countSpan.textContent = count;
        
        const floatingBar = document.getElementById('floating-consolidated-bar');
        const floatingCount = document.getElementById('floating-selected-count');
        if (floatingCount) floatingCount.textContent = count;
        
        if (floatingBar) {
            if (count > 0) {
                floatingBar.classList.remove('hidden');
            } else {
                floatingBar.classList.add('hidden');
            }
        }

        // Sincronizar master checkbox con sesiones únicas
        const totalUnique = new Set(Array.from(document.querySelectorAll('.session-chk')).map(c => String(c.value))).size;
        const master = document.getElementById('check-all-desktop');
        if (master && totalUnique > 0) {
            master.checked = count === totalUnique;
        }

        // Resaltar suavemente filas seleccionadas
        document.querySelectorAll('tr[data-session-row]').forEach(tr => {
            const rowId = tr.getAttribute('data-session-row');
            if (selected.includes(rowId)) {
                tr.classList.add('bg-[#F5B81C]/[0.05]');
            } else {
                tr.classList.remove('bg-[#F5B81C]/[0.05]');
            }
        });
    }

    function toggleSelectAll(masterChk) {
        const isChecked = masterChk.checked;
        document.querySelectorAll('.session-chk').forEach(chk => {
            chk.checked = isChecked;
        });
        updateSelectedCount();
    }

    function deselectAll() {
        document.querySelectorAll('.session-chk').forEach(chk => chk.checked = false);
        const master = document.getElementById('check-all-desktop');
        if (master) master.checked = false;
        updateSelectedCount();
    }

    function selectWeekend() {
        deselectAll();
        const weekendDays = ['viernes', 'sábado', 'sabado', 'domingo'];
        const matchedIds = new Set();
        
        document.querySelectorAll('.session-chk').forEach(chk => {
            const day = (chk.getAttribute('data-day') || '').toLowerCase();
            if (weekendDays.some(d => day.includes(d))) {
                matchedIds.add(String(chk.value));
            }
        });
        
        // Si no encontró suficientes de fin de semana, toma las 3 más recientes
        if (matchedIds.size === 0) {
            const allIds = Array.from(new Set(Array.from(document.querySelectorAll('.session-chk')).map(c => String(c.value))));
            allIds.slice(0, 3).forEach(id => matchedIds.add(id));
        }

        matchedIds.forEach(id => {
            document.querySelectorAll('.session-chk[value="' + id + '"]').forEach(c => c.checked = true);
        });
        
        updateSelectedCount();
        openConsolidatedModal();
    }

    async function openConsolidatedModal() {
        let ids = getSelectedSessionIds();
        if (ids.length === 0) {
            selectWeekend();
            ids = getSelectedSessionIds();
        }

        const modal = document.getElementById('modal-consolidated-summary');
        if (!modal) return;
        modal.classList.remove('hidden');
        
        const loading = document.getElementById('modal-consolidated-loading');
        const content = document.getElementById('modal-consolidated-data');
        if (loading) loading.classList.remove('hidden');
        if (content) content.classList.add('hidden');
        
        try {
            const response = await fetch(`{{ route('sessions.consolidatedSummary') }}?session_ids=${ids.join(',')}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            renderConsolidatedData(data);
            if (loading) loading.classList.add('hidden');
            if (content) content.classList.remove('hidden');
        } catch (err) {
            console.error(err);
            alert('Error al calcular el consolidado de noches.');
        }
    }

    function closeConsolidatedModal() {
        const modal = document.getElementById('modal-consolidated-summary');
        if (modal) modal.classList.add('hidden');
    }

    function goToPrintConsolidated() {
        let ids = getSelectedSessionIds();
        if (ids.length === 0) {
            ids = Array.from(document.querySelectorAll('.session-chk')).slice(0, 3).map(c => c.value);
        }
        if (ids.length === 0) return;
        window.open(`{{ route('sessions.consolidatedSummary') }}?session_ids=${ids.join(',')}`, '_blank');
    }

    function fmtBs(val) {
        return 'Bs. ' + (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderConsolidatedData(data) {
        document.getElementById('modal-consolidated-dates').textContent = `${data.date_range} • ${data.count} noches consolidadas`;
        
        // KPIs
        document.getElementById('m-kpi-ingresos').textContent = fmtBs(data.totals.ingresos);
        document.getElementById('m-kpi-egresos').textContent = fmtBs(data.totals.egresos);
        document.getElementById('m-kpi-neto').textContent = fmtBs(data.totals.neto);
        document.getElementById('m-kpi-ventas').textContent = fmtBs(data.totals.ventas);

        // Pagos
        document.getElementById('m-pay-cash').textContent = fmtBs(data.totals.efectivo);
        document.getElementById('m-pay-cash-principal').textContent = fmtBs(data.bars.principal.efectivo);
        document.getElementById('m-pay-cash-subte').textContent = fmtBs(data.bars.subte.efectivo);
        document.getElementById('m-pay-cash-tienda').textContent = fmtBs(data.bars.tienda.efectivo);

        document.getElementById('m-pay-qr').textContent = fmtBs(data.totals.qr_total);
        document.getElementById('m-pay-qr-yasta').textContent = fmtBs(data.totals.qr_yasta);
        document.getElementById('m-pay-qr-yape').textContent = fmtBs(data.totals.qr_yape);
        document.getElementById('m-pay-qr-total').textContent = fmtBs(data.totals.qr_total);

        document.getElementById('m-pay-card-gross').textContent = fmtBs(data.totals.card_gross);
        document.getElementById('m-pay-card-bruto').textContent = fmtBs(data.totals.card_gross);
        document.getElementById('m-pay-card-comision').textContent = '- ' + fmtBs(data.totals.card_commission);
        document.getElementById('m-pay-card-neto').textContent = fmtBs(data.totals.card_net);

        // Tabla Barras
        const barsTbody = document.getElementById('m-bars-tbody');
        barsTbody.innerHTML = `
            <tr class="hover:bg-zinc-900/50">
                <td class="px-4 py-2.5 font-bold font-sans text-white">Barra Principal <span class="text-[10px] text-zinc-500 block font-normal font-sans">Piso Principal</span></td>
                <td class="px-4 py-2.5 text-right font-bold text-[#F5B81C]">${fmtBs(data.bars.principal.ventas)}</td>
                <td class="px-4 py-2.5 text-right text-emerald-400">${fmtBs(data.bars.principal.efectivo)}</td>
                <td class="px-4 py-2.5 text-right text-blue-400">${fmtBs(data.bars.principal.qr)}</td>
                <td class="px-4 py-2.5 text-right text-purple-400">${fmtBs(data.bars.principal.card)}</td>
            </tr>
            <tr class="hover:bg-zinc-900/50">
                <td class="px-4 py-2.5 font-bold font-sans text-white">Barra Subterráneo <span class="text-[10px] text-zinc-500 block font-normal font-sans">Subterráneo</span></td>
                <td class="px-4 py-2.5 text-right font-bold text-[#F5B81C]">${fmtBs(data.bars.subte.ventas)}</td>
                <td class="px-4 py-2.5 text-right text-emerald-400">${fmtBs(data.bars.subte.efectivo)}</td>
                <td class="px-4 py-2.5 text-right text-blue-400">${fmtBs(data.bars.subte.qr)}</td>
                <td class="px-4 py-2.5 text-right text-purple-400">${fmtBs(data.bars.subte.card)}</td>
            </tr>
            <tr class="hover:bg-zinc-900/50">
                <td class="px-4 py-2.5 font-bold font-sans text-white">Tienda Oficial <span class="text-[10px] text-zinc-500 block font-normal font-sans">${data.bars.tienda.store_units || 0} unid. sueltas</span></td>
                <td class="px-4 py-2.5 text-right font-bold text-[#F5B81C]">${fmtBs(data.bars.tienda.ventas)}</td>
                <td class="px-4 py-2.5 text-right text-emerald-400">${fmtBs(data.bars.tienda.efectivo)}</td>
                <td class="px-4 py-2.5 text-right text-blue-400">${fmtBs(data.bars.tienda.qr)}</td>
                <td class="px-4 py-2.5 text-right text-purple-400">${fmtBs(data.bars.tienda.card)}</td>
            </tr>
        `;

        // Tabla Noches
        const nightsTbody = document.getElementById('m-nights-tbody');
        nightsTbody.innerHTML = data.nights.map(n => `
            <tr class="hover:bg-zinc-900/50">
                <td class="px-3 py-2 font-bold text-white">${n.date}</td>
                <td class="px-3 py-2 text-zinc-300 font-sans">${n.day_name}</td>
                <td class="px-3 py-2 text-right font-bold text-[#F5B81C]">${fmtBs(n.ventas)}</td>
                <td class="px-3 py-2 text-right text-emerald-400">${fmtBs(n.efectivo)}</td>
                <td class="px-3 py-2 text-right text-blue-400">${fmtBs(n.qr)}</td>
                <td class="px-3 py-2 text-right text-purple-400">${fmtBs(n.tarjeta_bruto)}</td>
                <td class="px-3 py-2 text-right text-rose-400/80">${fmtBs(n.personal)}</td>
                <td class="px-3 py-2 text-right text-rose-400/80">${fmtBs(n.gastos)}</td>
                <td class="px-3 py-2 text-right font-bold text-emerald-400">${fmtBs(n.neto)}</td>
            </tr>
        `).join('');
    }
</script>
@endsection
