@extends('layouts.app')

@section('title', 'Personal y Turnos')

@section('content')

<style>
@media print {
    #main-sidebar, header, nav, .drawer-panel, .drawer-overlay,
    #shift-selector-modal, #staff-toast, button, input,
    .role-pill, a, [type="button"], .md\:hidden {
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
    .hidden.md\:block {
        display: block !important;
    }
    table {
        display: table !important;
        width: 100% !important;
        border-collapse: collapse !important;
    }
    th, td {
        border: 1px solid #d1d5db !important;
        padding: 6px 10px !important;
        color: #000000 !important;
    }
    th {
        background: #f3f4f6 !important;
        font-weight: bold !important;
    }
    .category-divider-header {
        background: #e5e7eb !important;
        color: #000000 !important;
        font-weight: bold !important;
    }
    .text-white, .text-zinc-200, .text-zinc-300, .text-zinc-400 {
        color: #000000 !important;
    }
    .text-\[\#F5B81C\] {
        color: #000000 !important;
    }
}
</style>

<div class="space-y-5 max-w-7xl mx-auto pb-10">

    <!-- CABECERA EXCLUSIVA PARA IMPRESIÓN (OCULTA EN PANTALLA) -->
    <div class="hidden print-only text-center pb-4 mb-4 border-b-2 border-black font-sans">
        <h1 class="text-2xl font-black uppercase tracking-tight text-black">DISCOTECA TÍO CHU</h1>
        <h2 class="text-sm font-bold uppercase tracking-wider text-zinc-700 mt-0.5">PLANILLA OFICIAL DE PERSONAL Y ASISTENCIA DEL TURNO</h2>
        <div class="flex items-center justify-center gap-3 text-xs text-zinc-600 mt-1">
            <span>Día de Turno: <strong class="uppercase text-black">{{ $currentDay }}</strong></span>
            <span>•</span>
            <span>Integrantes: <strong class="text-black">{{ $staffMembers->count() }} trabajadores</strong></span>
            @if($activeSession)
                <span>•</span>
                <span>Noche Activa: <strong class="uppercase text-black">{{ $activeSession->day_name }} ({{ \Carbon\Carbon::parse($activeSession->session_date)->format('d/m/Y') }})</strong></span>
            @endif
        </div>
    </div>

    <!-- ==========================================
         CABECERA (iOS PURE DARK - SIN RELLENO DE IA)
         ========================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-3.5 rounded-2xl theme-card border theme-border shadow-sm font-sans">
        <!-- Título + Estado de Noche -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-[#F5B81C] shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white font-sans">
                        Personal y Turnos
                    </h1>
                    @if($activeSession)
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-zinc-900 border border-zinc-800 text-[11px] font-bold text-zinc-300">
                            <span class="w-1.5 h-1.5 rounded-full {{ $activeSession->isOpen() ? 'bg-emerald-400 animate-pulse' : 'bg-zinc-600' }}"></span>
                            <span class="uppercase text-[#F5B81C]">{{ $activeSession->day_name }}</span>
                            <span class="text-zinc-500 font-mono text-[10px]">{{ \Carbon\Carbon::parse($activeSession->session_date)->format('d/m') }}</span>
                        </span>
                    @endif
                </div>
                <p class="text-xs text-zinc-400 mt-0.5">
                    Planilla de personal, asignación de áreas y control de asistencia
                </p>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 shrink-0">
            @if($activeSession && $activeSession->isOpen())
                <button type="button" onclick="openShiftSelectorModal()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-[#F5B81C] text-zinc-200 hover:text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer active:scale-95">
                    <svg class="w-4 h-4 shrink-0 text-[#F5B81C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>Personal de Turno</span>
                    <span id="header-shift-count-badge" class="px-2 py-0.5 rounded-full bg-[#F5B81C] text-black font-mono text-xs font-black">
                        {{ count($currentSessionStaffIds) }}
                    </span>
                </button>
            @endif

            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white text-xs font-bold rounded-xl transition-all shadow-sm active:scale-95 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir</span>
            </button>

            <a href="{{ route('staffPayments.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white text-xs font-bold rounded-xl transition-all shadow-sm active:scale-95">
                <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Planilla Pagos</span>
            </a>

            <button type="button" onclick="openCreateStaffDrawer()"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#F5B81C] hover:bg-[#e5ac18] text-zinc-950 text-xs font-black rounded-xl transition-all shadow-md shadow-[#F5B81C]/10 cursor-pointer active:scale-95">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>+ Registrar Personal</span>
            </button>
        </div>
    </div>

    <!-- ========================================================
         2. BARRA DE CONTROL: TURNOS POR DÍA, BUSCADOR & ROLES
         ======================================================== -->
    <div class="p-3 rounded-2xl theme-card border theme-border shadow-sm space-y-3 font-sans">
        <!-- Fila 1: Segmented Control de Días y Buscador Directo -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- iOS Segmented Control de Días (Viernes, Sábado, Domingo, Todos, etc.) -->
            <div class="inline-flex items-center p-1 rounded-xl bg-zinc-900 border border-zinc-800 overflow-x-auto no-scrollbar gap-1 text-xs shrink-0 max-w-full">
                @if($isExtraDay && $extraDayKey)
                    <a href="{{ route('staff.index', ['day' => $extraDayKey]) }}" 
                       class="whitespace-nowrap px-3 py-1.5 rounded-lg font-bold transition-all {{ $currentDay === $extraDayKey ? 'bg-[#F5B81C] text-black shadow-sm font-black' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1 animate-pulse"></span>
                        {{ $extraDayName }} <span class="ml-1 text-[11px] {{ $currentDay === $extraDayKey ? 'text-black font-black' : 'text-zinc-500' }}">({{ $countExtra }})</span>
                    </a>
                @endif

                <a href="{{ route('staff.index', ['day' => 'viernes']) }}" 
                   class="whitespace-nowrap px-3 py-1.5 rounded-lg font-bold transition-all {{ $currentDay === 'viernes' ? 'bg-[#F5B81C] text-black shadow-sm font-black' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    Viernes <span class="ml-1 text-[11px] {{ $currentDay === 'viernes' ? 'text-black font-black' : 'text-zinc-500' }}">({{ $countViernes }})</span>
                </a>
                <a href="{{ route('staff.index', ['day' => 'sabado']) }}" 
                   class="whitespace-nowrap px-3 py-1.5 rounded-lg font-bold transition-all {{ $currentDay === 'sabado' ? 'bg-[#F5B81C] text-black shadow-sm font-black' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    Sábado <span class="ml-1 text-[11px] {{ $currentDay === 'sabado' ? 'text-black font-black' : 'text-zinc-500' }}">({{ $countSabado }})</span>
                </a>
                <a href="{{ route('staff.index', ['day' => 'domingo']) }}" 
                   class="whitespace-nowrap px-3 py-1.5 rounded-lg font-bold transition-all {{ $currentDay === 'domingo' ? 'bg-[#F5B81C] text-black shadow-sm font-black' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    Domingo <span class="ml-1 text-[11px] {{ $currentDay === 'domingo' ? 'text-black font-black' : 'text-zinc-500' }}">({{ $countDomingo }})</span>
                </a>
                <a href="{{ route('staff.index', ['day' => 'todos']) }}" 
                   class="whitespace-nowrap px-3 py-1.5 rounded-lg font-bold transition-all {{ $currentDay === 'todos' ? 'bg-[#F5B81C] text-black shadow-sm font-black' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    Todos <span class="ml-1 text-[11px] {{ $currentDay === 'todos' ? 'text-black font-black' : 'text-zinc-500' }}">({{ $countTodos }})</span>
                </a>
            </div>

            <!-- Buscador Directo y Contador -->
            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <div class="relative w-full md:w-64 shrink-0">
                    <svg class="w-3.5 h-3.5 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" 
                           id="staff-search-input" 
                           oninput="filterStaffTable(this.value)" 
                           placeholder="Buscar por nombre..." 
                           class="w-full pl-8 pr-7 py-1.5 text-xs bg-zinc-900 border border-zinc-800 rounded-xl text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C] transition-all font-sans">
                    <button type="button" 
                            id="clear-staff-search" 
                            onclick="clearStaffSearch()" 
                            class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-white text-sm leading-none cursor-pointer">
                        &times;
                    </button>
                </div>
                <div class="text-xs text-zinc-400 font-sans whitespace-nowrap hidden sm:flex items-center px-2.5 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800">
                    <span id="staff-count-display">Total: <span class="text-white font-bold">{{ $staffMembers->count() }}</span></span>
                </div>
            </div>
        </div>

        <!-- Fila 2: Filtros de Rol Oficiales de Don Ludo (Píldoras tipo Bodega Central) -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs border-t border-zinc-800/70 pt-2.5">
            <button type="button" onclick="filterByRole('all')" id="role-pill-all" class="role-pill px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap bg-[#F5B81C] text-black shadow-sm border border-[#F5B81C]">
                Todos ({{ $staffMembers->count() }})
            </button>
            <button type="button" onclick="filterByRole('seguridad')" id="role-pill-seguridad" class="role-pill px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap bg-zinc-900 border border-zinc-800 text-zinc-300 hover:border-zinc-700 hover:text-white">
                1. Seguridad ({{ $staffMembers->filter(fn($m) => $m->getRoleCategory() === 'seguridad')->count() }})
            </button>
            <button type="button" onclick="filterByRole('barra')" id="role-pill-barra" class="role-pill px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap bg-zinc-900 border border-zinc-800 text-zinc-300 hover:border-zinc-700 hover:text-white">
                2. Barra ({{ $staffMembers->filter(fn($m) => $m->getRoleCategory() === 'barra')->count() }})
            </button>
            <button type="button" onclick="filterByRole('mozos')" id="role-pill-mozos" class="role-pill px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap bg-zinc-900 border border-zinc-800 text-zinc-300 hover:border-zinc-700 hover:text-white">
                3. Mozos ({{ $staffMembers->filter(fn($m) => $m->getRoleCategory() === 'mozos')->count() }})
            </button>
            <button type="button" onclick="filterByRole('limpieza')" id="role-pill-limpieza" class="role-pill px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap bg-zinc-900 border border-zinc-800 text-zinc-300 hover:border-zinc-700 hover:text-white">
                4. Limpieza ({{ $staffMembers->filter(fn($m) => $m->getRoleCategory() === 'limpieza')->count() }})
            </button>
        </div>
    </div>

    <!-- ========================================================
         3. TABLA Y TARJETAS DE PERSONAL (FONDO PLOMO OSCURO THEME-CARD)
         ======================================================== -->
    <div class="rounded-2xl theme-card border theme-border overflow-hidden font-sans min-h-[480px] shadow-sm">
        @php
            if (!function_exists('staffNormalizeDay')) {
                function staffNormalizeDay(?string $day): string {
                    if (!$day) return '';
                    $d = mb_strtolower(trim($day));
                    return str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $d);
                }
            }
            $catLabels = [
                1 => '1. SEGURIDAD',
                2 => '2. BARRA',
                3 => '3. MOZOS / MESEROS',
                4 => '4. LIMPIEZA',
                5 => '5. OTROS',
            ];
            $lastRankMobile = null;
            $lastRankDesktop = null;
        @endphp

        <!-- VISTA DE TARJETAS PARA CELULAR (md:hidden) -->
        <div class="md:hidden divide-y divide-zinc-800/80">
            @forelse($staffMembers as $index => $member)
                @php
                    $pay = match($currentDay) {
                        'viernes' => $member->getPayForDay('Viernes'),
                        'sabado' => $member->getPayForDay('Sábado'),
                        'domingo' => $member->getPayForDay('Domingo'),
                        default => ($activeSession && staffNormalizeDay($activeSession->day_name) === $currentDay)
                            ? $member->getPayForDay($activeSession->day_name)
                            : ($member->friday_pay ?? $member->saturday_pay ?? $member->sunday_pay ?? $member->default_pay ?? 0),
                    };
                    $displayRole = str_ireplace(['Staff / ', 'Staff/', ' (S)', '(S)', 'mozo', 'mozos'], ['', '', '', '', 'MESERO', 'MESEROS'], $member->role);
                    $displayBar = str_ireplace(['Pista / Mozos', 'Pista o mozos', 'Pista', 'Seguridad / Puerta'], ['Meseros', 'Meseros', 'Meseros', 'Seguridad'], $member->assigned_bar ?? 'General');
                    $memberCat = $member->getRoleCategory();
                    $memberRank = $member->getRoleCategoryRank();
                @endphp

                @if($memberRank !== $lastRankMobile)
                    @php $lastRankMobile = $memberRank; @endphp
                    <div class="category-divider-header px-4 py-2 bg-zinc-900/90 border-y border-zinc-800 flex items-center justify-between text-zinc-300 font-sans text-xs font-bold uppercase tracking-wider" data-category="{{ $memberCat }}">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#F5B81C]"></span>
                            <span class="text-white">{{ $catLabels[$memberRank] ?? 'PERSONAL' }}</span>
                        </div>
                        <span class="text-zinc-500 font-mono text-[11px] font-bold">
                            {{ $staffMembers->filter(fn($m) => $m->getRoleCategoryRank() === $memberRank)->count() }} pers.
                        </span>
                    </div>
                @endif

                <div class="p-3.5 space-y-2.5 staff-table-row hover:bg-zinc-900/40 transition-colors" 
                     data-name="{{ strtolower($member->name) }}" 
                     data-role="{{ strtolower($displayRole) }}" 
                     data-bar="{{ strtolower($displayBar) }}"
                     data-category="{{ $memberCat }}"
                     data-rank="{{ $memberRank }}">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-xs text-zinc-500 font-mono font-bold mr-1">#{{ $index + 1 }}</span>
                            <span class="font-bold text-white text-sm tracking-tight uppercase">{{ $member->name }}</span>
                        </div>
                        <span class="font-mono font-black text-[#F5B81C] text-sm shrink-0">
                            Bs. {{ number_format($pay, 2) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-zinc-900 border border-zinc-800 text-zinc-200 uppercase">
                            {{ strtoupper($displayRole) }}
                        </span>
                        <span class="text-zinc-400 text-xs">• {{ $displayBar }}</span>
                        @if(!empty($member->phone))
                            <a href="tel:{{ $member->phone }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-emerald-400 font-mono text-[11px] ml-auto hover:border-emerald-500/50 transition-colors">
                                <svg class="w-3 h-3 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>{{ $member->phone }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-1 font-sans">
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $member->works_friday ? 'bg-zinc-800 text-zinc-200 border border-zinc-700/60' : 'text-zinc-600 bg-zinc-900/60 border border-zinc-800/40' }}">Vie</span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $member->works_saturday ? 'bg-zinc-800 text-zinc-200 border border-zinc-700/60' : 'text-zinc-600 bg-zinc-900/60 border border-zinc-800/40' }}">Sáb</span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $member->works_sunday ? 'bg-zinc-800 text-zinc-200 border border-zinc-700/60' : 'text-zinc-600 bg-zinc-900/60 border border-zinc-800/40' }}">Dom</span>
                        </div>

                        <button type="button"
                                onclick="openEditStaffDrawer(
                                    {{ $member->id }},
                                    {{ json_encode($member->name) }},
                                    {{ json_encode($displayRole) }},
                                    {{ json_encode($displayBar) }},
                                    {{ $member->works_friday ? 'true' : 'false' }},
                                    {{ $member->works_saturday ? 'true' : 'false' }},
                                    {{ $member->works_sunday ? 'true' : 'false' }},
                                    {{ $member->default_pay ?? 100 }},
                                    {{ $member->friday_pay ?? 100 }},
                                    {{ $member->saturday_pay ?? 110 }},
                                    {{ $member->sunday_pay ?? 110 }},
                                    {{ json_encode($member->phone ?? '') }},
                                    {{ $member->is_active ? 1 : 0 }}
                                )"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-[#F5B81C] text-zinc-300 hover:text-white text-xs font-bold transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Editar</span>
                        </button>
                    </div>
                </div>
            @empty
                @if($isExtraDay && $currentDay === $extraDayKey && $activeSession && $activeSession->isOpen())
                    <div class="p-8 text-center space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-[#F5B81C]/10 border border-[#F5B81C]/30 mx-auto flex items-center justify-center text-[#F5B81C]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-white uppercase">Turno de {{ $extraDayName }} sin personal asignado</h3>
                        <p class="text-xs text-zinc-400 max-w-xs mx-auto">Selecciona de la lista quiénes trabajarán en este turno extraordinario.</p>
                        <button type="button" onclick="openShiftSelectorModal()"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-[#F5B81C] text-black text-xs font-black rounded-xl cursor-pointer shadow-md">
                            <span>Seleccionar Personal de Turno</span>
                        </button>
                    </div>
                @else
                    <div class="p-8 text-center text-zinc-500 font-sans text-xs">
                        No hay personal activo registrado para este turno.
                    </div>
                @endif
            @endforelse
        </div>

        <!-- TABLA COMPLETA PARA TABLET Y ESCRITORIO (hidden md:block) -->
        <div class="hidden md:block">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-zinc-900/90 border-b border-zinc-800 text-zinc-400 uppercase text-[10px] tracking-wider font-bold">
                    <tr>
                        <th class="w-12 px-3 py-3.5 text-center">N°</th>
                        <th class="px-4 py-3.5">Trabajador</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Contacto</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Cargo / Rol</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Área</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap">Jornal</th>
                        <th class="w-24 px-4 py-3.5 text-right whitespace-nowrap">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 font-sans">
                    @forelse($staffMembers as $index => $member)
                        @php
                            $pay = match($currentDay) {
                                'viernes' => $member->getPayForDay('Viernes'),
                                'sabado' => $member->getPayForDay('Sábado'),
                                'domingo' => $member->getPayForDay('Domingo'),
                                default => ($activeSession && staffNormalizeDay($activeSession->day_name) === $currentDay)
                                    ? $member->getPayForDay($activeSession->day_name)
                                    : ($member->friday_pay ?? $member->saturday_pay ?? $member->sunday_pay ?? $member->default_pay ?? 0),
                            };
                            $displayRole = str_ireplace(['Staff / ', 'Staff/', ' (S)', '(S)', 'mozo', 'mozos'], ['', '', '', '', 'MESERO', 'MESEROS'], $member->role);
                            $displayBar = str_ireplace(['Pista / Mozos', 'Pista o mozos', 'Pista', 'Seguridad / Puerta'], ['Meseros', 'Meseros', 'Meseros', 'Seguridad'], $member->assigned_bar ?? 'General');
                            $memberCat = $member->getRoleCategory();
                            $memberRank = $member->getRoleCategoryRank();
                        @endphp

                        @if($memberRank !== $lastRankDesktop)
                            @php $lastRankDesktop = $memberRank; @endphp
                            <tr class="category-divider-header bg-zinc-900/80 border-y border-zinc-800 text-zinc-300 font-sans text-xs font-bold uppercase tracking-wider" data-category="{{ $memberCat }}">
                                <td colspan="7" class="px-4 py-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#F5B81C]"></span>
                                            <span class="text-white">{{ $catLabels[$memberRank] ?? 'PERSONAL' }}</span>
                                        </div>
                                        <span class="text-zinc-500 font-mono text-[11px] font-bold">
                                            {{ $staffMembers->filter(fn($m) => $m->getRoleCategoryRank() === $memberRank)->count() }} trabajadores
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        <tr class="hover:bg-zinc-900/40 transition-colors staff-table-row" 
                            data-name="{{ strtolower($member->name) }}" 
                            data-role="{{ strtolower($displayRole) }}" 
                            data-bar="{{ strtolower($displayBar) }}"
                            data-category="{{ $memberCat }}"
                            data-rank="{{ $memberRank }}">
                            <td class="px-3 py-3 text-center text-zinc-500 font-mono font-bold">{{ $index + 1 }}</td>
                            
                            <!-- Nombre + Turnos programados -->
                            <td class="px-4 py-3">
                                <div class="font-bold text-white text-sm sm:text-base tracking-tight uppercase">{{ $member->name }}</div>
                                <div class="flex items-center gap-1.5 mt-1 font-sans">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $member->works_friday ? 'bg-zinc-800 text-zinc-200 border border-zinc-700/60' : 'text-zinc-600 bg-zinc-900/60 border border-zinc-800/40' }}">
                                        Vie
                                    </span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $member->works_saturday ? 'bg-zinc-800 text-zinc-200 border border-zinc-700/60' : 'text-zinc-600 bg-zinc-900/60 border border-zinc-800/40' }}">
                                        Sáb
                                    </span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $member->works_sunday ? 'bg-zinc-800 text-zinc-200 border border-zinc-700/60' : 'text-zinc-600 bg-zinc-900/60 border border-zinc-800/40' }}">
                                        Dom
                                    </span>
                                </div>
                            </td>

                            <!-- Celular / Contacto -->
                            <td class="px-4 py-3">
                                @if(!empty($member->phone))
                                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-200 font-sans text-xs">
                                        <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span>{{ $member->phone }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-600 font-sans text-xs">— Sin número —</span>
                                @endif
                            </td>

                            <!-- Rol / Cargo -->
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold bg-zinc-900 border border-zinc-800 text-zinc-200 uppercase">
                                    {{ strtoupper($displayRole) }}
                                </span>
                            </td>

                            <!-- Área Asignada -->
                            <td class="px-4 py-3 text-zinc-300 font-medium">
                                {{ $displayBar }}
                            </td>

                            <!-- Jornal -->
                            <td class="px-4 py-3 text-right">
                                <span class="font-mono font-black text-[#F5B81C] text-sm sm:text-base">
                                    Bs. {{ number_format($pay, 2) }}
                                </span>
                            </td>

                            <!-- Acción Editar -->
                            <td class="px-4 py-3 text-right">
                                <button type="button"
                                        onclick="openEditStaffDrawer(
                                            {{ $member->id }},
                                            {{ json_encode($member->name) }},
                                            {{ json_encode($displayRole) }},
                                            {{ json_encode($displayBar) }},
                                            {{ $member->works_friday ? 'true' : 'false' }},
                                            {{ $member->works_saturday ? 'true' : 'false' }},
                                            {{ $member->works_sunday ? 'true' : 'false' }},
                                            {{ $member->default_pay ?? 100 }},
                                            {{ $member->friday_pay ?? 100 }},
                                            {{ $member->saturday_pay ?? 110 }},
                                            {{ $member->sunday_pay ?? 110 }},
                                            {{ json_encode($member->phone ?? '') }},
                                            {{ $member->is_active ? 1 : 0 }}
                                        )"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-[#F5B81C] text-zinc-300 hover:text-white text-xs font-bold transition-all cursor-pointer shadow-sm active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Editar</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-zinc-500 font-sans">
                                @if($isExtraDay && $currentDay === $extraDayKey && $activeSession && $activeSession->isOpen())
                                    <div class="space-y-3">
                                        <div class="w-12 h-12 rounded-2xl bg-[#F5B81C]/10 border border-[#F5B81C]/30 mx-auto flex items-center justify-center text-[#F5B81C]">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-base font-bold text-white uppercase tracking-tight">Turno de {{ $extraDayName }} sin personal asignado</h3>
                                        <p class="text-xs text-zinc-400 max-w-md mx-auto">Esta noche extraordinaria comienza sin personal predeterminado. Selecciona qué integrantes trabajarán hoy en el local.</p>
                                        <button type="button" onclick="openShiftSelectorModal()"
                                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs font-black rounded-xl transition-all shadow-md active:scale-95 cursor-pointer">
                                            <svg class="w-4 h-4 shrink-0 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                            <span>Seleccionar Personal de Turno Ahora</span>
                                        </button>
                                    </div>
                                @else
                                    No hay personal activo registrado para este turno.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    <tr id="staff-search-empty" class="hidden">
                        <td colspan="7" class="px-4 py-12 text-center text-zinc-500 font-sans">
                            No se encontró ningún integrante que coincida con la búsqueda o filtro seleccionado.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FIRMAS PARA REPORTE IMPRESO DE TURNO (OCULTO EN PANTALLA) -->
    <div class="hidden print-only pt-10 mt-8 border-t border-black text-center text-xs font-sans">
        <div class="grid grid-cols-2 gap-12">
            <div>
                <div class="border-t border-black w-48 mx-auto pt-2 font-bold uppercase text-black">Encargado de Turno / Seguridad</div>
                <span class="text-[10px] text-zinc-600 block mt-0.5">Control de Asistencia</span>
            </div>
            <div>
                <div class="border-t border-black w-48 mx-auto pt-2 font-bold uppercase text-black">Don Ludo / Gerencia</div>
                <span class="text-[10px] text-zinc-600 block mt-0.5">Visto Bueno y Aprobación</span>
            </div>
        </div>
    </div>

