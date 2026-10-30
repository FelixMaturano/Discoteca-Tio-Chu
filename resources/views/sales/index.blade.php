@extends('layouts.app')

@section('title', 'Ventas por Barra')

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

    /* En modo tabla tradicional: ocultar labels de tarjetas */
    .card-label {
        display: none !important;
    }

    /* ========================================================
       MODO RESPONSIVE AUTOMÁTICO
       - Celular (< 768px): Se adapta automáticamente como tarjetas táctiles
       - PC (>= 768px): Tabla Excel tradicional con columnas congeladas
       ======================================================== */
    @media (max-width: 767px) {
        #sales-form .sales-table-wrapper {
            overflow-x: visible !important;
        }
        #sales-form .excel-table {
            display: block !important;
            width: 100% !important;
            border: none !important;
        }
        #sales-form .excel-table thead {
            display: none !important;
        }
        #sales-form .excel-table tbody {
            display: flex !important;
            flex-direction: column !important;
            gap: 0.75rem !important;
            width: 100% !important;
            padding: 0.75rem !important;
            background: transparent !important;
        }

        /* Fila de Licor convertida en tarjeta táctil */
        #sales-form .liquor-row {
            display: grid !important;
            grid-template-columns: repeat(6, 1fr) !important;
            gap: 0.625rem 0.5rem !important;
            background: #09090b !important;
            border: 1px solid #27272a !important;
            border-radius: 1.25rem !important;
            padding: 1rem !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.6) !important;
            position: relative !important;
        }
        #sales-form .liquor-row:focus-within {
            border-color: rgba(245, 184, 28, 0.6) !important;
            box-shadow: 0 0 20px -2px rgba(245, 184, 28, 0.15) !important;
            background: #0e0c06 !important;
        }

        /* Fila de Mixer convertida en tarjeta táctil */
        #sales-form .mixer-row {
            display: grid !important;
            grid-template-columns: repeat(6, 1fr) !important;
            gap: 0.625rem 0.5rem !important;
            background: #09090b !important;
            border: 1px solid #27272a !important;
            border-radius: 1.25rem !important;
            padding: 1rem !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.6) !important;
            position: relative !important;
        }
        #sales-form .mixer-row:focus-within {
            border-color: rgba(245, 184, 28, 0.6) !important;
            box-shadow: 0 0 20px -2px rgba(245, 184, 28, 0.15) !important;
            background: #0e0c06 !important;
        }

        /* Mostrar labels descriptivos en tarjetas móviles */
        #sales-form .card-label {
            display: block !important;
        }

        /* Inputs en modo tarjeta: 100% de ancho de celda */
        #sales-form input.excel-input {
            width: 100% !important;
            height: 2.6rem !important;
            font-size: 0.95rem !important;
            border-radius: 0.75rem !important;
        }

        /* Asignaciones de Grid para Licor */
        #sales-form .col-num { display: none !important; }
        #sales-form .col-product { 
            grid-column: 1 / 5 !important; 
            border: none !important; 
            padding: 0 !important; 
        }
        #sales-form .col-subtotal { 
            grid-column: 5 / 7 !important; 
            border: none !important; 
            padding: 0 !important; 
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-end !important;
            justify-content: flex-start !important;
            text-align: right !important;
        }
        #sales-form .liquor-row .col-subtotal::before {
            content: "Subtotal Combo";
            display: block;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #71717a;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }
        #sales-form .mixer-row .col-subtotal::before {
            content: "Total Extras";
            display: block;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #71717a;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }
        #sales-form .col-mixer { 
            grid-column: 1 / 4 !important; 
            border: none !important; 
            padding: 0 !important; 
            display: flex !important;
            align-items: center !important;
        }
        #sales-form .col-special { 
            grid-column: 4 / 7 !important; 
            border: none !important; 
            padding: 0 !important; 
            display: flex !important;
            justify-content: flex-end !important;
            align-items: center !important;
        }
        #sales-form .col-pkg { 
            grid-column: 1 / 3 !important; 
            border: none !important; 
            padding: 0 !important; 
        }
        #sales-form .col-units { 
            grid-column: 3 / 5 !important; 
            border: none !important; 
            padding: 0 !important; 
        }
        #sales-form .col-totini { 
            grid-column: 5 / 7 !important; 
            border: none !important; 
            padding: 0 !important; 
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
        }
        #sales-form .col-saldo { 
            grid-column: 1 / 3 !important; 
            border: none !important; 
            padding: 0 !important; 
        }
        #sales-form .col-vendido { 
            grid-column: 3 / 5 !important; 
            border: none !important; 
            padding: 0 !important; 
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
        }
        #sales-form .col-price { 
            grid-column: 5 / 7 !important; 
            border: none !important; 
            padding: 0 !important; 
        }

        /* Asignaciones de Grid para Mixers */
        #sales-form .col-mixer-consumido { 
            grid-column: 3 / 5 !important; 
            border: none !important; 
            padding: 0 !important; 
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
        }
        #sales-form .col-mixer-included { 
            grid-column: 5 / 7 !important; 
            border: none !important; 
            padding: 0 !important; 
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
        }
        #sales-form .col-mixer-extras { 
            grid-column: 1 / 4 !important; 
            border: none !important; 
            padding: 0 !important; 
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
        }
        #sales-form .col-mixer-price { 
            grid-column: 4 / 7 !important; 
            border: none !important; 
            padding: 0 !important; 
        }

        /* Totales en Modo Tarjetas */
        #sales-form tfoot {
            display: block !important;
            background: #09090b !important;
            border-top: 1px solid #27272a !important;
            padding: 0.875rem !important;
        }
        #sales-form tfoot tr {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 0.5rem !important;
            width: 100% !important;
            border: none !important;
        }
        #sales-form tfoot td {
            border: none !important;
            padding: 0 !important;
        }
    }

    /* En PC (>= 768px): Columna de producto congelada sticky */
    @media (min-width: 768px) {
        #sales-form .col-sticky-product {
            position: sticky !important;
            left: 0 !important;
            z-index: 20 !important;
            background-color: #09090b !important;
            box-shadow: 3px 0 8px -2px rgba(0,0,0,0.7) !important;
        }
    }
</style>

