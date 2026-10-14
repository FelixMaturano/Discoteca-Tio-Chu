@extends('layouts.app')

@section('title', 'Cobros por QR')

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
</style>

<div class="space-y-4 max-w-7xl mx-auto w-full pb-8">

    <!-- ========================================================
         1. CABECERA PRINCIPAL: ESTADO DE NOCHE & SELECTOR
         ======================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-3.5 rounded-2xl bg-[#09090b] border border-zinc-800/80 shadow-sm">
        
        <!-- Izquierda: Título y Selector de Noche -->
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white font-sans">
                    Cobros por QR
                </h1>
                <p class="text-[11px] text-zinc-400 mt-0.5">
                    Transferencias registradas por Yasta (Banco Unión) y Yape (Banco BCP)
                </p>
            </div>

            @if($session)
                <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800">
                    <span class="w-2 h-2 rounded-full {{ $session->isOpen() ? 'bg-emerald-500' : 'bg-zinc-600' }}"></span>
                    <span class="text-xs font-black uppercase tracking-wider {{ $session->isOpen() ? 'text-zinc-200' : 'text-zinc-500' }}">
                        {{ $session->isOpen() ? 'Noche Abierta' : 'Noche Cerrada' }}
                    </span>
                </div>
            @endif

            <!-- Selector de Noche en Píldora -->
            @if($allSessions->isNotEmpty())
                <form method="GET" action="{{ route('qrs.index') }}" class="flex items-center">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs hover:border-[#F5B81C] transition-colors">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <select name="session_id" id="session_id" onchange="this.form.submit()" 
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

        <!-- Derecha: Estado o Acceso a Cierre -->
        <div class="flex items-center gap-2 shrink-0">
            @if($session && !$session->isOpen())
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-zinc-400">
                    <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Modo lectura</span>
                    <a href="{{ route('closing.index', ['session_id' => $session->id]) }}" class="text-[#F5B81C] hover:underline ml-1 font-bold">Cierre &rarr;</a>
                </div>
            @endif
        </div>

    </div>

    @if(!$session)
        <!-- Estado Vacío -->
        <div class="p-12 text-center rounded-2xl bg-[#09090b] border border-zinc-800/80 flex flex-col items-center justify-center">
            <div class="w-12 h-12 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-600 mb-3">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect width="6" height="6" x="3" y="3" rx="1"/>
                    <rect width="6" height="6" x="15" y="3" rx="1"/>
                    <rect width="6" height="6" x="3" y="15" rx="1"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 15h3m3 0h-3v3m0 3v-3m3 3v.01M10 7v3a2 2 0 01-2 2H5"/>
                </svg>
            </div>
            <h3 class="text-sm font-black uppercase tracking-wider text-white">No hay ninguna noche abierta o seleccionada</h3>
            <p class="text-xs text-zinc-400 mt-1 mb-5">Apertura una noche para registrar cobros por QR.</p>
            <a href="{{ route('sessions.create') }}" class="px-5 py-2.5 rounded-xl bg-[#F5B81C] text-black font-black text-xs hover:bg-[#e5ac18] transition-all active:scale-95 shadow-sm">
                + Aperturar Nueva Noche
            </a>
        </div>
    @else

        <!-- ========================================================
             2. 4 TARJETAS KPI RESUMEN: SOBRIAS Y MINIMALISTAS
             ======================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3.5">
            
            <!-- TARJETA 1: Total General QR -->
            <div class="p-3 sm:p-4 rounded-2xl theme-card border theme-border flex items-center justify-between shadow-sm">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block truncate">
                        Total QR
                    </span>
                    <div class="text-lg sm:text-2xl font-black text-white font-mono tracking-tight mt-0.5 sm:mt-1 truncate">
                        <span class="text-xs font-sans font-bold text-[#F5B81C] mr-0.5">Bs.</span>{{ number_format($totalGeneral, 2) }}
                    </div>
                    <span class="text-[9px] sm:text-[10px] text-zinc-500 font-mono block mt-0.5 truncate">
                        Yasta: {{ number_format($totalYasta, 0) }} • Yape: {{ number_format($totalYape, 0) }}
                    </span>
                </div>
                <div class="hidden sm:flex w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 items-center justify-center text-zinc-300 shrink-0 ml-3">
                    <svg class="w-5 h-5 text-[#F5B81C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="6" height="6" x="3" y="3" rx="1"/>
                        <rect width="6" height="6" x="15" y="3" rx="1"/>
                        <rect width="6" height="6" x="3" y="15" rx="1"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 15h3m3 0h-3v3m0 3v-3m3 3v.01M10 7v3a2 2 0 01-2 2H5"/>
                    </svg>
                </div>
            </div>

            <!-- TARJETA 2: Barra Principal -->
            <div class="p-3 sm:p-4 rounded-2xl theme-card border theme-border flex items-center justify-between shadow-sm">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block truncate">
                        Barra Principal
                    </span>
                    <div class="text-lg sm:text-2xl font-black text-white font-mono tracking-tight mt-0.5 sm:mt-1 truncate">
                        <span class="text-xs font-sans font-bold text-[#F5B81C] mr-0.5">Bs.</span>{{ number_format($totalsByPos['Barra Principal']['total'], 2) }}
                    </div>
                    <span class="text-[10px] text-zinc-500 font-mono block mt-0.5 truncate">
                        Yasta: Bs. {{ number_format($totalsByPos['Barra Principal']['yasta'], 0) }} • Yape: Bs. {{ number_format($totalsByPos['Barra Principal']['yape'], 0) }}
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-300 shrink-0 ml-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 22h8M12 15v7M19 3l-7 8-7-8h14z"/>
                    </svg>
                </div>
            </div>

            <!-- TARJETA 3: Tienda (Entrada) -->
            <div class="p-3 sm:p-4 rounded-2xl theme-card border theme-border flex items-center justify-between shadow-sm">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block truncate">
                        Tienda
                    </span>
                    <div class="text-lg sm:text-2xl font-black text-white font-mono tracking-tight mt-0.5 sm:mt-1 truncate">
                        <span class="text-xs font-sans font-bold text-[#F5B81C] mr-0.5">Bs.</span>{{ number_format($totalsByPos['Tienda']['total'], 2) }}
                    </div>
                    <span class="text-[9px] sm:text-[10px] text-zinc-500 font-mono block mt-0.5 truncate">
                        Yasta: {{ number_format($totalsByPos['Tienda']['yasta'], 0) }} • Yape: {{ number_format($totalsByPos['Tienda']['yape'], 0) }}
                    </span>
                </div>
                <div class="hidden sm:flex w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 items-center justify-center text-zinc-300 shrink-0 ml-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>

            <!-- TARJETA 4: Barra Subterráneo -->
            <div class="p-3 sm:p-4 rounded-2xl theme-card border theme-border flex items-center justify-between shadow-sm">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block truncate">
                        Barra Subterráneo
                    </span>
                    <div class="text-lg sm:text-2xl font-black text-white font-mono tracking-tight mt-0.5 sm:mt-1 truncate">
                        <span class="text-xs font-sans font-bold text-[#F5B81C] mr-0.5">Bs.</span>{{ number_format($totalsByPos['Subte']['total'], 2) }}
                    </div>
                    <span class="text-[9px] sm:text-[10px] text-zinc-500 font-mono block mt-0.5 truncate">
                        Yasta: {{ number_format($totalsByPos['Subte']['yasta'], 0) }} • Yape: {{ number_format($totalsByPos['Subte']['yape'], 0) }}
                    </span>
                </div>
                <div class="hidden sm:flex w-10 h-10 rounded-xl bg-zinc-950 border border-zinc-800 items-center justify-center text-zinc-300 shrink-0 ml-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>

        </div>

        <!-- ========================================================
             3. FORMULARIO MINIMALISTA CON SWITCHES MODO IPHONE & LOGOS
             ======================================================== -->
        <div class="p-5 rounded-2xl theme-card border theme-border shadow-sm">
            <div class="border-b theme-border pb-3 mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-black text-white uppercase tracking-wider font-sans">
                        Registrar Nuevo Cobro QR
                    </h2>
                    <p class="text-[11px] text-zinc-400 mt-0.5">
                        Selecciona el punto de venta, la app y el mesero o bartender
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('qrs.store') }}" 
                  class="space-y-4 {{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }}">
                @csrf
                <input type="hidden" name="night_session_id" value="{{ $session->id }}">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    
                    <!-- 1. PUNTO DE VENTA (SWITCHES MODO IPHONE) -->
                    <div class="md:col-span-4 space-y-1.5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400">
                            Punto de Venta
                        </label>
                        <div class="grid grid-cols-3 p-1 rounded-xl bg-zinc-950 border border-zinc-800 text-xs">
                            <label class="cursor-pointer">
                                <input type="radio" name="point_of_sale" value="Barra Principal" class="peer sr-only" checked>
                                <div class="py-2 text-center rounded-lg font-bold text-zinc-400 transition-all peer-checked:bg-[#F5B81C] peer-checked:text-black peer-checked:font-black peer-checked:shadow-[0_0_12px_rgba(245,184,28,0.35)]">
                                    Principal
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="point_of_sale" value="Tienda" class="peer sr-only">
                                <div class="py-2 text-center rounded-lg font-bold text-zinc-400 transition-all peer-checked:bg-[#F5B81C] peer-checked:text-black peer-checked:font-black peer-checked:shadow-[0_0_12px_rgba(245,184,28,0.35)]">
                                    Tienda
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="point_of_sale" value="Subte" class="peer sr-only">
                                <div class="py-2 text-center rounded-lg font-bold text-zinc-400 transition-all peer-checked:bg-[#F5B81C] peer-checked:text-black peer-checked:font-black peer-checked:shadow-[0_0_12px_rgba(245,184,28,0.35)]">
                                    Subte
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. APLICACIÓN BANCARIA (SWITCHES MODO IPHONE CON LOGOS) -->
                    <div class="md:col-span-3 space-y-1.5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400">
                            Aplicación
                        </label>
                        <div class="grid grid-cols-2 p-1 rounded-xl bg-zinc-950 border border-zinc-800 text-xs">
                            <!-- YASTA -->
                            <label class="cursor-pointer">
                                <input type="radio" name="bank_app" value="YASTA" class="peer sr-only" checked>
                                <div class="py-1.5 px-2 flex items-center justify-center gap-2 rounded-lg font-bold text-zinc-400 transition-all peer-checked:bg-[#F5B81C] peer-checked:text-black peer-checked:font-black peer-checked:shadow-[0_0_12px_rgba(245,184,28,0.35)]">
                                    <img src="{{ asset('images/LogosQR/yasta.png') }}" alt="Yasta" class="h-5 w-auto object-contain rounded">
                                    <span class="tracking-wide">YASTA</span>
                                </div>
                            </label>
                            <!-- YAPE -->
                            <label class="cursor-pointer">
                                <input type="radio" name="bank_app" value="YAPE" class="peer sr-only">
                                <div class="py-1.5 px-2 flex items-center justify-center gap-2 rounded-lg font-bold text-zinc-400 transition-all peer-checked:bg-[#F5B81C] peer-checked:text-black peer-checked:font-black peer-checked:shadow-[0_0_12px_rgba(245,184,28,0.35)]">
                                    <img src="{{ asset('images/LogosQR/yape.png') }}" alt="Yape" class="h-5 w-auto object-contain rounded">
                                    <span class="tracking-wide">YAPE</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 3. COBRANTE (DROPDOWN PERSONALIZADO IOS PURE DARK) -->
                    <div class="md:col-span-3 space-y-1.5 relative" id="cobrante-container">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400">
                            Cobrante
                        </label>
                        <input type="hidden" name="cobrante_name" id="cobrante_name_input" value="" required>
                        
                        <!-- Botón Trigger Personalizado -->
                        <button type="button" 
                                id="cobrante-trigger"
                                onclick="toggleCobranteMenu()"
                                class="w-full flex items-center justify-between text-xs font-bold rounded-xl px-3 py-2.5 bg-zinc-950 border border-zinc-800 text-zinc-300 hover:border-[#F5B81C] focus:outline-none transition-all cursor-pointer shadow-sm">
                            <div class="flex items-center gap-2 truncate">
                                <span id="cobrante-dot" class="w-2 h-2 rounded-full bg-zinc-600 shrink-0"></span>
                                <span id="cobrante-display" class="truncate text-zinc-400 font-bold">Cobrante</span>
                            </div>
                            <svg id="cobrante-chevron" class="w-4 h-4 text-zinc-400 transition-transform duration-200 shrink-0 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Ventana Desplegable iOS Flotante -->
                        <div id="cobrante-menu" 
                             class="hidden absolute left-0 right-0 top-full mt-1.5 z-50 rounded-2xl bg-[#09090b]/98 border border-zinc-800 shadow-2xl backdrop-blur-xl p-2 max-h-72 overflow-y-auto space-y-2">
                            
                            <!-- Buscador Rápido -->
                            <div class="sticky top-0 bg-[#09090b] pb-1 z-10">
                                <div class="relative">
                                    <svg class="w-3.5 h-3.5 text-zinc-500 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" id="cobrante-search" oninput="filterCobrantes(this.value)" placeholder="Buscar cobrante..." 
                                           class="w-full text-xs bg-zinc-950 border border-zinc-800 rounded-xl pl-8 pr-3 py-1.5 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C]">
                                </div>
                            </div>

                            @php
                                $bartenders = $cobrantesStaff->filter(fn($s) => str_contains(mb_strtolower($s->role), 'bartender') || str_contains(mb_strtolower($s->role), 'barra'));
                                $refuerzos  = $cobrantesStaff->filter(fn($s) => str_contains(mb_strtolower($s->role), 'refuerzo'));
                                $meseros    = $cobrantesStaff->reject(fn($s) =>
                                    str_contains(mb_strtolower($s->role), 'bartender') ||
                                    str_contains(mb_strtolower($s->role), 'barra') ||
                                    str_contains(mb_strtolower($s->role), 'refuerzo')
                                );
                            @endphp

                            <!-- Grupo Bartenders -->
                            @if($bartenders->isNotEmpty())
                                <div class="cobrante-group" data-group="bartenders">
                                    <div class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-[#F5B81C]">
                                        BARTENDERS
                                    </div>
                                    <div class="space-y-0.5 mt-0.5">
                                        @foreach($bartenders as $b)
                                            @php
                                                $bRole = str_ireplace('mozo', 'MESERO', $b->role);
                                            @endphp
                                            <div onclick="selectCobrante('{{ $b->name }}')" 
                                                 class="cobrante-item flex items-center justify-between px-2.5 py-2 rounded-xl hover:bg-zinc-800/60 cursor-pointer transition-colors group"
                                                 data-name="{{ $b->name }}"
                                                 data-search="{{ strtolower($b->name . ' ' . $bRole) }}">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <div class="w-6 h-6 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-[10px] font-black text-zinc-300 group-hover:border-[#F5B81C] group-hover:text-white shrink-0">
                                                        {{ substr($b->name, 0, 1) }}
                                                    </div>
                                                    <div class="min-w-0 truncate">
                                                        <span class="text-xs font-bold text-white block truncate">{{ $b->name }}</span>
                                                        <span class="text-[10px] text-zinc-400 font-medium block truncate">{{ strtoupper($bRole) }}</span>
                                                    </div>
                                                </div>
                                                <span class="cobrante-check hidden text-[#F5B81C] text-xs font-black shrink-0 ml-2">✓</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Grupo Meseros -->
                            @if($meseros->isNotEmpty())
                                <div class="cobrante-group" data-group="meseros">
                                    <div class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-[#F5B81C]">
                                        MESEROS
                                    </div>
                                    <div class="space-y-0.5 mt-0.5">
                                        @foreach($meseros as $m)
                                            @php
                                                $cleanRole = str_ireplace(['mozo', 'mozos'], ['MESERO', 'MESEROS'], $m->role);
                                                if (empty($cleanRole) || strtolower($cleanRole) === 'staff') {
                                                    $cleanRole = 'MESERO';
                                                }
                                            @endphp
                                            <div onclick="selectCobrante('{{ $m->name }}')" 
                                                 class="cobrante-item flex items-center justify-between px-2.5 py-2 rounded-xl hover:bg-zinc-800/60 cursor-pointer transition-colors group"
                                                 data-name="{{ $m->name }}"
                                                 data-search="{{ strtolower($m->name . ' ' . $cleanRole) }}">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <div class="w-6 h-6 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-[10px] font-black text-zinc-300 group-hover:border-[#F5B81C] group-hover:text-white shrink-0">
                                                        {{ substr($m->name, 0, 1) }}
                                                    </div>
                                                    <div class="min-w-0 truncate">
                                                        <span class="text-xs font-bold text-white block truncate">{{ $m->name }}</span>
                                                        <span class="text-[10px] text-zinc-400 font-medium block truncate">{{ strtoupper($cleanRole) }}</span>
                                                    </div>
                                                </div>
                                                <span class="cobrante-check hidden text-[#F5B81C] text-xs font-black shrink-0 ml-2">✓</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Grupo Refuerzos -->
                            @if($refuerzos->isNotEmpty())
                                <div class="cobrante-group" data-group="refuerzos">
                                    <div class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-[#F5B81C]">
                                        REFUERZOS
                                    </div>
                                    <div class="space-y-0.5 mt-0.5">
                                        @foreach($refuerzos as $r)
                                            @php
                                                $rRole = str_ireplace('mozo', 'MESERO', $r->role);
                                            @endphp
                                            <div onclick="selectCobrante('{{ $r->name }}')" 
                                                 class="cobrante-item flex items-center justify-between px-2.5 py-2 rounded-xl hover:bg-zinc-800/60 cursor-pointer transition-colors group"
                                                 data-name="{{ $r->name }}"
                                                 data-search="{{ strtolower($r->name . ' ' . $rRole) }}">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <div class="w-6 h-6 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-[10px] font-black text-zinc-300 group-hover:border-[#F5B81C] group-hover:text-white shrink-0">
                                                        {{ substr($r->name, 0, 1) }}
                                                    </div>
                                                    <div class="min-w-0 truncate">
                                                        <span class="text-xs font-bold text-white block truncate">{{ $r->name }}</span>
                                                        <span class="text-[10px] text-zinc-400 font-medium block truncate">{{ strtoupper($rRole) }}</span>
                                                    </div>
                                                </div>
                                                <span class="cobrante-check hidden text-[#F5B81C] text-xs font-black shrink-0 ml-2">✓</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    <!-- 4. MONTO COBRADO & BOTÓN AGREGAR -->
                    <div class="md:col-span-2 space-y-1.5">
                        <label for="amount" class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400">
                            Monto
                        </label>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-sans font-bold text-xs text-[#F5B81C]">Bs.</span>
                                <input type="number" step="any" min="0.01" inputmode="decimal" name="amount" id="amount" required placeholder="0.00"
                                       class="w-full text-base font-mono font-black rounded-xl pl-8 pr-2 py-2 bg-zinc-950 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] placeholder:text-zinc-600">
                            </div>
                            <button type="submit" 
                                    class="py-2.5 px-3.5 bg-[#F5B81C] text-black font-black text-xs rounded-xl hover:bg-[#e5ac18] active:scale-95 transition-all shadow-sm shrink-0 flex items-center gap-1 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                <span>Cobrar</span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        <!-- ========================================================
             4. 3 COLUMNAS POR PUNTO DE VENTA (AUDITORÍA LIMPIA)
             ======================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            @foreach($pointsOfSale as $posName)
                @php
                    $list = $paymentsByPos[$posName];
                    $subtotals = $totalsByPos[$posName];
                    $displayPosName = match($posName) {
                        'Barra Principal' => 'Barra Principal',
                        'Subte' => 'Barra Subterráneo',
                        default => $posName,
                    };
                @endphp
                <div class="rounded-2xl theme-card border theme-border flex flex-col h-full overflow-hidden shadow-sm">
                    
                    <!-- Encabezado de Columna -->
                    <div class="px-4 py-3.5 bg-zinc-950 border-b theme-border">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-black text-white uppercase tracking-wider font-sans">
                                {{ $displayPosName }}
                            </h3>
                            <span class="text-sm font-mono font-black text-white">
                                <span class="text-xs text-[#F5B81C] font-sans mr-0.5">Bs.</span>{{ number_format($subtotals['total'], 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] text-zinc-500 mt-1 font-mono">
                            <span>Yasta: Bs. {{ number_format($subtotals['yasta'], 0) }} • Yape: Bs. {{ number_format($subtotals['yape'], 0) }}</span>
                            <span class="font-sans text-zinc-400">({{ $list->count() }} cobros)</span>
                        </div>
                    </div>

                    <!-- Tabla de Transacciones -->
                    <div class="flex-1 overflow-y-auto max-h-[460px]">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead class="bg-zinc-950/80 border-b border-zinc-800/80 text-zinc-400 uppercase text-[10px] tracking-wider sticky top-0 font-bold backdrop-blur-sm">
                                <tr>
                                    <th class="px-3.5 py-2.5">Cobrante</th>
                                    <th class="px-2 py-2.5 text-center">App</th>
                                    <th class="px-3.5 py-2.5 text-right">Monto</th>
                                    <th class="px-2 py-2.5 text-right w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y border-zinc-800/60 font-sans">
                                @forelse($list as $qr)
                                    <tr id="qr-row-{{ $qr->id }}" class="hover:bg-zinc-900/40 transition-colors">
                                        <td class="px-3.5 py-2.5 font-bold text-zinc-200 uppercase text-xs truncate max-w-[120px]">
                                            {{ $qr->operator_name }}
                                        </td>
                                        <td class="px-2 py-2.5 text-center">
                                            @if($qr->bank_app === 'YASTA')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-zinc-950 border border-zinc-800 text-[10px] font-bold text-zinc-300 font-mono">
                                                    <img src="{{ asset('images/LogosQR/yasta.png') }}" alt="Yasta" class="h-3.5 w-auto object-contain rounded">
                                                    <span>Yasta</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-zinc-950 border border-zinc-800 text-[10px] font-bold text-zinc-300 font-mono">
                                                    <img src="{{ asset('images/LogosQR/yape.png') }}" alt="Yape" class="h-3.5 w-auto object-contain rounded">
                                                    <span>Yape</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-2.5 text-right font-black font-mono text-white text-xs">
                                            Bs. {{ number_format($qr->amount, 2) }}
                                        </td>
                                        <td class="px-2 py-2.5 text-right">
                                            <form method="POST" action="{{ route('qrs.destroy', $qr) }}" 
                                                  class="delete-qr-form {{ !$session->isOpen() ? 'pointer-events-none opacity-40' : '' }}"
                                                  data-qr-id="{{ $qr->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        title="Eliminar cobro"
                                                        class="text-zinc-500 hover:text-rose-400 p-1 rounded-md transition-colors cursor-pointer active:scale-95">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"/>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-zinc-500 text-xs font-sans">
                                            Sin cobros QR en {{ $displayPosName }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pie de la Columna -->
                    <div class="px-4 py-3 bg-zinc-950 border-t border-zinc-800/80 flex items-center justify-between font-sans">
                        <span class="text-[11px] text-zinc-400 font-bold uppercase tracking-wider">Subtotal:</span>
                        <span class="text-xs font-black font-mono text-[#F5B81C]">Bs. {{ number_format($subtotals['total'], 2) }}</span>
                    </div>

                </div>
            @endforeach

        </div>

    @endif

</div>

<!-- Script para eliminación asíncrona sin parpadeo de página -->
<script>
// Control del Selector Personalizado de Cobrante (Estilo iOS)
function toggleCobranteMenu() {
    const menu = document.getElementById('cobrante-menu');
    const chevron = document.getElementById('cobrante-chevron');
    if (!menu) return;
    const isHidden = menu.classList.contains('hidden');
    if (isHidden) {
        menu.classList.remove('hidden');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        const search = document.getElementById('cobrante-search');
        if (search) {
            search.value = '';
            filterCobrantes('');
            setTimeout(() => search.focus(), 60);
        }
    } else {
        menu.classList.add('hidden');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
}

function selectCobrante(name) {
    const input = document.getElementById('cobrante_name_input');
    const display = document.getElementById('cobrante-display');
    const dot = document.getElementById('cobrante-dot');
    const trigger = document.getElementById('cobrante-trigger');

    if (input) input.value = name;
    if (display) {
        display.textContent = name;
        display.className = 'truncate text-white font-black';
    }
    if (dot) {
        dot.className = 'w-2 h-2 rounded-full bg-[#F5B81C] shadow-[0_0_8px_rgba(245,184,28,0.5)] shrink-0';
    }
    if (trigger) {
        trigger.classList.remove('border-rose-500/80');
        trigger.classList.add('border-[#F5B81C]/70');
    }

    // Actualizar checks
    document.querySelectorAll('.cobrante-item').forEach(item => {
        const check = item.querySelector('.cobrante-check');
        if (item.getAttribute('data-name') === name) {
            item.classList.add('bg-zinc-800/80');
            if (check) check.classList.remove('hidden');
        } else {
            item.classList.remove('bg-zinc-800/80');
            if (check) check.classList.add('hidden');
        }
    });

    toggleCobranteMenu();

    const amountInput = document.getElementById('amount');
    if (amountInput) amountInput.focus();
}

function filterCobrantes(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('.cobrante-item').forEach(item => {
        const searchData = item.getAttribute('data-search') || '';
        if (searchData.includes(q)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });

    // Ocultar grupos vacíos
    document.querySelectorAll('.cobrante-group').forEach(group => {
        const visibleItems = Array.from(group.querySelectorAll('.cobrante-item')).filter(i => i.style.display !== 'none');
        group.style.display = visibleItems.length ? 'block' : 'none';
    });
}

// Cerrar al hacer clic afuera
document.addEventListener('click', (e) => {
    const container = document.getElementById('cobrante-container');
    const menu = document.getElementById('cobrante-menu');
    if (container && menu && !container.contains(e.target)) {
        menu.classList.add('hidden');
        const chevron = document.getElementById('cobrante-chevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
});

// Validación al enviar el formulario
document.addEventListener('DOMContentLoaded', () => {
    const qrForm = document.querySelector('form[action="{{ route('qrs.store') }}"]');
    if (qrForm) {
        qrForm.addEventListener('submit', function(e) {
            const cobranteVal = (document.getElementById('cobrante_name_input')?.value || '').trim();
            if (!cobranteVal) {
                e.preventDefault();
                const trigger = document.getElementById('cobrante-trigger');
                if (trigger) {
                    trigger.classList.add('border-rose-500');
                }
                toggleCobranteMenu();
            }
        });
    }

    document.querySelectorAll('.delete-qr-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!confirm('¿Eliminar este cobro QR?')) return;

            const qrId = this.getAttribute('data-qr-id');
            const row = document.getElementById('qr-row-' + qrId);
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