</div>

<!-- DRAWER OVERLAY -->
<div class="drawer-overlay" id="staff-drawer-overlay" onclick="closeStaffDrawer()"></div>

<!-- DRAWER: REGISTRAR NUEVO PERSONAL -->
<div class="drawer-panel" id="drawer-create-staff">
    <div class="drawer-header">
        <div>
            <h2 class="text-base font-black text-white tracking-tight">Registrar Personal</h2>
        </div>
        <button type="button" onclick="closeStaffDrawer()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <form method="POST" action="{{ route('staff.store') }}" class="drawer-body space-y-4 font-sans">
        @csrf

        <!-- Nombre -->
        <div>
            <label for="c_name" class="block text-xs font-bold text-zinc-300 mb-1.5">
                Nombre Completo o Apodo <span class="text-[#F5B81C]">*</span>
            </label>
            <input type="text" name="name" id="c_name" required
                   placeholder="Ej. MAURI, ALEX, KELLY, ARIEL"
                   class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white placeholder-zinc-500 uppercase focus:outline-none focus:border-[#F5B81C] transition-all">
        </div>

        <!-- Rol y Área -->
        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="c_role" class="block text-xs font-bold text-zinc-300 mb-1.5">
                    Cargo / Rol <span class="text-[#F5B81C]">*</span>
                </label>
                <select name="role" id="c_role" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                    <option value="Mesero">Mesero</option>
                    <option value="Limpieza">Limpieza</option>
                    <option value="Seguridad">Seguridad</option>
                    <option value="Bartender Principal">Bartender Principal</option>
                    <option value="Bartender Subterráneo">Bartender Subterráneo</option>
                    <option value="DJ / Sonido">DJ / Sonido</option>
                    <option value="Administrador">Administrador</option>
                </select>
            </div>
            <div>
                <label for="c_assigned_bar" class="block text-xs font-bold text-zinc-300 mb-1.5">
                    Área Asignada
                </label>
                <select name="assigned_bar" id="c_assigned_bar"
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                    <option value="Meseros">Meseros</option>
                    <option value="Barra Principal">Barra Principal</option>
                    <option value="Barra Subterráneo">Barra Subterráneo</option>
                    <option value="Seguridad">Seguridad</option>
                    <option value="Tienda">Tienda</option>
                    <option value="General">General</option>
                </select>
            </div>
        </div>

        <!-- Teléfono / Celular (Opcional) -->
        <div>
            <label for="c_phone" class="block text-xs font-bold text-zinc-300 mb-1.5">
                Teléfono / Celular (Opcional)
            </label>
            <div class="flex items-center rounded-xl bg-zinc-900 border border-zinc-800 px-3.5 py-2 focus-within:border-[#F5B81C] transition-all">
                <svg class="w-4 h-4 text-zinc-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <input type="text" name="phone" id="c_phone" placeholder="Ej. 70012345"
                       class="w-full text-sm font-sans bg-transparent border-0 text-white placeholder-zinc-500 focus:outline-none p-0">
            </div>
        </div>

        <!-- Días de Turno Programados -->
        <div class="p-3.5 rounded-xl bg-zinc-900/80 border border-zinc-800 space-y-2">
            <span class="block text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Días de Turno Habitual</span>
            <div class="grid grid-cols-3 gap-2 pt-1">
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_friday" value="1" checked
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Viernes</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_saturday" value="1" checked
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Sábado</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_sunday" value="1" checked
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Domingo</span>
                </label>
            </div>
        </div>

        <!-- Tarifas de Pago por Noche -->
        <div>
            <label class="block text-xs font-bold text-zinc-300 mb-1.5">Tarifas por Noche (Bs.)</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <div>
                    <label for="c_default_pay" class="block text-[10px] font-sans font-bold text-zinc-400 mb-1">Base</label>
                    <input type="number" step="5" name="default_pay" id="c_default_pay" required value="100"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="c_friday_pay" class="block text-[10px] font-sans font-bold text-zinc-400 mb-1">Vie</label>
                    <input type="number" step="5" name="friday_pay" id="c_friday_pay" value="100"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="c_saturday_pay" class="block text-[10px] font-sans font-bold text-zinc-400 mb-1">Sáb</label>
                    <input type="number" step="5" name="saturday_pay" id="c_saturday_pay" value="110"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="c_sunday_pay" class="block text-[10px] font-sans font-bold text-zinc-400 mb-1">Dom</label>
                    <input type="number" step="5" name="sunday_pay" id="c_sunday_pay" value="110"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="pt-5 border-t border-zinc-800 flex items-center justify-end gap-2.5 mt-6 w-full">
            <button type="button" onclick="closeStaffDrawer()" 
                    class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl border border-zinc-800 text-zinc-400 hover:text-white text-xs sm:text-sm font-bold text-center transition-all cursor-pointer">
                Cancelar
            </button>
            <button type="submit" 
                    class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs sm:text-sm font-black uppercase tracking-wider text-center transition-all cursor-pointer shadow-md">
                Guardar Personal
            </button>
        </div>
    </form>
</div>

<!-- DRAWER: EDITAR PERSONAL -->
<div class="drawer-panel" id="drawer-edit-staff">
    <div class="drawer-header">
        <div>
            <h2 class="text-base font-black text-white tracking-tight" id="edit-staff-title">Editar Personal</h2>
        </div>
        <button type="button" onclick="closeStaffDrawer()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <form method="POST" id="edit-staff-form" action="" class="drawer-body space-y-4 font-sans">
        @csrf
        @method('PUT')

        <!-- Nombre -->
        <div>
            <label for="e_name" class="block text-xs font-bold text-zinc-300 mb-1.5">
                Nombre Completo o Apodo <span class="text-[#F5B81C]">*</span>
            </label>
            <input type="text" name="name" id="e_name" required
                   class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white placeholder-zinc-500 uppercase focus:outline-none focus:border-[#F5B81C] transition-all">
        </div>

        <!-- Rol y Área -->
        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="e_role" class="block text-xs font-bold text-zinc-300 mb-1.5">
                    Cargo / Rol <span class="text-[#F5B81C]">*</span>
                </label>
                <select name="role" id="e_role" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                    <option value="Mesero">Mesero</option>
                    <option value="Limpieza">Limpieza</option>
                    <option value="Seguridad">Seguridad</option>
                    <option value="Bartender Principal">Bartender Principal</option>
                    <option value="Bartender Subterráneo">Bartender Subterráneo</option>
                    <option value="DJ / Sonido">DJ / Sonido</option>
                    <option value="Administrador">Administrador</option>
                </select>
            </div>
            <div>
                <label for="e_assigned_bar" class="block text-xs font-bold text-zinc-300 mb-1.5">
                    Área Asignada
                </label>
                <select name="assigned_bar" id="e_assigned_bar"
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                    <option value="Meseros">Meseros</option>
                    <option value="Barra Principal">Barra Principal</option>
                    <option value="Barra Subterráneo">Barra Subterráneo</option>
                    <option value="Seguridad">Seguridad</option>
                    <option value="Tienda">Tienda</option>
                    <option value="General">General</option>
                </select>
            </div>
        </div>

        <!-- Teléfono / Celular (De la BD) -->
        <div>
            <label for="e_phone" class="block text-xs font-bold text-zinc-300 mb-1.5">
                Teléfono / Celular (Opcional)
            </label>
            <div class="flex items-center rounded-xl bg-zinc-900 border border-zinc-800 px-3.5 py-2 focus-within:border-[#F5B81C] transition-all">
                <svg class="w-4 h-4 text-zinc-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <input type="text" name="phone" id="e_phone" placeholder="Ej. 70012345"
                       class="w-full text-sm font-sans bg-transparent border-0 text-white placeholder-zinc-500 focus:outline-none p-0">
            </div>
        </div>

        <!-- Días de Turno Programados -->
        <div class="p-3.5 rounded-xl bg-zinc-900/80 border border-zinc-800 space-y-2">
            <span class="block text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Días de Turno Habitual</span>
            <div class="grid grid-cols-3 gap-2 pt-1">
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_friday" id="e_works_friday" value="1"
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Viernes</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_saturday" id="e_works_saturday" value="1"
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Sábado</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_sunday" id="e_works_sunday" value="1"
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Domingo</span>
                </label>
            </div>
        </div>

        <!-- Tarifas de Pago por Noche -->
        <div>
            <label class="block text-xs font-bold text-zinc-300 mb-1.5">Tarifas por Noche (Bs.)</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <div>
                    <label for="e_default_pay" class="block text-[10px] font-sans font-bold text-zinc-400 mb-1">Base</label>
                    <input type="number" step="5" name="default_pay" id="e_default_pay" required
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="e_friday_pay" class="block text-[10px] font-sans font-bold text-zinc-400 mb-1">Vie</label>
                    <input type="number" step="5" name="friday_pay" id="e_friday_pay"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="e_saturday_pay" class="block text-[10px] font-sans font-bold text-zinc-400 mb-1">Sáb</label>
                    <input type="number" step="5" name="saturday_pay" id="e_saturday_pay"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="e_sunday_pay" class="block text-[10px] font-sans font-bold text-zinc-400 mb-1">Dom</label>
                    <input type="number" step="5" name="sunday_pay" id="e_sunday_pay"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
            </div>
        </div>

        <!-- Estado del Personal -->
        <div>
            <label for="e_is_active" class="block text-xs font-bold text-zinc-300 mb-1.5">
                Estado de Disponibilidad
            </label>
            <select name="is_active" id="e_is_active"
                    class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                <option value="1">Activo (Habilitado para turnos)</option>
                <option value="0">Inactivo / De Baja</option>
            </select>
        </div>

        <!-- Botones de Acción -->
        <div class="pt-5 border-t border-zinc-800 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mt-6 w-full">
            <button type="button" onclick="confirmDeleteStaff()"
                    class="w-full sm:w-auto text-center sm:text-left py-2 text-xs text-rose-400 hover:text-rose-300 font-bold transition-all cursor-pointer">
                Eliminar Personal
            </button>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <button type="button" onclick="closeStaffDrawer()" 
                        class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl border border-zinc-800 text-zinc-400 hover:text-white text-xs sm:text-sm font-bold text-center transition-all cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" 
                        class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs sm:text-sm font-black uppercase tracking-wider text-center transition-all cursor-pointer shadow-md">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>

    <form id="delete-staff-inline-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<!-- ========================================================
     MODAL DE SELECCIÓN DE PERSONAL DE TURNO (TAREA 2 & 3)
     ======================================================== -->
<div id="shift-selector-modal" class="fixed inset-0 z-[120] hidden flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-sm transition-opacity duration-200">
    <div class="theme-card border theme-border rounded-2xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden font-sans">
        
        <!-- Modal Header -->
        <div class="p-4 sm:p-5 border-b border-zinc-800 flex items-center justify-between bg-zinc-900/90 shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#F5B81C]"></span>
                    <h2 class="text-base sm:text-lg font-black text-white uppercase tracking-tight">
                        Seleccionar Personal de Turno
                    </h2>
                </div>
                <p class="text-xs text-zinc-400 mt-0.5">
                    @if($activeSession)
                        Noche: <span class="text-white font-bold uppercase">{{ $activeSession->day_name }}</span>
                        ({{ \Carbon\Carbon::parse($activeSession->session_date)->format('d/m/Y') }})
                    @else
                        Turno Extraordinario
                    @endif
                    — Marca o desmarca quiénes trabajarán hoy.
                </p>
            </div>
            <button type="button" onclick="closeShiftSelectorModal()" class="w-8 h-8 rounded-full bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center text-lg leading-none cursor-pointer transition-colors">
                &times;
            </button>
        </div>

        <!-- Toolbar: Buscador, Filtros por Rol y Acciones de Selección -->
        <div class="p-3 sm:p-4 border-b border-zinc-800 bg-zinc-900/60 space-y-3 shrink-0">
            <div class="flex flex-col sm:flex-row items-center gap-2.5">
                <!-- Buscador instantáneo -->
                <div class="relative w-full sm:flex-1">
                    <svg class="w-4 h-4 text-zinc-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="shift-modal-search" oninput="filterModalCards(this.value)" placeholder="Buscar trabajador por nombre o cargo..." class="w-full pl-9 pr-4 py-2 rounded-xl text-xs sm:text-sm bg-zinc-900 border border-zinc-800 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>

                <!-- Botones Selección Rápida -->
                <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                    <button type="button" onclick="toggleAllModalCheckboxes(true)" class="flex-1 sm:flex-initial px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white text-xs font-bold transition-all cursor-pointer">
                        Marcar Visibles
                    </button>
                    <button type="button" onclick="toggleAllModalCheckboxes(false)" class="flex-1 sm:flex-initial px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white text-xs font-bold transition-all cursor-pointer">
                        Desmarcar Todos
                    </button>
                </div>
            </div>

            <!-- Filtros de Rol Oficiales (Tarea 3) -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-sans font-semibold">
                <button type="button" onclick="filterModalByRole('all')" id="modal-role-pill-all" class="modal-role-pill px-3 py-1 rounded-lg border border-[#F5B81C] bg-[#F5B81C] text-black font-black transition-all cursor-pointer">
                    Todos ({{ $allActiveStaff->count() }})
                </button>
                <button type="button" onclick="filterModalByRole('seguridad')" id="modal-role-pill-seguridad" class="modal-role-pill px-3 py-1 rounded-lg border border-zinc-800 bg-zinc-900 text-zinc-300 hover:text-white transition-all cursor-pointer">
                    1. Seguridad ({{ $allActiveStaff->filter(fn($m) => $m->getRoleCategory() === 'seguridad')->count() }})
                </button>
                <button type="button" onclick="filterModalByRole('barra')" id="modal-role-pill-barra" class="modal-role-pill px-3 py-1 rounded-lg border border-zinc-800 bg-zinc-900 text-zinc-300 hover:text-white transition-all cursor-pointer">
                    2. Barra ({{ $allActiveStaff->filter(fn($m) => $m->getRoleCategory() === 'barra')->count() }})
                </button>
                <button type="button" onclick="filterModalByRole('mozos')" id="modal-role-pill-mozos" class="modal-role-pill px-3 py-1 rounded-lg border border-zinc-800 bg-zinc-900 text-zinc-300 hover:text-white transition-all cursor-pointer">
                    3. Mozos ({{ $allActiveStaff->filter(fn($m) => $m->getRoleCategory() === 'mozos')->count() }})
                </button>
                <button type="button" onclick="filterModalByRole('limpieza')" id="modal-role-pill-limpieza" class="modal-role-pill px-3 py-1 rounded-lg border border-zinc-800 bg-zinc-900 text-zinc-300 hover:text-white transition-all cursor-pointer">
                    4. Limpieza ({{ $allActiveStaff->filter(fn($m) => $m->getRoleCategory() === 'limpieza')->count() }})
                </button>
            </div>
        </div>

        <!-- Tarjetas Compactas del Personal (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-3.5 sm:p-5 space-y-4 max-h-[58vh]" id="shift-modal-cards-container">
            @php
                $modalLastRank = null;
            @endphp
            @foreach($allActiveStaff as $member)
                @php
                    $isChecked = in_array($member->id, $currentSessionStaffIds);
                    $pay = $activeSession ? $member->getPayForDay($activeSession->day_name) : $member->default_pay;
                    $cat = $member->getRoleCategory();
                    $rank = $member->getRoleCategoryRank();
                @endphp

                @if($rank !== $modalLastRank)
                    @php $modalLastRank = $rank; @endphp
                    @if(!$loop->first)
                        </div>
                    </div>
                    @endif
                    <div class="modal-cat-section pt-1" data-category="{{ $cat }}">
                        <div class="flex items-center gap-2 mb-2 px-1 text-zinc-400 font-mono text-[11px] font-black uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#F5B81C]"></span>
                            <span>{{ $catLabels[$rank] ?? 'PERSONAL' }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @endif

                <label class="modal-staff-card group flex items-center justify-between p-3 rounded-xl border transition-all cursor-pointer select-none {{ $isChecked ? 'bg-[#F5B81C]/15 border-[#F5B81C]/70 text-white shadow-sm' : 'bg-zinc-900/80 border-zinc-800 hover:border-zinc-700 text-zinc-300' }}"
                       data-id="{{ $member->id }}"
                       data-name="{{ strtolower($member->name) }}"
                       data-role="{{ strtolower($member->role) }}"
                       data-category="{{ $cat }}"
                       data-pay="{{ (float)$pay }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <input type="checkbox" 
                               name="shift_staff_ids[]" 
                               value="{{ $member->id }}" 
                               {{ $isChecked ? 'checked' : '' }} 
                               onchange="onModalCheckboxChange(this)"
                               class="shift-checkbox w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] accent-[#F5B81C] cursor-pointer shrink-0">
                        <div class="min-w-0">
                            <div class="font-bold text-xs sm:text-sm uppercase tracking-tight truncate group-hover:text-white">{{ $member->name }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5 text-[10px]">
                                <span class="px-1.5 py-0.5 rounded font-sans font-bold uppercase bg-zinc-900 border border-zinc-800 text-zinc-400">
                                    {{ strtoupper($cat) }}
                                </span>
                                <span class="text-zinc-500 truncate">• {{ $member->assigned_bar ?? 'General' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0 pl-2">
                        <span class="font-mono font-black text-xs sm:text-sm text-[#F5B81C]">
                            Bs. {{ number_format($pay, 0) }}
                        </span>
                    </div>
                </label>

                @if($loop->last)
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Sticky Footer con Contadores y Guardado -->
        <div class="p-3.5 sm:p-4 border-t border-zinc-800 bg-zinc-900/90 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3 text-xs sm:text-sm font-sans w-full sm:w-auto">
                <span class="text-zinc-400">
                    Seleccionados: <span id="shift-modal-selected-count" class="text-white font-mono font-bold text-base">0</span> de {{ $allActiveStaff->count() }}
                </span>
                <span class="text-zinc-600">•</span>
                <span class="text-zinc-400">
                    Planilla: <span class="text-[#F5B81C] font-mono font-black text-base">Bs. <span id="shift-modal-total-pay">0.00</span></span>
                </span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="button" onclick="closeShiftSelectorModal()" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white text-xs sm:text-sm font-bold transition-all cursor-pointer">
                    Cancelar
                </button>
                <button type="button" id="btn-save-shift" onclick="saveShiftAttendance()" class="flex-1 sm:flex-initial px-6 py-2.5 rounded-xl bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs sm:text-sm font-black transition-all shadow-sm active:scale-95 cursor-pointer flex items-center justify-center gap-2">
                    <span id="btn-save-shift-text">Guardar Personal de Turno</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- TOAST FLOTANTE MINIMALISTA iOS -->
<div id="staff-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[130] flex items-center gap-2.5 px-4 py-2.5 rounded-full bg-zinc-900/95 border border-zinc-700/80 shadow-2xl backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300 font-sans text-xs">
    <span id="staff-toast-dot" class="w-2 h-2 rounded-full bg-[#F5B81C]"></span>
    <span id="staff-toast-text" class="text-white font-medium"></span>
</div>

<script>
    let currentRoleFilter = 'all';
    let modalRoleFilter = 'all';

    function openCreateStaffDrawer() {
        document.getElementById('staff-drawer-overlay').classList.add('is-open');
        document.getElementById('drawer-create-staff').classList.add('is-open');
        document.body.classList.add('drawer-open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const input = document.getElementById('c_name');
            if (input) input.focus();
        }, 350);
    }

    function openEditStaffDrawer(id, name, role, assignedBar, worksFri, worksSat, worksSun, defPay, friPay, satPay, sunPay, phone, isActive) {
        const baseUrl = '{{ url("/staff") }}';
        document.getElementById('edit-staff-form').action = baseUrl + '/' + id;
        document.getElementById('delete-staff-inline-form').action = baseUrl + '/' + id;

        document.getElementById('edit-staff-title').textContent = name;
        document.getElementById('e_name').value = name;
        document.getElementById('e_phone').value = phone || '';
        document.getElementById('e_default_pay').value = defPay;
        document.getElementById('e_friday_pay').value = friPay;
        document.getElementById('e_saturday_pay').value = satPay;
        document.getElementById('e_sunday_pay').value = sunPay;
        document.getElementById('e_is_active').value = isActive;
        document.getElementById('e_works_friday').checked = !!worksFri;
        document.getElementById('e_works_saturday').checked = !!worksSat;
        document.getElementById('e_works_sunday').checked = !!worksSun;

        // Set role select
        const cleanRole = String(role || '').replace(/^Staff\s*\/\s*/i, '').replace(/\s*\(S\)/i, '').replace(/mozos?/i, 'Mesero').trim();
        const roleSelect = document.getElementById('e_role');
        if (roleSelect) {
            for (let opt of roleSelect.options) { 
                opt.selected = opt.value.toLowerCase() === cleanRole.toLowerCase() || opt.value.toLowerCase() === String(role).toLowerCase(); 
            }
        }

        // Set area select
        const cleanArea = String(assignedBar || '').replace(/Pista\s*\/\s*Mozos/i, 'Meseros').replace(/Seguridad\s*\/\s*Puerta/i, 'Seguridad').trim();
        const areaSelect = document.getElementById('e_assigned_bar');
        if (areaSelect) {
            for (let opt of areaSelect.options) { 
                opt.selected = opt.value.toLowerCase() === cleanArea.toLowerCase() || opt.value.toLowerCase() === String(assignedBar).toLowerCase(); 
            }
        }

        document.getElementById('staff-drawer-overlay').classList.add('is-open');
        document.getElementById('drawer-edit-staff').classList.add('is-open');
        document.body.classList.add('drawer-open');
        document.body.style.overflow = 'hidden';
    }

    function closeStaffDrawer() {
        document.getElementById('staff-drawer-overlay').classList.remove('is-open');
        document.getElementById('drawer-create-staff').classList.remove('is-open');
        document.getElementById('drawer-edit-staff').classList.remove('is-open');
        document.body.classList.remove('drawer-open');
        document.body.style.overflow = '';
    }

    function confirmDeleteStaff() {
        if (confirm('¿Eliminar este trabajador del registro permanentemente? Esta acción no se puede deshacer.')) {
            document.getElementById('delete-staff-inline-form').submit();
        }
    }

    /* --- FILTROS DE ROL (1. SEGURIDAD, 2. BARRA, 3. MOZOS, 4. LIMPIEZA) --- */
    function filterByRole(role) {
        currentRoleFilter = role;
        
        document.querySelectorAll('.role-pill').forEach(btn => {
            btn.className = 'role-pill px-3 py-1.5 rounded-xl border border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700 hover:text-white transition-all cursor-pointer whitespace-nowrap';
        });
        const activeBtn = document.getElementById('role-pill-' + role);
        if (activeBtn) {
            activeBtn.className = 'role-pill px-3 py-1.5 rounded-xl border border-[#F5B81C] bg-[#F5B81C] text-black font-black transition-all cursor-pointer whitespace-nowrap shadow-sm';
        }

        applyStaffFilters();
    }

    function filterStaffTable(query) {
        applyStaffFilters();
    }

    function applyStaffFilters() {
        const q = (document.getElementById('staff-search-input')?.value || '').toLowerCase().trim();
        const clearBtn = document.getElementById('clear-staff-search');
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', q.length === 0);
        }

        const rows = document.querySelectorAll('.staff-table-row');
        let visibleCount = 0;
        const visibleCategories = new Set();

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const role = row.getAttribute('data-role') || '';
            const bar = row.getAttribute('data-bar') || '';
            const cat = row.getAttribute('data-category') || '';

            const matchesQuery = !q || name.includes(q) || role.includes(q) || bar.includes(q);
            const matchesRole = (currentRoleFilter === 'all') || (cat === currentRoleFilter);

            const isVisible = matchesQuery && matchesRole;
            row.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleCount++;
                visibleCategories.add(cat);
            }
        });

        // Mostrar u ocultar cabeceras de categorías según coincidencia
        document.querySelectorAll('.category-divider-header').forEach(header => {
            const cat = header.getAttribute('data-category');
            if (currentRoleFilter === 'all') {
                header.style.display = visibleCategories.has(cat) ? '' : 'none';
            } else {
                header.style.display = (cat === currentRoleFilter && visibleCategories.has(cat)) ? '' : 'none';
            }
        });

        const emptyRow = document.getElementById('staff-search-empty');
        if (emptyRow) {
            emptyRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }

        const countDisplay = document.getElementById('staff-count-display');
        if (countDisplay) {
            countDisplay.innerHTML = `Total: <span class="text-white font-bold">${visibleCount}</span>`;
        }
    }

    function clearStaffSearch() {
        const input = document.getElementById('staff-search-input');
        if (input) {
            input.value = '';
            applyStaffFilters();
            input.focus();
        }
    }

    /* --- MODAL SELECCIONAR PERSONAL DE TURNO (TAREA 2) --- */
    function openShiftSelectorModal() {
        const modal = document.getElementById('shift-selector-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        recalculateModalStats();
        setTimeout(() => {
            document.getElementById('shift-modal-search')?.focus();
        }, 120);
    }

    function closeShiftSelectorModal() {
        const modal = document.getElementById('shift-selector-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function onModalCheckboxChange(cb) {
        const card = cb.closest('.modal-staff-card');
        if (card) {
            if (cb.checked) {
                card.classList.remove('bg-zinc-900/80', 'bg-zinc-900', 'border-zinc-800', 'text-zinc-300');
                card.classList.add('bg-[#F5B81C]/15', 'border-[#F5B81C]/70', 'text-white', 'shadow-sm');
            } else {
                card.classList.remove('bg-[#F5B81C]/15', 'border-[#F5B81C]/70', 'text-white', 'shadow-sm');
                card.classList.add('bg-zinc-900/80', 'border-zinc-800', 'text-zinc-300');
            }
        }
        recalculateModalStats();
    }

    function recalculateModalStats() {
        let count = 0;
        let totalPay = 0;
        document.querySelectorAll('.modal-staff-card').forEach(card => {
            const cb = card.querySelector('.shift-checkbox');
            if (cb && cb.checked) {
                count++;
                totalPay += parseFloat(card.getAttribute('data-pay') || 0);
            }
        });
        const cntEl = document.getElementById('shift-modal-selected-count');
        const payEl = document.getElementById('shift-modal-total-pay');
        if (cntEl) cntEl.textContent = count;
        if (payEl) payEl.textContent = totalPay.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function filterModalByRole(role) {
        modalRoleFilter = role;
        document.querySelectorAll('.modal-role-pill').forEach(btn => {
            btn.className = 'modal-role-pill px-3 py-1 rounded-lg border border-zinc-800 bg-zinc-900 text-zinc-300 hover:text-white transition-all cursor-pointer';
        });
        const activeBtn = document.getElementById('modal-role-pill-' + role);
        if (activeBtn) {
            activeBtn.className = 'modal-role-pill px-3 py-1 rounded-lg border border-[#F5B81C] bg-[#F5B81C] text-black font-black transition-all cursor-pointer';
        }
        applyModalFilters();
    }

    function filterModalCards(query) {
        applyModalFilters();
    }

    function applyModalFilters() {
        const q = (document.getElementById('shift-modal-search')?.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.modal-staff-card');
        const visibleCats = new Set();

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const role = card.getAttribute('data-role') || '';
            const cat = card.getAttribute('data-category') || '';

            const matchesQuery = !q || name.includes(q) || role.includes(q);
            const matchesRole = (modalRoleFilter === 'all') || (cat === modalRoleFilter);
            const isVisible = matchesQuery && matchesRole;

            card.style.display = isVisible ? '' : 'none';
            if (isVisible) {
                visibleCats.add(cat);
            }
        });

        document.querySelectorAll('.modal-cat-section').forEach(section => {
            const cat = section.getAttribute('data-category');
            if (modalRoleFilter === 'all') {
                section.style.display = visibleCats.has(cat) ? '' : 'none';
            } else {
                section.style.display = (cat === modalRoleFilter && visibleCats.has(cat)) ? '' : 'none';
            }
        });
    }

    function toggleAllModalCheckboxes(state) {
        document.querySelectorAll('.modal-staff-card').forEach(card => {
            if (card.style.display !== 'none') {
                const cb = card.querySelector('.shift-checkbox');
                if (cb) {
                    cb.checked = state;
                    onModalCheckboxChange(cb);
                }
            }
        });
    }

    async function saveShiftAttendance() {
        const btn = document.getElementById('btn-save-shift');
        const btnText = document.getElementById('btn-save-shift-text');
        const sessionId = {{ $activeSession ? $activeSession->id : 'null' }};

        if (!sessionId) {
            showToast('No hay una noche activa para sincronizar turnos', 'error');
            return;
        }

        const checkedBoxes = document.querySelectorAll('#shift-selector-modal .shift-checkbox:checked');
        const selectedIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));

        btn.disabled = true;
        btnText.textContent = 'Guardando...';

        try {
            const res = await fetch('{{ route("staff.syncAttendance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    session_id: sessionId,
                    staff_ids: selectedIds
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showToast(data.message || 'Turno guardado con éxito');
                closeShiftSelectorModal();
                const badge = document.getElementById('header-shift-count-badge');
                if (badge) badge.textContent = data.count;
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                showToast(data.message || 'Error al guardar el turno', 'error');
            }
        } catch(e) {
            showToast('Error de red al sincronizar personal', 'error');
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Guardar Personal de Turno';
        }
    }

    function showToast(text, type = 'success') {
        const toast = document.getElementById('staff-toast');
        const toastText = document.getElementById('staff-toast-text');
        const toastDot = document.getElementById('staff-toast-dot');
        if (!toast || !toastText) return;

        toastText.innerHTML = text;
        if (toastDot) {
            toastDot.className = type === 'error' ? 'w-2 h-2 rounded-full bg-rose-500' : 'w-2 h-2 rounded-full bg-[#F5B81C]';
        }
        toast.classList.remove('opacity-0', 'pointer-events-none');
        toast.classList.add('opacity-100');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0', 'pointer-events-none');
        }, 2600);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStaffDrawer();
            closeShiftSelectorModal();
            clearStaffSearch();
        }
    });

    // Inicializar contadores del modal
    document.addEventListener('DOMContentLoaded', function() {
        recalculateModalStats();
    });
</script>
@endsection