<div class="space-y-4 max-w-7xl mx-auto w-full pb-14 sm:pb-8">

    <!-- ========================================================
         1. CABECERA PRINCIPAL: TÍTULO, NOCHE & ACCIÓN DE GUARDAR
         ======================================================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-3.5 sm:px-5 sm:py-3.5 rounded-2xl theme-card border theme-border">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white font-sans">
                Ventas por Barra & Combos
            </h1>
            <p class="text-xs text-zinc-400 mt-0.5">
                Liquidación de combos, consumo de botellas y recaudación por punto de venta
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-between sm:justify-end gap-2 w-full md:w-auto shrink-0">
            <!-- Selector de Noche -->
            @if($allSessions->isNotEmpty())
                <form method="GET" action="{{ route('sales.index') }}" class="flex items-center flex-1 sm:flex-initial">
                    <input type="hidden" name="bar" value="{{ $selectedBar }}">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs hover:border-[#F5B81C] transition-colors w-full sm:w-auto">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <select name="session_id" id="session_id" onchange="this.form.submit()" 
                                class="bg-transparent border-0 text-xs font-semibold text-white focus:outline-none cursor-pointer pr-1 w-full">
                            @foreach($allSessions as $s)
                                <option value="{{ $s->id }}" {{ $session && $session->id === $s->id ? 'selected' : '' }} class="bg-zinc-950 text-white">
                                    {{ $s->day_name }} {{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }} ({{ $s->isOpen() ? 'En Vivo' : 'Cerrada' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif

            <!-- Estado Noche Cerrada (Discreto) -->
            @if($session && !$session->isOpen())
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-zinc-400">
                    <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Modo lectura</span>
                    <a href="{{ route('closing.index', ['session_id' => $session->id]) }}" class="text-[#F5B81C] hover:underline ml-1 font-semibold">Cierre &rarr;</a>
                </div>
            @endif

            <!-- Botón Guardar en Cabecera -->
            @if($session && $session->isOpen())
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <span id="sales-autosave-status" class="text-[10px] font-mono text-zinc-500" aria-live="polite"></span>
                    <button type="submit" form="sales-form" class="px-4 py-2 sm:py-1.5 rounded-xl bg-[#F5B81C] text-black font-bold text-xs hover:bg-[#e5ac18] transition-all flex items-center justify-center gap-1.5 cursor-pointer dilemo-btn shadow-sm w-full sm:w-auto">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ $selectedBar === 'Tienda' ? 'Guardar Ventas Tienda' : 'Guardar Ventas' }}</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if(!$session)
        <!-- Estado Vacío -->
        <div class="p-8 sm:p-12 text-center rounded-2xl theme-card border theme-border">
            <h3 class="text-sm font-bold uppercase tracking-wider text-white">No hay ninguna noche abierta o seleccionada</h3>
            <p class="text-xs text-zinc-400 mt-1 mb-4">Apertura una noche para registrar las ventas.</p>
            <a href="{{ route('sessions.create') }}" class="px-4 py-2 rounded-xl bg-[#F5B81C] text-black font-bold text-xs hover:bg-[#e5ac18] transition-all dilemo-btn inline-block">
                + Aperturar Nueva Noche
            </a>
        </div>
    @else

        <!-- ========================================================
             2. SELECTOR DE PUNTOS DE VENTA (iOS SEGMENTED CONTROL)
             ======================================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 p-2 rounded-2xl theme-card border theme-border">
            <div class="grid grid-cols-3 sm:inline-flex p-1 rounded-xl bg-zinc-950 border border-zinc-800 w-full sm:w-auto">
                @foreach($availableBars as $bar)
                    @php
                        $isSelected = $selectedBar === $bar;
                        $isPrincipal = str_contains($bar, 'Kelly') || str_contains($bar, 'Principal');
                        $isSubte = str_contains($bar, 'Ariel') || str_contains($bar, 'Subterráneo') || str_contains($bar, 'Subte');
                        $badgeLabel = $isPrincipal ? 'Piso Principal' : ($isSubte ? 'Subterráneo' : 'Unidades Sueltas');
                    @endphp
                    <a href="{{ route('sales.index', ['session_id' => $session->id, 'bar' => $bar]) }}" 
                       class="px-3 sm:px-4 py-2 rounded-lg font-bold text-xs transition-all flex items-center justify-center gap-1.5 sm:gap-2 cursor-pointer text-center {{ $isSelected ? 'bg-[#F5B81C] text-black shadow-sm' : 'text-zinc-400 hover:text-white' }}">
                        <span>{{ $bar }}</span>
                        <span class="text-[10px] font-medium opacity-75 hidden md:inline">({{ $badgeLabel }})</span>
                    </a>
                @endforeach
            </div>

            @if($session->isOpen() && $selectedBar !== 'Tienda')
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modal-add-special').classList.remove('hidden')"
                            class="px-3.5 py-2 sm:py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-semibold text-zinc-300 hover:text-white hover:border-zinc-700 transition-all flex items-center justify-center gap-1.5 cursor-pointer dilemo-btn w-full sm:w-auto">
                        <span class="text-[#F5B81C] font-bold">+</span>
                        <span>Nueva Soda en Catálogo</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- MÉTRICAS CLARAS DE LA BARRA / TIENDA (GRID 2x2 EN MÓVIL) -->
        <!-- ======================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3.5">
            
            <!-- Total Bebidas Liquidado / Total Tienda -->
            <div class="theme-card rounded-2xl p-3.5 sm:p-5 border theme-border flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-semibold text-zinc-400 uppercase tracking-wider">{{ $selectedBar === 'Tienda' ? 'Total Tienda' : 'Total Bebidas' }}</span>
                    <span class="text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md bg-zinc-950 border border-zinc-800 text-zinc-400">{{ $selectedBar }}</span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-black font-mono text-[#F5B81C] tracking-tight" id="header-grand-total">
                    <span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>{{ number_format($grandTotalBar, 2) }}
                </div>
                <p class="text-[10px] sm:text-[11px] text-zinc-400 mt-1 font-sans">
                    @if($selectedBar === 'Tienda')
                        <span id="header-tienda-units" class="font-semibold text-white">{{ $totalTiendaUnits ?? $allBarSales->sum('vendido') }} bebidas</span> vendidas
                    @else
                        <span id="header-total-combos" class="font-semibold text-white">{{ $totalLiquorCombos }} combos</span> + <span id="header-total-extras" class="font-semibold text-white">{{ $totalMixerExtras }} extras</span>
                    @endif
                </p>
            </div>

            <!-- Efectivo en Mano en Barra / Tienda -->
            <div class="theme-card rounded-2xl p-3.5 sm:p-5 border theme-border flex flex-col justify-between">
                <span class="text-[11px] sm:text-xs font-semibold text-zinc-400 uppercase tracking-wider block">Efectivo {{ $selectedBar === 'Tienda' ? 'Tienda' : 'en Barra' }}</span>
                <div class="mt-2 text-xl sm:text-2xl font-black font-mono text-emerald-400 tracking-tight">
                    <span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span><span id="header-bar-cash">{{ number_format($barCashRemaining, 2) }}</span>
                </div>
                <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-1 font-sans">
                    Dinero físico en caja
                </p>
            </div>

            <!-- En QR (YASTA / YAPE) -->
            <div class="theme-card rounded-2xl p-3.5 sm:p-5 border theme-border flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-semibold text-zinc-400 uppercase tracking-wider">Cobrado QR</span>
                    <a href="{{ route('qrs.index', ['session_id' => $session->id]) }}" class="text-[10px] font-semibold text-blue-400 hover:underline">Ver QR &rarr;</a>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-black font-mono text-blue-400 tracking-tight">
                    <span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>{{ number_format($selectedBar === 'Tienda' ? $storeSales->sum('qr_amount') : $barTotalQr, 2) }}
                </div>
                <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-1 font-mono truncate">
                    @if($selectedBar === 'Tienda')
                        Despachos cobrados con QR
                    @else
                        YASTA: {{ number_format($barTotalQrYasta, 0) }} • YAPE: {{ number_format($barTotalQrYape, 0) }}
                    @endif
                </p>
            </div>

            <!-- En Tarjetas POS -->
            <div class="theme-card rounded-2xl p-3.5 sm:p-5 border theme-border flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] sm:text-xs font-semibold text-zinc-400 uppercase tracking-wider">Tarjetas (POS)</span>
                    <a href="{{ route('invoices.index', ['session_id' => $session->id]) }}" class="text-[10px] font-semibold text-purple-400 hover:underline">Ver POS &rarr;</a>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-black font-mono text-purple-400 tracking-tight">
                    <span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>{{ number_format($selectedBar === 'Tienda' ? $storeSales->sum('card_amount') : $barTotalCard, 2) }}
                </div>
                <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-1 font-mono truncate">
                    @if($selectedBar === 'Tienda')
                        Despachos cobrados con POS
                    @else
                        Neto: Bs. {{ number_format($barTotalCardNet, 2) }}
                    @endif
                </p>
            </div>
        </div>

        <!-- Modal: Registrar Variante Especial en Catálogo -->
        <div id="modal-add-special" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-3 sm:p-4">
            <div class="theme-card p-4 sm:p-6 rounded-2xl border theme-border max-w-md w-full shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b theme-border pb-3 mb-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider font-sans">Nueva Soda en Catálogo</h3>
                    <button type="button" onclick="document.getElementById('modal-add-special').classList.add('hidden')" class="text-zinc-400 hover:text-white text-base font-bold cursor-pointer">&times;</button>
                </div>
                <p class="text-xs text-zinc-400 mb-4">Define una soda especial para una categoría de tragos (ej: Sprite para Rones o Fernet).</p>
                
                <form method="POST" action="{{ route('sales.specialMixers.store') }}" class="space-y-4 {{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }}">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-zinc-300 mb-1">Categoría del Trago</label>
                        <select name="category" required class="w-full text-xs rounded-xl px-3 py-2 bg-zinc-950 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] cursor-pointer">
                            <option value="" disabled selected class="bg-zinc-950">-- Seleccionar Categoría --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" class="bg-zinc-950">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-300 mb-1">Nombre de la Variante / Soda</label>
                        <input type="text" name="mixer_name" placeholder="Ej: Sprite 2.0L, Aquarius Pera" required 
                               class="w-full text-xs rounded-xl px-3 py-2 bg-zinc-950 border border-zinc-800 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-300 mb-1">Producto Mixer para Descuento en Barra</label>
                        <select name="product_id" required class="w-full text-xs rounded-xl px-3 py-2 bg-zinc-950 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] cursor-pointer">
                            <option value="" disabled selected class="bg-zinc-950">-- Seleccionar Soda / Mixer en Almacén --</option>
                            @foreach($allMixerProducts as $mix)
                                <option value="{{ $mix->id }}" class="bg-zinc-950">{{ $mix->name }} (Bs. {{ number_format($mix->sale_price, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-3">
                        <button type="button" onclick="document.getElementById('modal-add-special').classList.add('hidden')" 
                                class="px-4 py-2 rounded-xl text-xs text-zinc-400 font-semibold bg-zinc-950 border border-zinc-800 hover:text-white cursor-pointer w-full sm:w-auto">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-[#F5B81C] text-black font-bold text-xs rounded-xl hover:bg-[#e5ac18] cursor-pointer dilemo-btn w-full sm:w-auto">Guardar en Catálogo</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Asignar Mixers Especiales a la Fila de Venta -->
        <div id="modal-assign-special" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-3 sm:p-4">
            <div class="theme-card p-4 sm:p-6 rounded-2xl border theme-border max-w-lg w-full shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b theme-border pb-3">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-[#F5B81C] font-bold block">Acompañamiento Alternativo</span>
                        <h3 class="text-base font-bold text-white" id="modal-liquor-title">Licor</h3>
                    </div>
                    <button type="button" onclick="closeSpecialModal()" class="text-zinc-400 hover:text-white text-lg font-bold cursor-pointer">&times;</button>
                </div>

                <div class="bg-zinc-950 rounded-xl p-3 border border-zinc-800 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-zinc-400">Total Combos Vendidos:</span>
                        <span class="font-bold text-white ml-1 text-sm font-mono" id="modal-total-combos">0</span>
                    </div>
                    <div>
                        <span class="text-zinc-400">Soda por Defecto:</span>
                        <span class="font-bold text-[#F5B81C] ml-1" id="modal-default-mixer-name">-</span>
                    </div>
                </div>

                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    <p class="text-[11px] text-zinc-400">
                        Asigna cuántos combos salieron con un mixer alternativo (se restarán automáticamente de la soda por defecto):
                    </p>
                    
                    <div class="space-y-2">
                        @foreach($allMixerProducts as $mixer)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-zinc-950 border border-zinc-800 hover:border-zinc-700 transition-all gap-2">
                                <span class="text-xs font-medium text-white flex-1 min-w-0 truncate">{{ $mixer->name }}</span>
                                <div class="flex items-center gap-2 shrink-0">
                                    <label class="text-[11px] text-zinc-400">Combos:</label>
                                    <input type="number" inputmode="numeric" min="0" value="0" 
                                           data-mixer-id="{{ $mixer->id }}" 
                                           data-mixer-name="{{ $mixer->name }}"
                                           class="modal-special-qty w-16 h-9 text-center rounded-lg px-2 py-1 text-xs font-mono font-bold bg-black border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C]">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-3 bg-zinc-950 border border-zinc-800 rounded-xl flex items-center justify-between text-xs">
                    <span class="text-zinc-300">Sodas por defecto restantes:</span>
                    <span class="font-bold text-[#F5B81C] text-sm font-mono" id="modal-remaining-default">0</span>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-2 pt-2 border-t theme-border">
                    <button type="button" onclick="closeSpecialModal()" class="px-4 py-2 rounded-xl text-xs text-zinc-400 font-semibold bg-zinc-950 border border-zinc-800 hover:text-white cursor-pointer w-full sm:w-auto">Cancelar</button>
                    <button type="button" onclick="saveSpecialModal()" class="px-5 py-2 bg-[#F5B81C] text-black font-bold text-xs rounded-xl hover:bg-[#e5ac18] cursor-pointer dilemo-btn w-full sm:w-auto">Listo / Aplicar</button>
                </div>
            </div>
        </div>

        @if($selectedBar === 'Tienda')
            <!-- ========================================================
                 TABLA DE VENTAS TIENDA OFICIAL — FORMATO DIRECTO
                 ======================================================== -->
            <form method="POST" action="{{ route('sales.updateBulk') }}" id="sales-form" data-ajax="true" class="{{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }} space-y-5 mb-8 pb-16 sm:pb-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="night_session_id" value="{{ $session->id }}">
                <input type="hidden" name="bar_name" value="Tienda">

                <!-- ENCABEZADO TIENDA OFICIAL -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 sm:p-5 rounded-2xl theme-card border theme-border">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#F5B81C]/20 border border-[#F5B81C]/40 flex items-center justify-center text-[#F5B81C] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#F5B81C] text-black">Tienda Oficial</span>
                                <h2 class="text-base sm:text-lg font-bold text-white tracking-tight">Ventas Directas de Unidades Sueltas</h2>
                            </div>
                            <p class="text-xs text-zinc-400 mt-0.5">
                                Anota cuántas bebidas se vendieron en mostrador. La ganancia y el total se calculan en vivo y sincronizan directamente con Bodega Central.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 sm:self-center">
                        <div class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-[10px] text-zinc-400 block uppercase tracking-wider font-semibold">Total Tienda:</span>
                                <span id="tienda-autosave-indicator" class="text-[10px] font-mono text-zinc-500"></span>
                            </div>
                            <span class="text-lg font-black font-mono text-[#F5B81C]" id="badge-tienda-grand-total">
                                Bs. {{ number_format($grandTotalBar, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- BUSCADOR RÁPIDO Y FILTROS POR CATEGORÍA -->
                <div class="p-3.5 rounded-2xl theme-card border theme-border space-y-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </div>
                        <input type="text" 
                               id="tienda-table-search" 
                               oninput="filterTiendaTable()"
                               placeholder="Buscar bebida (ej: San Pedro, Casa Real, Fernet, Ginger, Johnnie Walker)..." 
                               class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-white placeholder-zinc-500 text-xs sm:text-sm focus:outline-none focus:border-[#F5B81C] focus:ring-1 focus:ring-[#F5B81C] transition-all">
                        <button type="button" 
                                onclick="document.getElementById('tienda-table-search').value = ''; filterTiendaTable();" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-zinc-500 hover:text-white cursor-pointer text-xs">
                            &times;
                        </button>
                    </div>

                    <!-- Pastillas de Categorías -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-xs" id="tienda-table-category-pills">
                        <button type="button" onclick="setTiendaTableCategory('todas', this)" class="tienda-pill-btn px-3 py-1.5 rounded-xl font-bold text-xs cursor-pointer transition-all bg-[#F5B81C] text-black shadow-sm shrink-0">Todas</button>
                        <button type="button" onclick="setTiendaTableCategory('singanis', this)" class="tienda-pill-btn px-3 py-1.5 rounded-xl font-semibold text-xs cursor-pointer transition-all bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-white hover:border-zinc-700 shrink-0">Singanis</button>
                        <button type="button" onclick="setTiendaTableCategory('fernet-rones', this)" class="tienda-pill-btn px-3 py-1.5 rounded-xl font-semibold text-xs cursor-pointer transition-all bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-white hover:border-zinc-700 shrink-0">Fernet & Rones</button>
                        <button type="button" onclick="setTiendaTableCategory('whiskys', this)" class="tienda-pill-btn px-3 py-1.5 rounded-xl font-semibold text-xs cursor-pointer transition-all bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-white hover:border-zinc-700 shrink-0">Whiskys</button>
                        <button type="button" onclick="setTiendaTableCategory('gins', this)" class="tienda-pill-btn px-3 py-1.5 rounded-xl font-semibold text-xs cursor-pointer transition-all bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-white hover:border-zinc-700 shrink-0">Gins</button>
                        <button type="button" onclick="setTiendaTableCategory('tequilas-otros', this)" class="tienda-pill-btn px-3 py-1.5 rounded-xl font-semibold text-xs cursor-pointer transition-all bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-white hover:border-zinc-700 shrink-0">Tequilas & Otros</button>
                        <button type="button" onclick="setTiendaTableCategory('mixers', this)" class="tienda-pill-btn px-3 py-1.5 rounded-xl font-semibold text-xs cursor-pointer transition-all bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-white hover:border-zinc-700 shrink-0">Mixers & Sodas</button>
                    </div>
                </div>

                <!-- TABLA FORMATO IDENTICO A LAS OTRAS BARRAS PERO SIMPLIFICADO Y RESPONSIVE EN MÓVIL -->
                <div class="rounded-2xl theme-card border-0 md:border theme-border overflow-hidden bg-transparent md:bg-zinc-950/40">
                    <div class="overflow-x-visible md:overflow-x-auto sales-table-wrapper">
                        <table class="w-full text-xs text-left border-collapse excel-table md:min-w-[750px]" id="table-tienda">
                            <thead class="hidden md:table-header-group bg-zinc-950 border-b theme-border text-zinc-400 font-semibold uppercase text-[11px] tracking-wider">
                                <tr>
                                    <th class="px-2.5 py-2.5 w-10 text-center col-num">N°</th>
                                    <th class="px-3 py-2.5 col-product col-sticky-product">Bebida / Presentación</th>
                                    <th class="px-3 py-2.5 w-32 border-l theme-border">Acompañamiento</th>
                                    <th class="px-3 py-2.5 w-28 text-center border-l theme-border">Especial</th>
                                    <th class="px-3 py-2.5 w-24 text-right border-l theme-border">Precio Venta (Bs.)</th>
                                    <th class="px-3 py-2.5 w-28 text-center border-l theme-border font-bold text-[#F5B81C]">Unidades Vendidas</th>
                                    <th class="px-3 py-2.5 w-28 text-right border-l theme-border font-bold text-[#F5B81C]">Ganancia / Total (Bs.)</th>
                                </tr>
                            </thead>
                            <tbody class="block md:table-row-group divide-y-0 md:divide-y theme-border font-sans space-y-3 md:space-y-0" id="tienda-tbody">
                                @forelse($allBarSales as $index => $sale)
                                    @php
                                        $prod = $sale->product;
                                        if (!$prod) continue;
                                        $isMixer = $prod->category === 'Mixers';
                                        $drinkType = $prod->drink_type;
                                        $catKey = 'tequilas-otros';
                                        if ($isMixer) {
                                            $catKey = 'mixers';
                                        } elseif (str_contains(strtolower($drinkType), 'singani')) {
                                            $catKey = 'singanis';
                                        } elseif (str_contains(strtolower($drinkType), 'ron') || str_contains(strtolower($drinkType), 'fernet')) {
                                            $catKey = 'fernet-rones';
                                        } elseif (str_contains(strtolower($drinkType), 'whisky')) {
                                            $catKey = 'whiskys';
                                        } elseif (str_contains(strtolower($drinkType), 'gin')) {
                                            $catKey = 'gins';
                                        }

                                        $mixerInfo = $mapping[$sale->product_id] ?? null;
                                        $mixerId = $mixerInfo['mixer_id'] ?? 0;
                                        $mixerRatio = $mixerInfo['ratio'] ?? 1;
                                        $mixerName = 'Solo';
                                        if ($mixerId == 23) $mixerName = '1 Ginger Ale 2L';
                                        elseif ($mixerId == 24) $mixerName = '1 Coca Cola 2L';
                                        elseif ($mixerId == 25) $mixerName = '1 Agua Vital 2L';
                                        elseif ($mixerId == 26) $mixerName = '2 Aguas Tónicas 1L';

                                        $specialsJson = is_array($sale->selected_special_mixer) ? json_encode($sale->selected_special_mixer) : ($sale->selected_special_mixer ?? '{}');
                                        $specialsDecoded = [];
                                        if (!empty($sale->selected_special_mixer)) {
                                            $decoded = is_string($sale->selected_special_mixer) ? json_decode($sale->selected_special_mixer, true) : $sale->selected_special_mixer;
                                            if (is_array($decoded)) $specialsDecoded = $decoded;
                                        }

                                        $badgeParts = [];
                                        foreach ($specialsDecoded as $mId => $q) {
                                            if ($q > 0) {
                                                $mObj = $allMixerProducts->firstWhere('id', $mId);
                                                $shortName = $mObj ? explode(' ', $mObj->name)[0] : 'Mixer';
                                                $badgeParts[] = "$shortName ($q)";
                                            }
                                        }
                                        $badgeText = count($badgeParts) > 0 ? implode(', ', $badgeParts) : '+ Especial';
                                        $hasSpecials = count($badgeParts) > 0;
                                        $includedCombos = $sale->included_in_combos ?? 0;
                                        $initialExtras = max(0, (int)$sale->vendido - $includedCombos);
                                    @endphp
                                    <tr class="block md:table-row p-3.5 mb-2.5 md:mb-0 rounded-2xl md:rounded-none bg-zinc-950 md:bg-transparent border border-zinc-800 md:border-0 hover:border-zinc-700 md:hover:bg-zinc-900/40 transition-all tienda-row shadow-sm md:shadow-none space-y-2.5 md:space-y-0"
                                        data-row-id="{{ $sale->id }}"
                                        data-product-id="{{ $sale->product_id }}"
                                        data-product-name="{{ $prod->name }}"
                                        data-is-mixer="{{ $isMixer ? '1' : '0' }}"
                                        data-mixer-id="{{ $mixerId }}"
                                        data-mixer-name="{{ $mixerName }}"
                                        data-mixer-ratio="{{ $mixerRatio }}"
                                        data-included="{{ $includedCombos }}"
                                        data-manual-extra="{{ $initialExtras }}"
                                        data-category-key="{{ $catKey }}"
                                        data-name="{{ strtolower($prod->name) }}">
                                        
                                        <!-- N° (Oculto en móvil) -->
                                        <td class="hidden md:table-cell px-2.5 py-2 text-center text-zinc-500 font-mono col-num text-xs">
                                            {{ $index + 1 }}
                                        </td>

                                        <!-- Bebida / Presentación -->
                                        <td class="block md:table-cell p-0 md:px-3 md:py-2 col-product md:col-sticky-product border-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="leading-tight flex-1 min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="md:hidden text-[10px] font-mono text-zinc-500 font-bold">#{{ $index + 1 }}</span>
                                                        <span class="font-bold text-white text-sm sm:text-xs md:text-sm block truncate">{{ $prod->name }}</span>
                                                    </div>
                                                    <span class="text-[11px] md:text-[10px] text-zinc-400 font-normal block truncate">
                                                        {{ $isMixer ? 'Mixer / Soda' : $drinkType }} • {{ $prod->unit }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Acompañamiento -->
                                        <td class="inline-block md:table-cell p-0 md:px-3 md:py-1.5 md:border-l theme-border text-zinc-300 text-xs border-0 align-middle mr-2 md:mr-0">
                                            @if(!$isMixer)
                                                <span class="inline-flex px-2 py-0.5 rounded-lg bg-zinc-900 border border-zinc-800 text-[11px] font-medium text-zinc-300">
                                                    + {{ $mixerName }}
                                                </span>
                                            @else
                                                <span class="text-[10px] text-zinc-500 font-mono">Soda / Mixer</span>
                                            @endif
                                        </td>

                                        <!-- Especial -->
                                        <td class="inline-block md:table-cell p-0 md:px-3 md:py-1.5 md:border-l theme-border md:text-center border-0 align-middle">
                                            @if(!$isMixer && $mixerId > 0)
                                                <input type="hidden" 
                                                       name="sales[{{ $sale->id }}][selected_special_mixer]" 
                                                       id="input-specials-{{ $sale->id }}" 
                                                       value="{{ $specialsJson }}" 
                                                       class="input-special-mixers">
                                                <button type="button" 
                                                        onclick="openSpecialModal({{ $sale->id }})" 
                                                        id="btn-special-{{ $sale->id }}"
                                                        class="px-2 py-1 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1 cursor-pointer border {{ $hasSpecials ? 'bg-zinc-900 text-[#F5B81C] border-[#F5B81C]/50' : 'bg-zinc-950 text-zinc-400 border-zinc-800 hover:text-white' }}">
                                                    <span id="badge-special-{{ $sale->id }}">{{ $badgeText }}</span>
                                                </button>
                                            @elseif($isMixer)
                                                <span id="tienda-mixer-included-{{ $sale->id }}" 
                                                      class="{{ ($sale->included_in_combos ?? 0) > 0 ? '' : 'hidden' }} inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold font-mono bg-[#F5B81C]/10 text-[#F5B81C] border border-[#F5B81C]/20">
                                                    {{ $sale->included_in_combos ?? 0 }} en combos
                                                </span>
                                            @else
                                                <span class="text-zinc-600 text-xs">-</span>
                                            @endif
                                        </td>

                                        <!-- Precio Venta Unitario -->
                                        <td class="block md:table-cell p-0 md:px-3 md:py-1.5 md:text-right md:border-l theme-border border-0">
                                            <div class="flex items-center justify-between md:justify-end gap-1.5 pt-2 md:pt-0 border-t border-zinc-800/60 md:border-0 text-xs">
                                                <span class="md:hidden text-[11px] text-zinc-400 font-medium">Precio Unitario:</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-zinc-500 font-mono text-[11px]">Bs.</span>
                                                    <input type="number" 
                                                           step="0.5" 
                                                           min="0"
                                                           name="sales[{{ $sale->id }}][unit_price]" 
                                                           id="tienda-price-{{ $sale->id }}"
                                                           value="{{ $sale->unit_price > 0 ? $sale->unit_price : $prod->sale_price }}"
                                                           oninput="onTiendaRowChange({{ $sale->id }})"
                                                           onfocus="this.select()"
                                                           onclick="this.select()"
                                                           class="tienda-input-price w-20 h-7 md:h-8 text-right rounded-lg px-2 font-mono font-bold text-xs bg-zinc-950 border border-zinc-800 text-zinc-200 focus:outline-none focus:ring-1 focus:ring-[#F5B81C] focus:border-[#F5B81C]">
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Unidades Vendidas (Grande y cómodo en móvil, Enter/Flechas en desktop) -->
                                        <td class="inline-block w-[48%] md:w-28 md:table-cell p-0 md:px-3 md:py-1.5 md:border-l theme-border md:text-center border-0 align-top mr-[3%] md:mr-0 pt-2 md:pt-0">
                                            <div class="flex flex-col md:flex-row md:justify-center items-stretch md:items-center gap-1">
                                                <span class="md:hidden text-[10px] font-bold uppercase tracking-wider text-zinc-400">Vendidas</span>
                                                <input type="text" 
                                                       inputmode="numeric" 
                                                       pattern="[0-9]*"
                                                       name="sales[{{ $sale->id }}][vendido]" 
                                                       id="tienda-vendido-{{ $sale->id }}"
                                                       value="{{ (int)$sale->vendido }}"
                                                       data-row-id="{{ $sale->id }}"
                                                       onfocus="this.select()"
                                                       onclick="this.select()"
                                                       oninput="handleTiendaVendidoInput(this, {{ $sale->id }})"
                                                       onkeydown="handleTiendaKeyDown(event, this)"
                                                       onblur="if(this.value.trim()==='') { this.value='0'; onTiendaRowChange({{ $sale->id }}); }"
                                                       class="tienda-input-vendido w-full md:w-20 h-10 md:h-8 text-center rounded-xl md:rounded-lg font-mono font-black text-base md:text-sm bg-zinc-950 border border-zinc-700 text-white focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#1a1606] focus:text-[#F5B81C] transition-all shadow-inner">
                                            </div>
                                        </td>

                                        <!-- Ganancia / Total (Bs.) -->
                                        <td class="inline-block w-[48%] md:w-28 md:table-cell p-0 md:px-3 md:py-1.5 text-right md:border-l theme-border border-0 align-top pt-2 md:pt-0">
                                            <div class="flex flex-col md:flex-row md:justify-end items-stretch md:items-center gap-1">
                                                <span class="md:hidden text-[10px] font-bold uppercase tracking-wider text-[#F5B81C] text-right">Ganancia</span>
                                                <div class="flex items-center justify-end gap-1 w-full md:w-auto">
                                                    <span class="text-zinc-500 font-mono text-[11px] hidden md:inline">Bs.</span>
                                                    <input type="number" 
                                                           step="0.5" 
                                                           min="0"
                                                           name="sales[{{ $sale->id }}][subtotal]" 
                                                           id="tienda-subtotal-{{ $sale->id }}"
                                                           value="{{ number_format($sale->subtotal, 2, '.', '') }}"
                                                           oninput="onTiendaManualSubtotalChange({{ $sale->id }})"
                                                           onfocus="this.select()"
                                                           onclick="this.select()"
                                                           class="tienda-input-subtotal w-full md:w-24 h-10 md:h-8 text-right rounded-xl md:rounded-lg px-2.5 font-mono font-black text-sm md:text-xs bg-zinc-950 border border-zinc-800 text-[#F5B81C] focus:outline-none focus:ring-1 focus:ring-[#F5B81C] focus:border-[#F5B81C]">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="block md:table-row p-6 bg-zinc-950 rounded-2xl border border-zinc-800 text-center">
                                        <td colspan="7" class="block md:table-cell px-4 py-8 text-center text-zinc-500 font-sans">No hay bebidas registradas en Tienda.</td>
                                    </tr>
                                @endforelse
                                <tr id="tienda-empty-filter-row" class="hidden block md:table-row p-6 bg-zinc-950 rounded-2xl border border-zinc-800 text-center">
                                    <td colspan="7" class="block md:table-cell px-4 py-8 text-center text-zinc-500 font-sans">No se encontraron bebidas con ese filtro o búsqueda.</td>
                                </tr>
                            </tbody>
                            <tfoot class="block md:table-footer-group bg-zinc-950 border-t theme-border font-bold text-xs text-white rounded-2xl md:rounded-none p-3.5 mt-3 md:p-0 md:mt-0">
                                <tr class="flex items-center justify-between md:table-row">
                                    <td colspan="5" class="px-0 md:px-4 py-0 md:py-2.5 text-left md:text-right uppercase tracking-wider font-sans text-xs text-zinc-400 md:text-white">
                                        <span>Total Ventas Tienda:</span>
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-2.5 text-center font-mono font-black text-[#F5B81C] text-sm" id="tfoot-tienda-units">
                                        {{ $totalTiendaUnits ?? $allBarSales->sum('vendido') }}
                                    </td>
                                    <td class="px-0 md:px-4 py-0 md:py-2.5 text-right font-mono font-black text-[#F5B81C] text-base md:text-sm" id="tfoot-tienda-subtotal">
                                        Bs. {{ number_format($allBarSales->sum('subtotal'), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Guardarropa y Snacks (Para el Cierre de Caja) -->
                <div class="p-4 sm:p-5 rounded-2xl theme-card border theme-border space-y-3">
                    <div class="flex items-center justify-between border-b theme-border pb-2.5">
                        <div>
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-sans">Ingresos Adicionales de Tienda</h3>
                            <p class="text-[11px] text-zinc-400">Guardarropa y snacks recaudados durante la noche.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800 space-y-1.5">
                            <label class="text-[11px] font-bold text-zinc-300 uppercase tracking-wider block">Guardarropa (Bs.):</label>
                            <div class="flex items-center gap-2">
                                <span class="text-zinc-500 font-mono text-xs">Bs.</span>
                                <input type="number" 
                                       step="0.5" 
                                       min="0"
                                       name="guardarropa_amount" 
                                       value="{{ $closing->total_guardarropa ?? 0 }}"
                                       oninput="window.queueSalesAutosave?.()"
                                       class="w-full h-9 rounded-xl bg-black border border-zinc-800 px-3 font-mono font-bold text-sm text-white focus:outline-none focus:border-[#F5B81C]">
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-800 space-y-1.5">
                            <label class="text-[11px] font-bold text-zinc-300 uppercase tracking-wider block">Snacks / Alimentos (Bs.):</label>
                            <div class="flex items-center gap-2">
                                <span class="text-zinc-500 font-mono text-xs">Bs.</span>
                                <input type="number" 
                                       step="0.5" 
                                       min="0"
                                       name="snacks_amount" 
                                       value="{{ $closing->total_snacks ?? 0 }}"
                                       oninput="window.queueSalesAutosave?.()"
                                       class="w-full h-9 rounded-xl bg-black border border-zinc-800 px-3 font-mono font-bold text-sm text-white focus:outline-none focus:border-[#F5B81C]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón Inferior de Guardar -->
                <div class="p-4 rounded-2xl theme-card border theme-border flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="w-9 h-9 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-[#F5B81C] shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-400 block font-normal">Total Ganancia Tienda:</span>
                            <span class="text-lg font-bold font-mono text-white" id="footer-tienda-grand-total">
                                Bs. {{ number_format($grandTotalBar, 2) }}
                            </span>
                        </div>
                    </div>

                    @if($session->isOpen())
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#F5B81C] text-black font-bold text-xs uppercase tracking-wider hover:bg-[#e5ac18] transition-all flex items-center justify-center gap-2 dilemo-btn cursor-pointer shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Guardar Ventas Tienda</span>
                        </button>
                    @else
                        <div class="px-4 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-400 text-xs font-semibold flex items-center gap-2 w-full sm:w-auto justify-center">
                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>Noche cerrada (solo lectura)</span>
                        </div>
                    @endif
                </div>
            </form>
        @endif

        <!-- Formulario Tabular de Inventario y Ventas para Barras (Principal y Subterráneo) -->
        @if($selectedBar !== 'Tienda')
        <form method="POST" action="{{ route('sales.updateBulk') }}" id="sales-form" data-ajax="true" class="{{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="night_session_id" value="{{ $session->id }}">
            <input type="hidden" name="bar_name" value="{{ $selectedBar }}">

            <div class="space-y-4">

                <!-- 1. TABLA DE LICORES (COMBOS) -->
                <div class="rounded-2xl theme-card border theme-border overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 bg-zinc-950 border-b theme-border flex flex-wrap items-center justify-between gap-2.5">
                        <div>
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-sans">1. Licores & Combos</h3>
                            <p class="text-[11px] text-zinc-400 mt-0.5">
                                Stock sincronizado desde <a href="{{ route('barInventory.index', ['session_id' => $session->id, 'bar' => $selectedBar]) }}" class="text-[#F5B81C] hover:underline font-semibold">Inventario de Barras</a>.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-mono font-bold text-[#F5B81C]" id="badge-liquor-subtotal">
                                Subtotal: Bs. {{ number_format($subtotalLiquors, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto sales-table-wrapper">
                        <table class="w-full text-xs text-left border-collapse excel-table" id="table-liquors">
                            <thead class="bg-zinc-950 border-b theme-border text-zinc-400 font-semibold uppercase text-[11px] tracking-wider">
                                <tr>
                                    <th class="px-3 py-3.5 w-8 text-center col-num">N°</th>
                                    <th class="px-4 py-3.5 col-product col-sticky-product">Licor / Presentación</th>
                                    <th class="px-4 py-3.5 col-mixer">Acompañamiento</th>
                                    <th class="px-3 py-3.5 text-center col-special">Especial</th>
                                    <th class="px-2 py-3.5 w-16 text-center border-l theme-border bg-zinc-950/60 col-pkg">
                                        <div class="flex flex-col items-center">
                                            <span class="text-zinc-200">Cajas</span>
                                            <span class="text-[9px] text-zinc-500 lowercase">stock</span>
                                        </div>
                                    </th>
                                    <th class="px-2 py-3.5 w-16 text-center bg-zinc-950/60 col-units">
                                        <div class="flex flex-col items-center">
                                            <span class="text-zinc-200">Sueltas</span>
                                            <span class="text-[9px] text-zinc-500 lowercase">stock</span>
                                        </div>
                                    </th>
                                    <th class="px-3 py-3.5 w-20 text-center border-l theme-border col-totini">Total Inicial</th>
                                    <th class="px-3 py-3.5 w-24 text-center border-l theme-border bg-zinc-950/80 col-saldo">
                                        <div class="flex flex-col items-center text-[#F5B81C]">
                                            <span class="font-bold">Saldo Cierre</span>
                                            <span class="text-[9px] text-zinc-400 lowercase">conteo</span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-3.5 w-24 text-center border-l theme-border font-bold text-[#F5B81C] col-vendido">Combos Vendidos</th>
                                    <th class="px-4 py-3.5 w-28 text-right border-l theme-border col-price">Precio Combo</th>
                                    <th class="px-4 py-3.5 w-32 text-right border-l theme-border col-subtotal">Subtotal (Bs.)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y theme-border font-sans">
                                @forelse($liquorSales as $index => $sale)
                                    @php
                                        $mixerInfo = $mapping[$sale->product_id] ?? null;
                                        $mixerId = $mixerInfo['mixer_id'] ?? 0;
                                        $mixerRatio = $mixerInfo['ratio'] ?? 1;
                                        $mixerName = 'Solo';
                                        if ($mixerId == 23) $mixerName = '1 Ginger Ale 2L';
                                        elseif ($mixerId == 24) $mixerName = '1 Coca Cola 2L';
                                        elseif ($mixerId == 25) $mixerName = '1 Agua Vital 2L';
                                        elseif ($mixerId == 26) $mixerName = '2 Aguas Tónicas 1L';

                                        $specialsJson = is_array($sale->selected_special_mixer) ? json_encode($sale->selected_special_mixer) : ($sale->selected_special_mixer ?? '{}');
                                        $specialsDecoded = [];
                                        if (!empty($sale->selected_special_mixer)) {
                                            $decoded = is_string($sale->selected_special_mixer) ? json_decode($sale->selected_special_mixer, true) : $sale->selected_special_mixer;
                                            if (is_array($decoded)) $specialsDecoded = $decoded;
                                        }

                                        $badgeParts = [];
                                        foreach ($specialsDecoded as $mId => $q) {
                                            if ($q > 0) {
                                                $mObj = $allMixerProducts->firstWhere('id', $mId);
                                                $shortName = $mObj ? explode(' ', $mObj->name)[0] : 'Mixer';
                                                $badgeParts[] = "$shortName ($q)";
                                            }
                                        }
                                        $badgeText = count($badgeParts) > 0 ? implode(', ', $badgeParts) : '+ Especial';
                                        $hasSpecials = count($badgeParts) > 0;
                                    @endphp
                                    <tr class="hover:bg-zinc-900/40 transition-colors liquor-row" 
                                        data-row-id="{{ $sale->id }}" 
                                        data-product-id="{{ $sale->product_id }}"
                                        data-product-name="{{ $sale->product->name }}"
                                        data-units-per-pkg="{{ $sale->product->units_per_package ?? 1 }}"
                                        data-mixer-id="{{ $mixerId }}"
                                        data-mixer-name="{{ $mixerName }}"
                                        data-mixer-ratio="{{ $mixerRatio }}">
                                        
                                        <!-- N° -->
                                        <td class="px-3 py-2.5 text-center text-zinc-500 font-mono col-num">{{ $index + 1 }}</td>
                                        
                                        <!-- Nombre y Presentación -->
                                        <td class="px-4 py-2.5 col-product col-sticky-product">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <span class="font-bold text-white block text-sm sm:text-xs">{{ $sale->product->name }}</span>
                                                    <span class="text-[11px] text-zinc-400 font-normal">{{ $sale->product->unit }} • {{ $sale->product->units_per_package ?? 1 }} unid/caja</span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Acompañamiento -->
                                        <td class="px-4 py-2.5 text-zinc-300 text-xs col-mixer">
                                            <span class="inline-flex px-2 py-0.5 rounded-lg bg-zinc-950 border border-zinc-800 text-[11px] font-medium">
                                                + {{ $mixerName }}
                                            </span>
                                        </td>
                                        
                                        <!-- Botón de Mixer Especial -->
                                        <td class="px-3 py-2 text-center col-special">
                                            <input type="hidden" 
                                                   name="sales[{{ $sale->id }}][selected_special_mixer]" 
                                                   id="input-specials-{{ $sale->id }}" 
                                                   value="{{ $specialsJson }}" 
                                                   class="input-special-mixers">
                                            <button type="button" 
                                                    onclick="openSpecialModal({{ $sale->id }})" 
                                                    id="btn-special-{{ $sale->id }}"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1 cursor-pointer border {{ $hasSpecials ? 'bg-zinc-900 text-[#F5B81C] border-[#F5B81C]/50' : 'bg-zinc-950 text-zinc-400 border-zinc-800 hover:text-white' }}">
                                                <span id="badge-special-{{ $sale->id }}">{{ $badgeText }}</span>
                                            </button>
                                        </td>

                                        <!-- Cajas (Stock) -->
                                        <td class="px-2 py-2 text-center border-l theme-border col-pkg">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Cajas (Stock)</span>
                                            <input type="number" 
                                                   inputmode="numeric"
                                                   data-excel-col="0"
                                                   name="sales[{{ $sale->id }}][packages]" 
                                                   value="{{ $sale->packages }}" 
                                                   min="0"
                                                   class="excel-input input-packages w-14 sm:w-16 h-10 sm:h-9 text-center rounded-xl font-mono font-bold text-xs sm:text-sm bg-zinc-950 border border-zinc-800 text-white focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#181507] focus:text-[#F5B81C] transition-all">
                                        </td>

                                        <!-- Sueltas (Stock) -->
                                        <td class="px-2 py-2 text-center col-units">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Sueltas (Stock)</span>
                                            <input type="number" 
                                                   inputmode="numeric"
                                                   data-excel-col="1"
                                                   name="sales[{{ $sale->id }}][units]" 
                                                   value="{{ $sale->units }}" 
                                                   min="0"
                                                   class="excel-input input-units w-14 sm:w-16 h-10 sm:h-9 text-center rounded-xl font-mono font-bold text-xs sm:text-sm bg-zinc-950 border border-zinc-800 text-white focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#181507] focus:text-[#F5B81C] transition-all">
                                        </td>

                                        <!-- Total Inicial -->
                                        <td class="px-3 py-2.5 text-center border-l theme-border col-totini">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Total Inicial</span>
                                            <div class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-zinc-950 border border-zinc-800 font-bold font-mono text-zinc-300 text-xs h-10 sm:h-auto w-full sm:w-auto cell-total-initial">
                                                {{ $sale->total_initial }}
                                            </div>
                                        </td>

                                        <!-- Saldo Cierre -->
                                        <td class="px-3 py-2 text-center border-l theme-border bg-zinc-950/40 col-saldo">
                                            <span class="card-label text-[10px] font-bold text-[#F5B81C] mb-1">Saldo Cierre</span>
                                            <input type="number" 
                                                   inputmode="numeric"
                                                   data-excel-col="2"
                                                   name="sales[{{ $sale->id }}][saldo]" 
                                                   value="{{ $sale->saldo }}" 
                                                   min="0"
                                                   class="excel-input input-saldo w-16 sm:w-20 h-10 sm:h-9 text-center rounded-xl font-mono font-bold text-xs sm:text-sm bg-zinc-950 border-2 border-zinc-700/80 text-white focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#181507] focus:text-[#F5B81C] transition-all shadow-inner">
                                        </td>

                                        <!-- Combos Vendidos -->
                                        <td class="px-4 py-2.5 text-center border-l theme-border col-vendido">
                                            <span class="card-label text-[10px] font-bold text-[#F5B81C] mb-1">Combos Vendidos</span>
                                            <div class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-zinc-950 border border-zinc-800 font-black font-mono text-[#F5B81C] text-sm sm:text-base h-10 sm:h-auto w-full sm:w-auto cell-vendido">
                                                {{ $sale->vendido }}
                                            </div>
                                        </td>

                                        <!-- Precio Combo -->
                                        <td class="px-4 py-2 text-right border-l theme-border col-price">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Precio Combo</span>
                                            <input type="number" 
                                                   step="0.5" 
                                                   inputmode="decimal"
                                                   data-excel-col="3"
                                                   name="sales[{{ $sale->id }}][unit_price]" 
                                                   value="{{ $sale->unit_price }}" 
                                                   min="0"
                                                   class="excel-input input-price w-20 sm:w-24 h-10 sm:h-9 text-right rounded-xl px-2 font-mono font-bold text-xs sm:text-sm bg-zinc-950 border border-zinc-800 text-zinc-200 focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#181507] focus:text-[#F5B81C] transition-all">
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="px-4 py-2.5 text-right font-bold font-mono text-white text-xs sm:text-sm border-l theme-border cell-subtotal col-subtotal">
                                            Bs. {{ number_format($sale->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-4 py-8 text-center text-zinc-500 font-sans">No hay licores registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-zinc-950 border-t theme-border font-bold text-xs text-white">
                                <tr>
                                    <td colspan="8" class="px-4 py-3 text-right uppercase tracking-wider font-sans">
                                        <span>Subtotal Combos:</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-black text-[#F5B81C] text-base" id="tfoot-liquor-vendido">
                                        {{ $totalLiquorCombos }}
                                    </td>
                                    <td class="hidden sm:table-cell"></td>
                                    <td class="px-4 py-3 text-right font-mono font-black text-[#F5B81C] text-sm" id="tfoot-liquor-subtotal">
                                        Bs. {{ number_format($subtotalLiquors, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- 2. TABLA DE MIXERS / SODAS (EXTRAS) -->
                <div class="rounded-2xl theme-card border theme-border overflow-hidden">
                    <div class="px-4 sm:px-5 py-3.5 bg-zinc-950 border-b theme-border flex flex-wrap items-center justify-between gap-2.5">
                        <div>
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-sans">2. Mixers, Sodas & Aguas (Control de Extras)</h3>
                            <p class="text-[11px] text-zinc-400 mt-0.5">
                                Las sodas de combos se descuentan automáticamente. Solo las adicionales (extras) se cobran.
                            </p>
                        </div>
                        <span class="text-xs font-mono font-bold text-[#F5B81C]" id="badge-mixer-subtotal">
                            Subtotal Extras: Bs. {{ number_format($subtotalMixers, 2) }}
                        </span>
                    </div>

                    <div class="overflow-x-auto sales-table-wrapper">
                        <table class="w-full text-xs text-left border-collapse excel-table" id="table-mixers">
                            <thead class="bg-zinc-950 border-b theme-border text-zinc-400 font-semibold uppercase text-[11px] tracking-wider">
                                <tr>
                                    <th class="px-3 py-3.5 w-8 text-center col-num">N°</th>
                                    <th class="px-4 py-3.5 col-product col-sticky-product">Mixer / Soda</th>
                                    <th class="px-2 py-3.5 w-16 text-center border-l theme-border bg-zinc-950/60 col-pkg">
                                        <div class="flex flex-col items-center">
                                            <span class="text-zinc-200">Cajas</span>
                                            <span class="text-[9px] text-zinc-500 lowercase">stock</span>
                                        </div>
                                    </th>
                                    <th class="px-2 py-3.5 w-16 text-center bg-zinc-950/60 col-units">
                                        <div class="flex flex-col items-center">
                                            <span class="text-zinc-200">Sueltas</span>
                                            <span class="text-[9px] text-zinc-500 lowercase">stock</span>
                                        </div>
                                    </th>
                                    <th class="px-3 py-3.5 w-20 text-center border-l theme-border col-totini">Total Inicial</th>
                                    <th class="px-3 py-3.5 w-24 text-center border-l theme-border bg-zinc-950/80 col-saldo">
                                        <div class="flex flex-col items-center text-[#F5B81C]">
                                            <span class="font-bold">Saldo Cierre</span>
                                            <span class="text-[9px] text-zinc-400 lowercase">conteo</span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-3.5 w-20 text-center border-l theme-border col-mixer-consumido">Consumidas</th>
                                    <th class="px-4 py-3.5 w-24 text-center border-l theme-border text-zinc-400 col-mixer-included">En Combos</th>
                                    <th class="px-4 py-3.5 w-20 text-center border-l theme-border font-bold text-[#F5B81C] col-mixer-extras">Extras</th>
                                    <th class="px-4 py-3.5 w-28 text-right border-l theme-border col-mixer-price">Precio Extra</th>
                                    <th class="px-4 py-3.5 w-32 text-right border-l theme-border col-subtotal">Total Extras (Bs.)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y theme-border font-sans">
                                @forelse($mixerSales as $index => $sale)
                                    <tr class="hover:bg-zinc-900/40 transition-colors mixer-row" 
                                        data-row-id="{{ $sale->id }}" 
                                        data-units-per-pkg="{{ $sale->product->units_per_package ?? 1 }}"
                                        data-product-id="{{ $sale->product_id }}">
                                        
                                        <!-- N° -->
                                        <td class="px-3 py-2.5 text-center text-zinc-500 font-mono col-num">{{ $index + 1 }}</td>
                                        
                                        <!-- Mixer Name -->
                                        <td class="px-4 py-2.5 font-bold text-white col-product col-sticky-product">
                                            {{ $sale->product->name }}
                                        </td>

                                        <!-- Cajas (Stock) -->
                                        <td class="px-2 py-2 text-center border-l theme-border col-pkg">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Cajas (Stock)</span>
                                            <input type="number" 
                                                   inputmode="numeric"
                                                   data-excel-col="0"
                                                   name="sales[{{ $sale->id }}][packages]" 
                                                   value="{{ $sale->packages }}" 
                                                   min="0"
                                                   class="excel-input input-packages w-14 sm:w-16 h-10 sm:h-9 text-center rounded-xl font-mono font-bold text-xs sm:text-sm bg-zinc-950 border border-zinc-800 text-white focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#181507] focus:text-[#F5B81C] transition-all">
                                        </td>

                                        <!-- Sueltas (Stock) -->
                                        <td class="px-2 py-2 text-center col-units">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Sueltas (Stock)</span>
                                            <input type="number" 
                                                   inputmode="numeric"
                                                   data-excel-col="1"
                                                   name="sales[{{ $sale->id }}][units]" 
                                                   value="{{ $sale->units }}" 
                                                   min="0"
                                                   class="excel-input input-units w-14 sm:w-16 h-10 sm:h-9 text-center rounded-xl font-mono font-bold text-xs sm:text-sm bg-zinc-950 border border-zinc-800 text-white focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#181507] focus:text-[#F5B81C] transition-all">
                                        </td>

                                        <!-- Total Inicial -->
                                        <td class="px-3 py-2.5 text-center border-l theme-border col-totini">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Total Inicial</span>
                                            <div class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-zinc-950 border border-zinc-800 font-bold font-mono text-zinc-300 text-xs h-10 sm:h-auto w-full sm:w-auto cell-total-initial">
                                                {{ $sale->total_initial }}
                                            </div>
                                        </td>

                                        <!-- Saldo Cierre -->
                                        <td class="px-3 py-2 text-center border-l theme-border bg-zinc-950/40 col-saldo">
                                            <span class="card-label text-[10px] font-bold text-[#F5B81C] mb-1">Saldo Cierre</span>
                                            <input type="number" 
                                                   inputmode="numeric"
                                                   data-excel-col="2"
                                                   name="sales[{{ $sale->id }}][saldo]" 
                                                   value="{{ $sale->saldo }}" 
                                                   min="0"
                                                   class="excel-input input-saldo w-16 sm:w-20 h-10 sm:h-9 text-center rounded-xl font-mono font-bold text-xs sm:text-sm bg-zinc-950 border-2 border-zinc-700/80 text-white focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#181507] focus:text-[#F5B81C] transition-all shadow-inner">
                                        </td>

                                        <!-- Consumidas -->
                                        <td class="px-4 py-2.5 text-center border-l theme-border col-mixer-consumido">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Consumo</span>
                                            <div class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-zinc-950 border border-zinc-800 font-mono text-zinc-300 text-xs h-10 sm:h-auto w-full sm:w-auto cell-consumido">
                                                {{ $sale->vendido }}
                                            </div>
                                        </td>

                                        <!-- En Combos -->
                                        <td class="px-4 py-2.5 text-center border-l theme-border col-mixer-included">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">En Combos</span>
                                            <div class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-zinc-950 border border-zinc-800 font-mono text-zinc-400 text-xs h-10 sm:h-auto w-full sm:w-auto cell-included">
                                                {{ $sale->included_in_combos ?? 0 }}
                                            </div>
                                        </td>

                                        <!-- Extras -->
                                        <td class="px-4 py-2.5 text-center border-l theme-border col-mixer-extras">
                                            <span class="card-label text-[10px] font-bold text-[#F5B81C] mb-1">Extras Cobrar</span>
                                            <div class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-zinc-950 border border-zinc-800 font-black font-mono text-[#F5B81C] text-sm sm:text-base h-10 sm:h-auto w-full sm:w-auto cell-extras">
                                                {{ $sale->extras ?? 0 }}
                                            </div>
                                        </td>

                                        <!-- Precio Extra -->
                                        <td class="px-4 py-2 text-right border-l theme-border col-mixer-price">
                                            <span class="card-label text-[10px] font-semibold text-zinc-400 mb-1">Precio Extra</span>
                                            <input type="number" 
                                                   step="0.5" 
                                                   inputmode="decimal"
                                                   data-excel-col="3"
                                                   name="sales[{{ $sale->id }}][unit_price]" 
                                                   value="{{ $sale->unit_price }}" 
                                                   min="0"
                                                   class="excel-input input-price w-20 sm:w-24 h-10 sm:h-9 text-right rounded-xl px-2 font-mono font-bold text-xs sm:text-sm bg-zinc-950 border border-zinc-800 text-zinc-200 focus:outline-none focus:ring-2 focus:ring-[#F5B81C] focus:border-[#F5B81C] focus:bg-[#181507] focus:text-[#F5B81C] transition-all">
                                        </td>

                                        <!-- Subtotal Extras -->
                                        <td class="px-4 py-2.5 text-right font-bold font-mono text-white text-xs sm:text-sm border-l theme-border cell-subtotal col-subtotal">
                                            Bs. {{ number_format($sale->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-4 py-8 text-center text-zinc-500 font-sans">No hay mixers registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-zinc-950 border-t theme-border font-bold text-xs text-white">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right uppercase tracking-wider font-sans">
                                        <span>Totales Mixers:</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-bold" id="tfoot-mixer-consumido">
                                        {{ $totalMixerConsumed }}
                                    </td>
                                    <td class="hidden sm:table-cell"></td>
                                    <td class="px-4 py-3 text-center font-mono font-black text-[#F5B81C] text-base" id="tfoot-mixer-extras">
                                        {{ $totalMixerExtras }}
                                    </td>
                                    <td class="hidden sm:table-cell"></td>
                                    <td class="px-4 py-3 text-right font-mono font-black text-[#F5B81C] text-sm" id="tfoot-mixer-subtotal">
                                        Bs. {{ number_format($subtotalMixers, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- DETALLE DE COBROS DIGITALES (QR Y TARJETAS POS) -->
                <!-- ======================================================== -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    
                    <!-- 1. TABLA COBROS QR EN ESTA BARRA -->
                    <div class="rounded-2xl theme-card border theme-border overflow-hidden">
                        <div class="px-4 py-3 bg-zinc-950 border-b theme-border flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider font-sans">
                                    Cobros QR en {{ $selectedBar }}
                                </h3>
                                <p class="text-[11px] text-zinc-400">Transferencias YASTA & YAPE</p>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-xs font-mono font-bold text-blue-400">
                                    Bs. {{ number_format($barTotalQr, 2) }}
                                </span>
                                <a href="{{ route('qrs.index', ['session_id' => $session->id]) }}" 
                                   class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-zinc-900 border border-zinc-800 text-blue-400 hover:text-white transition-all">
                                    Ver QR &rarr;
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto max-h-60 overflow-y-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead class="bg-zinc-950 border-b theme-border text-zinc-400 uppercase text-[10px] tracking-wider sticky top-0 font-semibold">
                                    <tr>
                                        <th class="px-4 py-2.5">Cobrante</th>
                                        <th class="px-3 py-2.5 text-center">App</th>
                                        <th class="px-4 py-2.5 text-right">Monto (Bs.)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y theme-border font-sans">
                                    @forelse($barQrPayments as $qr)
                                        <tr class="hover:bg-zinc-900/30">
                                            <td class="px-4 py-2 text-zinc-200 font-medium">{{ $qr->operator_name ?? '—' }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-900 border border-zinc-800 text-zinc-300">
                                                    {{ $qr->bank_app }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right font-bold font-mono text-blue-400">Bs. {{ number_format($qr->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-zinc-500 text-xs">No hay cobros QR registrados para esta barra.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. TABLA TARJETAS POS EN ESTA BARRA -->
                    <div class="rounded-2xl theme-card border theme-border overflow-hidden">
                        <div class="px-4 py-3 bg-zinc-950 border-b theme-border flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider font-sans">
                                    Pagos con Tarjeta (POS) en {{ $selectedBar }}
                                </h3>
                                <p class="text-[11px] text-zinc-400">Facturas y cobro con tarjeta</p>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-xs font-mono font-bold text-purple-400">
                                    Bs. {{ number_format($barTotalCard, 2) }}
                                </span>
                                <a href="{{ route('invoices.index', ['session_id' => $session->id]) }}" 
                                   class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-zinc-900 border border-zinc-800 text-purple-400 hover:text-white transition-all">
                                    Ver POS &rarr;
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto max-h-60 overflow-y-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead class="bg-zinc-950 border-b theme-border text-zinc-400 uppercase text-[10px] tracking-wider sticky top-0 font-semibold">
                                    <tr>
                                        <th class="px-4 py-2.5 text-center"># Factura</th>
                                        <th class="px-4 py-2.5">Detalle</th>
                                        <th class="px-4 py-2.5 text-right">Comisión</th>
                                        <th class="px-4 py-2.5 text-right">Monto Bruto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y theme-border font-sans">
                                    @forelse($barCardInvoices as $inv)
                                        <tr class="hover:bg-zinc-900/30">
                                            <td class="px-4 py-2 text-center text-zinc-400 font-mono font-bold">#{{ $inv->correlative_num }}</td>
                                            <td class="px-4 py-2 text-zinc-300 text-[11px] truncate max-w-[140px]">{{ $inv->notes ?? 'Pago Tarjeta POS' }}</td>
                                            <td class="px-4 py-2 text-right text-zinc-500 text-[11px] font-mono">- Bs. {{ number_format($inv->commission_amount, 2) }}</td>
                                            <td class="px-4 py-2 text-right font-bold font-mono text-purple-400">Bs. {{ number_format($inv->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-zinc-500 text-xs">No hay pagos con tarjeta registrados para esta barra.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Pie de Formulario Estático con Guardado -->
                <div class="p-4 rounded-2xl theme-card border theme-border flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="w-9 h-9 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-[#F5B81C] shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-400 block font-normal">Total Bebidas Liquidado en {{ $selectedBar }}:</span>
                            <span class="text-lg font-bold font-mono text-white" id="footer-grand-total">
                                Bs. {{ number_format($grandTotalBar, 2) }}
                            </span>
                        </div>
                    </div>

                    @if($session->isOpen())
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#F5B81C] text-black font-bold text-xs uppercase tracking-wider hover:bg-[#e5ac18] transition-all flex items-center justify-center gap-2 dilemo-btn cursor-pointer shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Guardar Cambios de {{ $selectedBar }}</span>
                        </button>
                    @else
                        <div class="px-4 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-400 text-xs font-semibold flex items-center gap-2 w-full sm:w-auto justify-center">
                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>Noche cerrada (solo lectura)</span>
                        </div>
                    @endif
                </div>

            </div>
        </form>
        @endif

        <!-- Barra Flotante Inferior en Celular (Acceso Rápido a Guardar) -->
        @if($session && $session->isOpen())
            <div class="sm:hidden fixed bottom-3 left-3 right-3 z-40 p-2.5 rounded-2xl bg-black/95 border border-zinc-800 shadow-2xl backdrop-blur-md flex items-center justify-between gap-2.5">
                <div class="flex flex-col min-w-0">
                    <span class="text-[9px] text-zinc-400 uppercase tracking-wider font-semibold">Total {{ $selectedBar }}:</span>
                    <span class="text-sm font-black font-mono text-[#F5B81C] truncate" id="mobile-sticky-grand-total">
                        Bs. {{ number_format($grandTotalBar, 2) }}
                    </span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span id="sales-autosave-mobile-status" class="text-[10px] font-mono text-zinc-500"></span>
                    <button type="submit" form="sales-form" class="px-4 py-2 rounded-xl bg-[#F5B81C] text-black font-bold text-xs hover:bg-[#e5ac18] transition-all flex items-center gap-1.5 cursor-pointer shadow-md">
                        <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Guardar</span>
                    </button>
                </div>
            </div>
        @endif

        <!-- Script Reactivo para Combos, Deducción Automática y Mixers Especiales -->
        <script>
        const barQrTotal = {{ (float)$barTotalQr }};
        const barCardNetTotal = {{ (float)$barTotalCardNet }};
        let currentActiveRowId = null;


        function openSpecialModal(rowId) {
            currentActiveRowId = rowId;
            const row = document.querySelector(`.liquor-row[data-row-id="${rowId}"]`) || document.querySelector(`.tienda-row[data-row-id="${rowId}"]`);
            if (!row) return;

            const liquorName = row.getAttribute('data-product-name');
            const defaultMixerName = row.getAttribute('data-mixer-name');

            let totalCombos = 0;
            const tiendaInput = document.getElementById(`tienda-vendido-${rowId}`);
            if (tiendaInput) {
                totalCombos = Math.max(0, parseInt(tiendaInput.value) || 0);
            } else {
                const unitsPerPkg = parseInt(row.getAttribute('data-units-per-pkg')) || 1;
                const pkg = parseInt(row.querySelector('.input-packages')?.value) || 0;
                const units = parseInt(row.querySelector('.input-units')?.value) || 0;
                const saldo = parseInt(row.querySelector('.input-saldo')?.value) || 0;
                totalCombos = Math.max(0, (pkg * unitsPerPkg + units) - saldo);
            }

            document.getElementById('modal-liquor-title').textContent = liquorName;
            document.getElementById('modal-total-combos').textContent = totalCombos;
            document.getElementById('modal-default-mixer-name').textContent = defaultMixerName;

            // Cargar valores actuales del input JSON
            const inputJson = document.getElementById(`input-specials-${rowId}`);
            let currentSpecials = {};
            try {
                currentSpecials = JSON.parse(inputJson.value || '{}');
            } catch(e) {
                currentSpecials = {};
            }

            // Llenar inputs del modal
            document.querySelectorAll('.modal-special-qty').forEach(inp => {
                const mId = inp.getAttribute('data-mixer-id');
                inp.value = currentSpecials[mId] || 0;
            });

            updateModalRemaining(totalCombos);
            document.getElementById('modal-assign-special').classList.remove('hidden');
        }

        function updateModalRemaining(totalCombos) {
            let specialSum = 0;
            document.querySelectorAll('.modal-special-qty').forEach(inp => {
                specialSum += parseInt(inp.value) || 0;
            });
            const remaining = Math.max(0, totalCombos - specialSum);
            const remEl = document.getElementById('modal-remaining-default');
            remEl.textContent = remaining;
            if (specialSum > totalCombos) {
                remEl.className = 'font-bold text-rose-400 text-sm font-mono';
                remEl.textContent = `${remaining} (¡Excede los combos!)`;
            } else {
                remEl.className = 'font-bold text-[#F5B81C] text-sm font-mono';
            }
        }

        document.querySelectorAll('.modal-special-qty').forEach(inp => {
            inp.addEventListener('input', function() {
                const totalCombos = parseInt(document.getElementById('modal-total-combos').textContent) || 0;
                updateModalRemaining(totalCombos);
            });
        });

        function closeSpecialModal() {
            document.getElementById('modal-assign-special').classList.add('hidden');
            currentActiveRowId = null;
        }

        function saveSpecialModal() {
            if (!currentActiveRowId) return;

            const rowId = currentActiveRowId;
            const inputJson = document.getElementById(`input-specials-${rowId}`);
            const badgeEl = document.getElementById(`badge-special-${rowId}`);
            const btnEl = document.getElementById(`btn-special-${rowId}`);

            let specialsObj = {};
            let badgeParts = [];

            document.querySelectorAll('.modal-special-qty').forEach(inp => {
                const qty = parseInt(inp.value) || 0;
                const mId = inp.getAttribute('data-mixer-id');
                const mName = inp.getAttribute('data-mixer-name');
                if (qty > 0) {
                    specialsObj[mId] = qty;
                    const shortName = mName.split(' ')[0];
                    badgeParts.push(`${shortName} (${qty})`);
                }
            });

            inputJson.value = JSON.stringify(specialsObj);

            if (badgeParts.length > 0) {
                badgeEl.textContent = badgeParts.join(', ');
                btnEl.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1 cursor-pointer border bg-zinc-900 text-[#F5B81C] border-[#F5B81C]/50';
            } else {
                badgeEl.textContent = '+ Especial';
                btnEl.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1 cursor-pointer border bg-zinc-950 text-zinc-400 border-zinc-800 hover:text-white';
            }

            closeSpecialModal();
            if (typeof window.recalculateSales === 'function') window.recalculateSales();
            if (typeof window.recalculateTiendaTable === 'function') window.recalculateTiendaTable();
            window.queueSalesAutosave?.();
        }

        document.addEventListener('DOMContentLoaded', function () {

            const liquorRows = document.querySelectorAll('.liquor-row');
            const mixerRows = document.querySelectorAll('.mixer-row');
            const salesForm = document.getElementById('sales-form');
            const autosaveStatus = document.getElementById('sales-autosave-status');
            const mobileAutosaveStatus = document.getElementById('sales-autosave-mobile-status');
            let autosaveTimer = null;
            let autosaveInFlight = false;
            let autosaveQueued = false;

            function setAutosaveStatus(message, className) {
                if (autosaveStatus) {
                    autosaveStatus.textContent = message;
                    autosaveStatus.className = `text-[10px] font-mono ${className}`;
                }
                if (mobileAutosaveStatus) {
                    mobileAutosaveStatus.textContent = message;
                    mobileAutosaveStatus.className = `text-[10px] font-mono ${className}`;
                }
                const tiendaAutosave = document.getElementById('tienda-autosave-indicator');
                if (tiendaAutosave) {
                    tiendaAutosave.textContent = message === 'Guardado' ? '✓ Guardado' : message;
                    tiendaAutosave.className = `text-[10px] font-mono ${className}`;
                }
            }

            async function autosaveSales() {
                if (!salesForm) return;
                if (autosaveInFlight) {
                    autosaveQueued = true;
                    return;
                }

                autosaveInFlight = true;
                setAutosaveStatus('Guardando...', 'text-[#F5B81C]');
                try {
                    const response = await fetch(salesForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: new FormData(salesForm)
                    });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    setAutosaveStatus('Guardado', 'text-emerald-400');
                } catch (error) {
                    console.error('Error en autoguardado de ventas:', error);
                    setAutosaveStatus('Error al guardar', 'text-rose-400');
                } finally {
                    autosaveInFlight = false;
                    if (autosaveQueued) {
                        autosaveQueued = false;
                        queueAutosave();
                    }
                }
            }

            function queueAutosave() {
                if (!salesForm) return;
                clearTimeout(autosaveTimer);
                setAutosaveStatus('Pendiente...', 'text-zinc-400');
                autosaveTimer = setTimeout(autosaveSales, 450);
            }

            window.queueSalesAutosave = queueAutosave;

            const tiendaRows = document.querySelectorAll('.tienda-row');
            if (tiendaRows.length > 0) {
                window.recalculateTiendaTable(true, false);
            }

            if (!liquorRows.length && !mixerRows.length) return;

            window.recalculateSales = function() {
                let combosPerMixer = {};
                let totalLiquorCombos = 0;
                let subtotalLiquors = 0;

                liquorRows.forEach(row => {
                    const rowId = row.getAttribute('data-row-id');
                    const unitsPerPkg = parseInt(row.getAttribute('data-units-per-pkg')) || 1;
                    const pkg = parseInt(row.querySelector('.input-packages').value) || 0;
                    const units = parseInt(row.querySelector('.input-units').value) || 0;
                    const saldo = parseInt(row.querySelector('.input-saldo').value) || 0;
                    const price = parseFloat(row.querySelector('.input-price').value) || 0;
                    const defaultMixerId = parseInt(row.getAttribute('data-mixer-id')) || 0;
                    const mixerRatio = parseInt(row.getAttribute('data-mixer-ratio')) || 1;

                    const totalInitial = (pkg * unitsPerPkg) + units;
                    const vendido = Math.max(0, totalInitial - saldo);
                    const subtotal = vendido * price;

                    const cellTotIni = row.querySelector('.cell-total-initial');
                    if (cellTotIni) cellTotIni.textContent = totalInitial;

                    const cellVend = row.querySelector('.cell-vendido');
                    if (cellVend) cellVend.textContent = vendido;

                    const cellSub = row.querySelector('.cell-subtotal');
                    if (cellSub) cellSub.textContent = 'Bs. ' + subtotal.toFixed(2);

                    totalLiquorCombos += vendido;
                    subtotalLiquors += subtotal;

                    // Parsear especiales asignados a esta fila
                    const specialInput = document.getElementById(`input-specials-${rowId}`);
                    let specials = {};
                    try {
                        specials = JSON.parse(specialInput.value || '{}');
                    } catch(e) {
                        specials = {};
                    }

                    let specialCount = 0;
                    for (const [mId, qty] of Object.entries(specials)) {
                        const q = parseInt(qty) || 0;
                        if (q > 0) {
                            combosPerMixer[mId] = (combosPerMixer[mId] || 0) + q;
                            specialCount += q;
                        }
                    }

                    // El mixer por defecto recibe el remanente
                    if (defaultMixerId > 0) {
                        const remaining = Math.max(0, vendido - specialCount);
                        combosPerMixer[defaultMixerId] = (combosPerMixer[defaultMixerId] || 0) + (remaining * mixerRatio);
                    }
                });

                let totalMixerConsumed = 0;
                let totalMixerExtras = 0;
                let subtotalMixers = 0;

                mixerRows.forEach(row => {
                    const unitsPerPkg = parseInt(row.getAttribute('data-units-per-pkg')) || 1;
                    const pkg = parseInt(row.querySelector('.input-packages').value) || 0;
                    const units = parseInt(row.querySelector('.input-units').value) || 0;
                    const saldo = parseInt(row.querySelector('.input-saldo').value) || 0;
                    const price = parseFloat(row.querySelector('.input-price').value) || 0;
                    const productId = parseInt(row.getAttribute('data-product-id')) || 0;

                    const totalInitial = (pkg * unitsPerPkg) + units;
                    const consumido = Math.max(0, totalInitial - saldo);
                    const included = combosPerMixer[productId] || 0;
                    const extras = Math.max(0, consumido - included);
                    const subtotal = extras * price;

                    const cellTotIni = row.querySelector('.cell-total-initial');
                    if (cellTotIni) cellTotIni.textContent = totalInitial;

                    const cellCons = row.querySelector('.cell-consumido');
                    if (cellCons) cellCons.textContent = consumido;

                    const cellInc = row.querySelector('.cell-included');
                    if (cellInc) cellInc.textContent = included;

                    const cellExt = row.querySelector('.cell-extras');
                    if (cellExt) cellExt.textContent = extras;

                    const cellSub = row.querySelector('.cell-subtotal');
                    if (cellSub) cellSub.textContent = 'Bs. ' + subtotal.toFixed(2);

                    totalMixerConsumed += consumido;
                    totalMixerExtras += extras;
                    subtotalMixers += subtotal;
                });

                const storeSalesTotalEl = document.getElementById('store-sales-total-val');
                const storeSalesTotal = storeSalesTotalEl ? parseFloat(storeSalesTotalEl.value || 0) : 0;
                const grandTotal = subtotalLiquors + subtotalMixers + storeSalesTotal;
                const cashRemaining = Math.max(0, grandTotal - barQrTotal - barCardNetTotal);

                const headerCombos = document.getElementById('header-total-combos');
                if (headerCombos) headerCombos.textContent = `${totalLiquorCombos} combos`;

                const headerSubtotalCombosVal = document.getElementById('header-subtotal-combos-val');
                if (headerSubtotalCombosVal) headerSubtotalCombosVal.textContent = subtotalLiquors.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const badgeLiquorSubtotal = document.getElementById('badge-liquor-subtotal');
                if (badgeLiquorSubtotal) badgeLiquorSubtotal.textContent = 'Subtotal: Bs. ' + subtotalLiquors.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const tfootLiquorVendido = document.getElementById('tfoot-liquor-vendido');
                if (tfootLiquorVendido) tfootLiquorVendido.textContent = totalLiquorCombos;

                const tfootLiquorSubtotal = document.getElementById('tfoot-liquor-subtotal');
                if (tfootLiquorSubtotal) tfootLiquorSubtotal.textContent = 'Bs. ' + subtotalLiquors.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const headerExtras = document.getElementById('header-total-extras');
                if (headerExtras) headerExtras.textContent = `${totalMixerExtras} extras`;

                const headerSubtotalExtrasVal = document.getElementById('header-subtotal-extras-val');
                if (headerSubtotalExtrasVal) headerSubtotalExtrasVal.textContent = subtotalMixers.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const badgeMixerSubtotal = document.getElementById('badge-mixer-subtotal');
                if (badgeMixerSubtotal) badgeMixerSubtotal.textContent = 'Subtotal Extras: Bs. ' + subtotalMixers.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const tfootMixerConsumido = document.getElementById('tfoot-mixer-consumido');
                if (tfootMixerConsumido) tfootMixerConsumido.textContent = totalMixerConsumed;

                const tfootMixerExtras = document.getElementById('tfoot-mixer-extras');
                if (tfootMixerExtras) tfootMixerExtras.textContent = totalMixerExtras;

                const tfootMixerSubtotal = document.getElementById('tfoot-mixer-subtotal');
                if (tfootMixerSubtotal) tfootMixerSubtotal.textContent = 'Bs. ' + subtotalMixers.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const headerGrandTotal = document.getElementById('header-grand-total');
                if (headerGrandTotal) headerGrandTotal.innerHTML = `<span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>${grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                const headerBarCash = document.getElementById('header-bar-cash');
                if (headerBarCash) headerBarCash.textContent = cashRemaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const footerGrandTotal = document.getElementById('footer-grand-total');
                if (footerGrandTotal) footerGrandTotal.textContent = 'Bs. ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const mobileStickyGrandTotal = document.getElementById('mobile-sticky-grand-total');
                if (mobileStickyGrandTotal) mobileStickyGrandTotal.textContent = 'Bs. ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };

            /* ========================================================
               MOTOR DE NAVEGACIÓN TIPO EXCEL
               ======================================================== */
            function initExcelNavigation(form) {
                if (!form) return;

                function getMatrix(currentTable) {
                    const table = currentTable || form;
                    const visibleRows = Array.from(table.querySelectorAll('.liquor-row, .mixer-row')).filter(tr => tr.style.display !== 'none');
                    return visibleRows.map(tr => {
                        const inputs = Array.from(tr.querySelectorAll('input[data-excel-col]')).filter(inp => !inp.disabled);
                        inputs.sort((a, b) => parseInt(a.dataset.excelCol) - parseInt(b.dataset.excelCol));
                        return { tr, inputs };
                    });
                }

                form.addEventListener('focusin', function (e) {
                    const input = e.target;
                    if (!input.matches('input[data-excel-col]')) return;

                    setTimeout(() => {
                        if (document.activeElement === input) {
                            try { input.select(); } catch (err) {}
                        }
                    }, 25);

                    form.querySelectorAll('.liquor-row, .mixer-row').forEach(r => r.classList.remove('excel-active-row'));
                    const tr = input.closest('tr');
                    if (tr) tr.classList.add('excel-active-row');
                });

                form.addEventListener('focusout', function (e) {
                    const input = e.target;
                    if (!input.matches('input[data-excel-col]')) return;
                    const tr = input.closest('tr');
                    if (tr && !tr.contains(document.activeElement)) {
                        tr.classList.remove('excel-active-row');
                    }
                });

                form.addEventListener('keydown', function (e) {
                    const input = e.target;
                    if (!input.matches('input[data-excel-col]')) return;

                    const key = e.key;
                    if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter', 'Tab'].includes(key)) {
                        return;
                    }

                    const currentTable = input.closest('table');
                    const matrix = getMatrix(currentTable);
                    const currentTr = input.closest('tr');
                    const rowIndex = matrix.findIndex(item => item.tr === currentTr);
                    if (rowIndex === -1) return;

                    const rowInputs = matrix[rowIndex].inputs;
                    const colIndex = rowInputs.indexOf(input);
                    if (colIndex === -1) return;

                    let targetInput = null;

                    if (key === 'ArrowDown') {
                        e.preventDefault();
                        if (rowIndex + 1 < matrix.length) {
                            const nextRowInputs = matrix[rowIndex + 1].inputs;
                            targetInput = nextRowInputs[colIndex] || nextRowInputs[nextRowInputs.length - 1];
                        }
                    } else if (key === 'ArrowUp') {
                        e.preventDefault();
                        if (rowIndex - 1 >= 0) {
                            const prevRowInputs = matrix[rowIndex - 1].inputs;
                            targetInput = prevRowInputs[colIndex] || prevRowInputs[prevRowInputs.length - 1];
                        }
                    } else if (key === 'Enter') {
                        e.preventDefault();
                        if (e.shiftKey) {
                            if (rowIndex - 1 >= 0) {
                                const prevRowInputs = matrix[rowIndex - 1].inputs;
                                targetInput = prevRowInputs[colIndex] || prevRowInputs[prevRowInputs.length - 1];
                            }
                        } else {
                            if (rowIndex + 1 < matrix.length) {
                                const nextRowInputs = matrix[rowIndex + 1].inputs;
                                targetInput = nextRowInputs[colIndex] || nextRowInputs[nextRowInputs.length - 1];
                            }
                        }
                    } else if (key === 'ArrowRight' || (key === 'Tab' && !e.shiftKey)) {
                        e.preventDefault();
                        if (colIndex + 1 < rowInputs.length) {
                            targetInput = rowInputs[colIndex + 1];
                        } else if (rowIndex + 1 < matrix.length) {
                            targetInput = matrix[rowIndex + 1].inputs[0];
                        }
                    } else if (key === 'ArrowLeft' || (key === 'Tab' && e.shiftKey)) {
                        e.preventDefault();
                        if (colIndex - 1 >= 0) {
                            targetInput = rowInputs[colIndex - 1];
                        } else if (rowIndex - 1 >= 0) {
                            const prevRowInputs = matrix[rowIndex - 1].inputs;
                            targetInput = prevRowInputs[prevRowInputs.length - 1];
                        }
                    }

                    if (targetInput) {
                        targetInput.focus();
                        try { targetInput.select(); } catch (err) {}
                    }
                });
            }

            if (salesForm) initExcelNavigation(salesForm);

            document.querySelectorAll('#sales-form input').forEach(input => {
                input.addEventListener('input', window.recalculateSales);
                if (input.type === 'number') input.addEventListener('change', queueAutosave);
            });

            // Recalcular inmediatamente al cargar la página
            if (typeof window.recalculateSales === 'function') {
                window.recalculateSales();
            }
        });

        /* ========================================================
           CONTROL DE VENTAS DIRECTAS TIENDA (FORMATO TABULAR SIMPLIFICADO)
           ======================================================== */
        let tiendaActiveCategory = 'todas';

        window.handleTiendaVendidoInput = function(input, rowId) {
            let val = input.value.replace(/[^0-9]/g, '');
            // Si el valor comienza con 0 y tiene más dígitos (ej: "05"), quitar ceros a la izquierda
            if (val.length > 1 && val.startsWith('0')) {
                val = val.replace(/^0+/, '') || '0';
            }
            input.value = val;
            window.onTiendaRowChange(rowId);
        };

        window.handleTiendaKeyDown = function(event, currentInput) {
            if (event.key === 'Enter' || event.key === 'ArrowDown') {
                event.preventDefault();
                navigateTiendaRow(currentInput, 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                navigateTiendaRow(currentInput, -1);
            }
        };

        function navigateTiendaRow(currentInput, direction) {
            const allVisibleRows = Array.from(document.querySelectorAll('.tienda-row')).filter(r => !r.classList.contains('hidden'));
            const currentRow = currentInput.closest('.tienda-row');
            const currentIndex = allVisibleRows.indexOf(currentRow);
            if (currentIndex === -1) return;

            const nextIndex = currentIndex + direction;
            if (nextIndex >= 0 && nextIndex < allVisibleRows.length) {
                const targetInput = allVisibleRows[nextIndex].querySelector('.tienda-input-vendido');
                if (targetInput) {
                    targetInput.focus();
                    targetInput.select();
                }
            }
        }

        window.stepTiendaRowQty = function(rowId, delta) {
            const input = document.getElementById(`tienda-vendido-${rowId}`);
            if (!input) return;
            let current = parseInt(input.value) || 0;
            current = Math.max(0, current + delta);
            input.value = current;
            window.onTiendaRowChange(rowId);
        };

        window.onTiendaRowChange = function(rowId) {
            const row = document.querySelector(`.tienda-row[data-row-id="${rowId}"]`);
            if (!row) return;

            const isMixer = row.getAttribute('data-is-mixer') === '1';
            const inputVendido = document.getElementById(`tienda-vendido-${rowId}`);
            const inputPrice = document.getElementById(`tienda-price-${rowId}`);
            const inputSubtotal = document.getElementById(`tienda-subtotal-${rowId}`);

            if (isMixer) {
                const currentIncluded = parseInt(row.getAttribute('data-included')) || 0;
                const enteredVal = Math.max(0, parseInt(inputVendido ? inputVendido.value : 0) || 0);
                const extra = Math.max(0, enteredVal - currentIncluded);
                row.setAttribute('data-manual-extra', extra);
                window.recalculateTiendaTable(true, true);
                return;
            }

            const qty = Math.max(0, parseInt(inputVendido ? inputVendido.value : 0) || 0);
            const price = Math.max(0, parseFloat(inputPrice ? inputPrice.value : 0) || 0);
            const subtotal = qty * price;

            if (inputSubtotal) inputSubtotal.value = subtotal.toFixed(2);
            window.recalculateTiendaTable(true, true);
        };

        window.onTiendaManualSubtotalChange = function(rowId) {
            window.recalculateTiendaTable(false, true);
        };

        window.recalculateTiendaTable = function(autoUpdateMixers = true, triggerAutosave = true) {
            // 1. Calcular mixers requeridos por los combos vendidos de licores (incluyendo especiales)
            const combosPerMixer = {};

            document.querySelectorAll('.tienda-row[data-is-mixer="0"]').forEach(row => {
                const rowId = row.getAttribute('data-row-id');
                const inputVendido = document.getElementById(`tienda-vendido-${rowId}`);
                const vendido = Math.max(0, parseInt(inputVendido ? inputVendido.value : 0) || 0);
                const defaultMixerId = parseInt(row.getAttribute('data-mixer-id')) || 0;
                const mixerRatio = parseInt(row.getAttribute('data-mixer-ratio')) || 1;

                // Parsear especiales asignados a esta fila
                const specialInput = document.getElementById(`input-specials-${rowId}`);
                let specials = {};
                try {
                    specials = JSON.parse(specialInput ? specialInput.value : '{}') || {};
                } catch(e) {
                    specials = {};
                }

                let specialCount = 0;
                for (const [mId, qty] of Object.entries(specials)) {
                    const q = parseInt(qty) || 0;
                    if (q > 0) {
                        const mIdInt = parseInt(mId);
                        combosPerMixer[mIdInt] = (combosPerMixer[mIdInt] || 0) + q;
                        specialCount += q;
                    }
                }

                // El mixer por defecto recibe el remanente
                if (defaultMixerId > 0) {
                    const remaining = Math.max(0, vendido - specialCount);
                    combosPerMixer[defaultMixerId] = (combosPerMixer[defaultMixerId] || 0) + (remaining * mixerRatio);
                }
            });

            // 2. Actualizar mixers de Tienda
            document.querySelectorAll('.tienda-row[data-is-mixer="1"]').forEach(row => {
                const rowId = row.getAttribute('data-row-id');
                const productId = parseInt(row.getAttribute('data-product-id')) || 0;
                const inputVendido = document.getElementById(`tienda-vendido-${rowId}`);
                const inputPrice = document.getElementById(`tienda-price-${rowId}`);
                const inputSubtotal = document.getElementById(`tienda-subtotal-${rowId}`);
                const badgeIncluded = document.getElementById(`tienda-mixer-included-${rowId}`);

                const included = combosPerMixer[productId] || 0;
                row.setAttribute('data-included', included);

                const extra = Math.max(0, parseInt(row.getAttribute('data-manual-extra')) || 0);
                const totalSold = included + extra;
                const price = Math.max(0, parseFloat(inputPrice ? inputPrice.value : 0) || 0);

                if (autoUpdateMixers && inputVendido) {
                    inputVendido.value = totalSold;
                }

                if (autoUpdateMixers && inputSubtotal) {
                    inputSubtotal.value = (extra * price).toFixed(2);
                }

                if (badgeIncluded) {
                    if (included > 0) {
                        badgeIncluded.textContent = `${included} en combos`;
                        badgeIncluded.classList.remove('hidden');
                    } else {
                        badgeIncluded.classList.add('hidden');
                    }
                }
            });

            // 3. Sumar totales generales
            let totalUnits = 0;
            let totalSubtotal = 0;

            document.querySelectorAll('.tienda-row').forEach(row => {
                const rowId = row.getAttribute('data-row-id');
                const inputVendido = document.getElementById(`tienda-vendido-${rowId}`);
                const inputSubtotal = document.getElementById(`tienda-subtotal-${rowId}`);

                if (inputVendido && inputSubtotal) {
                    const units = Math.max(0, parseInt(inputVendido.value) || 0);
                    const sub = Math.max(0, parseFloat(inputSubtotal.value) || 0);
                    totalUnits += units;
                    totalSubtotal += sub;
                }
            });

            const tfootUnits = document.getElementById('tfoot-tienda-units');
            const tfootSubtotal = document.getElementById('tfoot-tienda-subtotal');
            const badgeGrandTotal = document.getElementById('badge-tienda-grand-total');
            const footerGrandTotal = document.getElementById('footer-tienda-grand-total');
            const headerGrandTotal = document.getElementById('header-grand-total');
            const headerCombos = document.getElementById('header-tienda-units');
            const mobileStickyTotal = document.getElementById('mobile-sticky-grand-total');

            const formattedTotal = 'Bs. ' + totalSubtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            if (tfootUnits) tfootUnits.textContent = totalUnits;
            if (tfootSubtotal) tfootSubtotal.textContent = formattedTotal;
            if (badgeGrandTotal) badgeGrandTotal.textContent = formattedTotal;
            if (footerGrandTotal) footerGrandTotal.textContent = formattedTotal;
            if (headerGrandTotal) headerGrandTotal.innerHTML = `<span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>${totalSubtotal.toFixed(2)}`;
            if (headerCombos) headerCombos.textContent = `${totalUnits} bebidas`;
            if (mobileStickyTotal) mobileStickyTotal.textContent = formattedTotal;

            if (triggerAutosave && typeof window.queueSalesAutosave === 'function') {
                window.queueSalesAutosave();
            }
        };

        window.setTiendaTableCategory = function(cat, btn) {
            tiendaActiveCategory = cat;
            document.querySelectorAll('.tienda-pill-btn').forEach(b => {
                b.className = 'tienda-pill-btn px-3 py-1.5 rounded-xl font-semibold text-xs cursor-pointer transition-all bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-white hover:border-zinc-700 shrink-0';
            });
            if (btn) {
                btn.className = 'tienda-pill-btn px-3 py-1.5 rounded-xl font-bold text-xs cursor-pointer transition-all bg-[#F5B81C] text-black shadow-sm shrink-0';
            }
            window.filterTiendaTable();
        };

        window.filterTiendaTable = function() {
            const term = (document.getElementById('tienda-table-search')?.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.tienda-row');
            let visibleIndex = 1;
            let visibleCount = 0;

            rows.forEach(row => {
                const cat = row.getAttribute('data-category-key') || '';
                const name = row.getAttribute('data-name') || '';

                const matchesCat = (tiendaActiveCategory === 'todas') || (cat === tiendaActiveCategory);
                const matchesTerm = !term || name.includes(term);

                if (matchesCat && matchesTerm) {
                    row.classList.remove('hidden');
                    const numCol = row.querySelector('.col-num');
                    if (numCol) numCol.textContent = visibleIndex++;
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            const emptyTr = document.getElementById('tienda-empty-filter-row');
            if (emptyTr) {
                if (visibleCount === 0) emptyTr.classList.remove('hidden');
                else emptyTr.classList.add('hidden');
            }
        };
        </script>

    @endif

</div>
@endsection
