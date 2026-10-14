@extends('layouts.app')

@section('title', 'Bodega Central — Control de Bebidas')

@section('content')

@php
    $totalBottles   = $products->sum('stock_warehouse');
    $totalValuation = $products->sum(fn($p) => $p->stock_warehouse * $p->sale_price);
    $lowStockCount  = $products->filter(fn($p) => $p->stock_warehouse <= 10 && $p->is_active)->count();

    if (!function_exists('getDrinkCategory')) {
        function getDrinkCategory(string $name, string $cat): string {
            $n = mb_strtolower($name);
            if (str_contains($n, 'singani')) return 'Singani';
            if (str_contains($n, 'whisky') || str_contains($n, 'whiskey') || str_contains($n, 'jack daniel')) return 'Whisky';
            if (str_contains($n, 'ron') || str_contains($n, 'abuelo') || str_contains($n, 'havana') || str_contains($n, 'fernet')) return 'Ron';
            if (str_contains($n, 'gin') || str_contains($n, 'tanqueray') || str_contains($n, 'beefeater') || str_contains($n, 'dharma') || str_contains($n, 'ganesha') || str_contains($n, 'james cook')) return 'Gin';
            if (str_contains($n, 'tequila') || str_contains($n, 'jager') || str_contains($n, 'cuervo') || str_contains($n, 'olmeca')) return 'Tequila';
            if ($cat === 'Mixers' || str_contains($n, 'coca') || str_contains($n, 'ginger') || str_contains($n, 'agua') || str_contains($n, 'sprite') || str_contains($n, 'aquarius') || str_contains($n, 'tónica') || str_contains($n, 'tonica')) return 'Mixer';
            if ($cat === 'Cervezas' || str_contains($n, 'cerveza') || str_contains($n, 'huari') || str_contains($n, 'paceña')) return 'Cerveza';
            return 'Otro';
        }
    }
@endphp

<style>
    /* Eliminar flechas blancas nativas de los inputs de número */
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

<div class="space-y-4 max-w-7xl mx-auto w-full pb-10" id="bodega-app">

    <!-- ========================================================
         1. CABECERA: TÍTULO, SELECTOR DE VISTA & NUEVO PRODUCTO
         ======================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-3.5 rounded-2xl theme-card border theme-border shadow-sm">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white font-sans">
                Bodega Central
            </h1>
            <p class="text-xs text-zinc-400 mt-0.5">
                Control de botellas y paquetes en almacén
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Selector de Vista (Tarjetas por defecto vs Tabla) -->
            <div class="inline-flex items-center p-1 rounded-xl bg-zinc-900 border border-zinc-800">
                <button type="button" 
                        id="view-mode-cards-btn"
                        onclick="switchViewMode('cards')"
                        title="Ver en Tarjetas"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer bg-[#F5B81C] text-black shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <rect width="7" height="7" x="3" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="14" rx="1"/>
                        <rect width="7" height="7" x="3" y="14" rx="1"/>
                    </svg>
                    <span>Tarjetas</span>
                </button>
                <button type="button" 
                        id="view-mode-table-btn"
                        onclick="switchViewMode('table')"
                        title="Ver en Tabla"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer text-zinc-400 hover:text-white">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <line x1="8" y1="6" x2="21" y2="6"/>
                        <line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/>
                        <line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/>
                        <line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                    <span>Tabla</span>
                </button>
            </div>

            <a href="{{ route('sales.index', ['bar' => 'Tienda']) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white border border-zinc-800 hover:border-zinc-700 text-xs font-bold rounded-xl transition-all cursor-pointer">
                <svg class="w-4 h-4 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Ventas Tienda (Sueltas)</span>
            </a>

            <button type="button" 
                    onclick="openCreateDrawer()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#F5B81C] hover:bg-[#e5ac18] text-zinc-950 text-xs font-black rounded-xl transition-all shadow-md shadow-[#F5B81C]/10 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Registrar Bebida</span>
            </button>
        </div>
    </div>

    <!-- ========================================================
         2. 4 TARJETAS KPI (ESTILO TÍO CHU / DILEMO)
         ======================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        
        <!-- KPI 1: Total Botellas en Bodega -->
        <div class="p-4 rounded-2xl theme-card border theme-border flex items-center justify-between shadow-sm">
            <div class="min-w-0">
                <span class="text-[11px] font-black uppercase tracking-wider theme-text-secondary block truncate">
                    Total en Bodega
                </span>
                <div class="text-2xl font-black theme-text-primary tracking-tight mt-0.5 font-sans truncate" id="kpi-total-bottles">
                    {{ number_format($totalBottles) }} <span class="text-xs font-normal text-zinc-400">botellas</span>
                </div>
                <span class="text-[10px] font-bold theme-text-secondary block mt-0.5">En depósito central</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-[#F5B81C] shrink-0 ml-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M10 2h4M10 2v3a2 2 0 0 1-.5 1.3L8 8v12a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V8l-1.5-1.7A2 2 0 0 1 14 5V2"/>
                </svg>
            </div>
        </div>

        <!-- KPI 2: Plata en Mercadería -->
        <div class="p-4 rounded-2xl theme-card border theme-border flex items-center justify-between shadow-sm">
            <div class="min-w-0">
                <span class="text-[11px] font-black uppercase tracking-wider theme-text-secondary block truncate">
                    Plata en Mercadería
                </span>
                <div class="text-2xl font-black theme-text-primary tracking-tight mt-0.5 font-sans truncate" id="kpi-valuation">
                    <span class="text-sm text-[#F5B81C] mr-0.5 font-bold">Bs.</span>{{ number_format($totalValuation, 2) }}
                </div>
                <span class="text-[10px] font-bold theme-text-secondary block mt-0.5">Precio de venta en barras</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-emerald-400 shrink-0 ml-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect width="18" height="18" x="3" y="3" rx="3"/>
                    <circle cx="12" cy="12" r="3"/>
                    <path d="m14.5 9.5-5 5"/>
                </svg>
            </div>
        </div>

        <!-- KPI 3: Variedad de Bebidas -->
        <div class="p-4 rounded-2xl theme-card border theme-border flex items-center justify-between shadow-sm">
            <div class="min-w-0">
                <span class="text-[11px] font-black uppercase tracking-wider theme-text-secondary block truncate">
                    Variedad de Bebidas
                </span>
                <div class="text-2xl font-black theme-text-primary tracking-tight mt-0.5 font-sans truncate">
                    {{ $products->where('is_active', true)->count() }} <span class="text-xs font-normal text-zinc-400">marcas</span>
                </div>
                <span class="text-[10px] font-bold theme-text-secondary block mt-0.5">Bebidas activas en carta</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-blue-400 shrink-0 ml-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="m16.5 9.4-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
            </div>
        </div>

        <!-- KPI 4: Bebidas por Agotarse -->
        <div class="p-4 rounded-2xl theme-card border {{ $lowStockCount > 0 ? 'border-amber-500/40' : 'border-zinc-800' }} flex items-center justify-between shadow-sm">
            <div class="min-w-0">
                <span class="text-[11px] font-black uppercase tracking-wider {{ $lowStockCount > 0 ? 'text-amber-400' : 'theme-text-secondary' }} block truncate">
                    Bebidas por Agotarse
                </span>
                <div class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-amber-400' : 'text-zinc-400' }} tracking-tight mt-0.5 font-sans truncate" id="kpi-low-stock">
                    {{ $lowStockCount }} <span class="text-xs font-normal text-zinc-400">alertas</span>
                </div>
                <span class="text-[10px] font-bold theme-text-secondary block mt-0.5">
                    {{ $lowStockCount > 0 ? 'Con 10 o menos botellas' : 'Stock suficiente en todo' }}
                </span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center {{ $lowStockCount > 0 ? 'text-amber-400' : 'text-zinc-500' }} shrink-0 ml-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
        </div>

    </div>

    <!-- ========================================================
         3. FILTROS POR CATEGORÍA & BUSCADOR DIRECTO
         ======================================================== -->
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 p-2.5 rounded-2xl theme-card border theme-border shadow-sm">
        
        <!-- Píldoras de Categoría -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs py-0.5" id="category-pills">
            @php
            $cats = [
                'all'     => 'Todas (' . $products->count() . ')',
                'Singani' => 'Singanis',
                'Ron'     => 'Fernet & Rones',
                'Whisky'  => 'Whiskys',
                'Gin'     => 'Gins',
                'Tequila' => 'Tequilas',
                'Mixer'   => 'Gaseosas & Aguas',
                'Cerveza' => 'Cervezas',
                'Otro'    => 'Otros',
            ];
            @endphp
            @foreach($cats as $key => $label)
                <button type="button" 
                        data-cat="{{ $key }}"
                        onclick="filterCat('{{ $key }}', this)"
                        class="category-btn px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap {{ $key === 'all' ? 'bg-[#F5B81C] text-black shadow-sm' : 'text-zinc-400 hover:text-white hover:bg-zinc-900 border border-transparent' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Buscador Directo -->
        <div class="relative w-full md:w-64 shrink-0">
            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-zinc-500">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </span>
            <input type="text" 
                   id="product-search" 
                   placeholder="Buscar bebida (ej. Fernet, Gin)..." 
                   oninput="searchProducts(this.value)"
                   class="w-full pl-8 pr-3 py-1.5 text-xs bg-zinc-900 border border-zinc-800 rounded-xl text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C] transition-all font-sans">
        </div>

    </div>

    <!-- ========================================================
         4. VISTA 1: TABLA EJECUTIVA (OPCIONAL)
         ======================================================== -->
    <div id="view-table-container" class="hidden rounded-2xl theme-card border theme-border overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b theme-border bg-zinc-950/60 text-zinc-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Bebida / Marca</th>
                        <th class="py-3 px-3">Categoría</th>
                        <th class="py-3 px-3 text-right">Precio Venta</th>
                        <th class="py-3 px-4 text-center">Cajas & Sueltas</th>
                        <th class="py-3 px-4 text-center">Botellas en Bodega</th>
                        <th class="py-3 px-4 text-center">Ajuste Rápido</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap w-24">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y theme-border font-sans" id="table-products-tbody">
                    @foreach($products as $prod)
                    @php
                        $sub = getDrinkCategory($prod->name, $prod->category);
                        $stock = (int) $prod->stock_warehouse;
                        $unitsPerPackage = max(1, (int) $prod->units_per_package);
                        $stockPackages = intdiv($stock, $unitsPerPackage);
                        $stockUnits = $stock % $unitsPerPackage;
                    @endphp
                    <tr class="product-item hover:bg-zinc-900/50 transition-colors"
                        data-id="{{ $prod->id }}"
                        data-name="{{ strtolower($prod->name) }}"
                        data-subcat="{{ $sub }}"
                        data-price="{{ $prod->sale_price }}"
                        data-stock="{{ $stock }}"
                        data-units-per-package="{{ $unitsPerPackage }}">
                        
                        <!-- Columna 1: Nombre & Presentación -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-[#F5B81C] shrink-0 font-bold text-xs">
                                    {{ mb_substr($prod->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="font-black text-white text-xs block truncate">{{ $prod->name }}</span>
                                    <span class="text-[10px] text-zinc-400 font-medium block">
                                        {{ $prod->unit }} • {{ $unitsPerPackage }} unid. x caja
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- Columna 2: Categoría -->
                        <td class="py-3 px-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-900 border border-zinc-800 text-zinc-300">
                                {{ $sub }}
                            </span>
                        </td>

                        <!-- Columna 3: Precio Venta -->
                        <td class="py-3 px-3 text-right font-mono font-bold text-white text-xs">
                            <span class="text-[#F5B81C] text-[10px] mr-0.5">Bs.</span>{{ number_format($prod->sale_price, 2) }}
                        </td>

                        <!-- Columna 4: Desglose Cajas y Sueltas -->
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <div class="flex items-center gap-1 bg-zinc-900 px-2 py-1 rounded-lg border border-zinc-800">
                                    <span class="text-[10px] font-bold text-zinc-500 uppercase">Cajas:</span>
                                    <input type="number" 
                                           min="0" 
                                           value="{{ $stockPackages }}"
                                           id="stock-packages-table-{{ $prod->id }}"
                                           data-product-id="{{ $prod->id }}"
                                           onchange="onStockPartChange({{ $prod->id }}, 'table')"
                                           class="w-10 text-center font-mono font-bold text-xs bg-transparent border-0 text-white focus:outline-none focus:ring-0 p-0">
                                </div>
                                <div class="flex items-center gap-1 bg-zinc-900 px-2 py-1 rounded-lg border border-zinc-800">
                                    <span class="text-[10px] font-bold text-zinc-500 uppercase">Sueltas:</span>
                                    <input type="number" 
                                           min="0" 
                                           value="{{ $stockUnits }}"
                                           id="stock-units-table-{{ $prod->id }}"
                                           data-product-id="{{ $prod->id }}"
                                           onchange="onStockPartChange({{ $prod->id }}, 'table')"
                                           class="w-8 text-center font-mono font-bold text-xs bg-transparent border-0 text-white focus:outline-none focus:ring-0 p-0">
                                </div>
                            </div>
                        </td>

                        <!-- Columna 5: Input Directo de Botellas Totales -->
                        <td class="py-3 px-4 text-center">
                            <div class="relative inline-flex items-center justify-center">
                                <input type="number"
                                       min="0"
                                       id="stock-input-table-{{ $prod->id }}"
                                       value="{{ $stock }}"
                                       data-product-id="{{ $prod->id }}"
                                       data-sale-price="{{ $prod->sale_price }}"
                                       data-last-saved="{{ $stock }}"
                                       oninput="onStockInput(this, {{ $prod->id }})"
                                       onchange="onStockChange(this, {{ $prod->id }})"
                                       onkeydown="if(event.key==='Enter'){this.blur();event.preventDefault();}"
                                       class="stock-total-input w-24 text-center text-base font-black font-mono rounded-xl py-1 px-2 border transition-all cursor-text bg-zinc-900 border-zinc-800 focus:border-[#F5B81C] focus:outline-none {{ $stock <= 0 ? 'text-zinc-600' : ($stock <= 10 ? 'text-amber-400' : 'text-emerald-400') }}">
                                <span class="save-indicator absolute -top-2 -right-2 text-[9px] font-mono opacity-0 transition-opacity duration-300 pointer-events-none bg-zinc-950 px-1 py-0.5 rounded border border-zinc-800"></span>
                            </div>
                        </td>

                        <!-- Columna 6: Ajuste Rápido (-10, -1, +1, +10) -->
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" 
                                        onclick="stepStock({{ $prod->id }}, -10)"
                                        title="Restar 10 botellas"
                                        class="px-2 py-1 rounded-lg text-[10px] font-mono font-black bg-zinc-900 text-zinc-400 hover:text-rose-400 border border-zinc-800 hover:border-rose-500/30 transition-all active:scale-95 cursor-pointer">
                                    -10
                                </button>
                                <button type="button" 
                                        onclick="stepStock({{ $prod->id }}, -1)"
                                        title="Restar 1 botella"
                                        class="px-2 py-1 rounded-lg text-[10px] font-mono font-black bg-zinc-900 text-zinc-300 hover:text-rose-400 border border-zinc-800 hover:border-rose-500/30 transition-all active:scale-95 cursor-pointer">
                                    -1
                                </button>
                                <button type="button" 
                                        onclick="stepStock({{ $prod->id }}, 1)"
                                        title="Sumar 1 botella"
                                        class="px-2 py-1 rounded-lg text-[10px] font-mono font-black bg-zinc-900 text-zinc-300 hover:text-emerald-400 border border-zinc-800 hover:border-emerald-500/30 transition-all active:scale-95 cursor-pointer">
                                    +1
                                </button>
                                <button type="button" 
                                        onclick="stepStock({{ $prod->id }}, 10)"
                                        title="Sumar 10 botellas"
                                        class="px-2 py-1 rounded-lg text-[10px] font-mono font-black bg-zinc-900 text-zinc-300 hover:text-emerald-400 border border-zinc-800 hover:border-emerald-500/30 transition-all active:scale-95 cursor-pointer">
                                    +10
                                </button>
                            </div>
                        </td>

                        <!-- Columna 7: Acción Editar -->
                        <td class="py-3 px-4 text-right whitespace-nowrap w-24">
                            <button type="button"
                                    onclick="openEditDrawer({{ $prod->id }}, {{ json_encode($prod->name) }}, {{ json_encode($prod->category) }}, {{ json_encode($prod->unit) }}, {{ $prod->sale_price }}, {{ $prod->cost_price ?? 0 }}, {{ $prod->units_per_package }}, {{ $prod->stock_warehouse }}, {{ $prod->is_active ? 1 : 0 }}, {{ json_encode($prod->image_url) }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-900/80 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-xs font-semibold transition-all cursor-pointer shadow-sm active:scale-95">
                                <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Editar</span>
                            </button>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================
         5. VISTA 2: CUADRÍCULA DE TARJETAS (POR DEFECTO)
         ======================================================== -->
    <div id="view-cards-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
        @foreach($products as $prod)
        @php
            $sub = getDrinkCategory($prod->name, $prod->category);
            $stock = (int) $prod->stock_warehouse;
            $unitsPerPackage = max(1, (int) $prod->units_per_package);
            $stockPackages = intdiv($stock, $unitsPerPackage);
            $stockUnits = $stock % $unitsPerPackage;
        @endphp
        <div class="product-item relative rounded-2xl overflow-hidden border border-zinc-800/80 bg-[#09090b] flex flex-col justify-between shadow-lg group hover:border-zinc-700/80 transition-all duration-300"
             data-id="{{ $prod->id }}"
             data-name="{{ strtolower($prod->name) }}"
             data-subcat="{{ $sub }}"
             data-price="{{ $prod->sale_price }}"
             data-stock="{{ $stock }}"
             data-units-per-package="{{ $unitsPerPackage }}">
            
            <!-- Zona Superior: Imagen de Fondo sin Bordes & Botella -->
            <div class="relative w-full h-36 overflow-hidden flex items-center justify-center bg-black/60">
                @if($prod->image_url)
                    <!-- Fondo de imagen que cubre sin bordes con desenfoque ambiental sutil -->
                    <img src="{{ $prod->image_url }}" alt="" class="absolute inset-0 w-full h-full object-cover blur-sm opacity-25 scale-110 pointer-events-none transition-transform duration-500 group-hover:scale-125">
                    <!-- Viñeta con degradado oscuro de lujo -->
                    <div class="absolute inset-0 bg-gradient-to-t from-[#09090b] via-black/40 to-black/75"></div>
                    <!-- Botella nítida en el centro -->
                    <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" class="relative z-10 max-h-32 w-auto object-contain drop-shadow-[0_10px_16px_rgba(0,0,0,0.85)] transition-transform duration-300 group-hover:scale-105">
                @else
                    <div class="w-12 h-12 rounded-2xl bg-zinc-900/60 border border-zinc-800/60 flex items-center justify-center text-zinc-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                @endif

                <!-- Badges Superiores Flotantes -->
                <div class="absolute top-2.5 left-2.5 right-2.5 z-20 flex items-center justify-between pointer-events-none">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-black/70 backdrop-blur-md border border-white/10 text-zinc-300">
                        {{ $sub }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-black/75 backdrop-blur-md border border-white/10 text-white shadow-sm">
                        <span class="text-[#F5B81C] text-[10px] mr-1">Bs.</span>{{ number_format($prod->sale_price, 2) }}
                    </span>
                </div>

                <!-- Botón rápido flotante: Cambiar Imagen -->
                <button type="button"
                        onclick="openEditDrawer({{ $prod->id }}, {{ json_encode($prod->name) }}, {{ json_encode($prod->category) }}, {{ json_encode($prod->unit) }}, {{ $prod->sale_price }}, {{ $prod->cost_price ?? 0 }}, {{ $prod->units_per_package }}, {{ $prod->stock_warehouse }}, {{ $prod->is_active ? 1 : 0 }}, {{ json_encode($prod->image_url) }}, true)"
                        title="Cambiar foto de esta bebida"
                        class="absolute bottom-2 right-2 z-20 p-1.5 rounded-xl bg-black/70 hover:bg-[#F5B81C] hover:text-black backdrop-blur-md border border-white/15 text-zinc-300 transition-all opacity-85 hover:opacity-100 cursor-pointer shadow-md active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>

            <!-- Panel Jaspeado Inferior (Frosted Glass / Textura con Opciones) -->
            <div class="relative z-10 p-3 rounded-b-2xl bg-zinc-950/85 backdrop-blur-md border-t border-zinc-800/80 flex flex-col justify-between flex-1 space-y-2.5">
                
                <!-- Nombre y Presentación -->
                <div>
                    <h3 class="font-black text-white text-xs line-clamp-1 leading-tight tracking-tight">{{ $prod->name }}</h3>
                    <div class="flex items-center justify-between text-[10px] text-zinc-400 font-medium mt-0.5">
                        <span>{{ $prod->unit }}</span>
                        <span class="text-zinc-500 font-mono">{{ $unitsPerPackage }} unid/caja</span>
                    </div>
                </div>

                <!-- Bloque de Stock Central y Desglose -->
                <div class="p-2 rounded-xl bg-zinc-900/60 border border-zinc-800/60 flex flex-col items-center">
                    <div class="flex items-center justify-between w-full mb-1 px-1">
                        <span class="text-[9px] font-black uppercase tracking-wider text-zinc-400">Stock Bodega</span>
                        <span class="text-[9px] font-mono font-bold text-zinc-400">Botellas</span>
                    </div>
                    
                    <div class="relative w-full flex items-center justify-center">
                        <input type="number"
                               min="0"
                               id="stock-input-cards-{{ $prod->id }}"
                               value="{{ $stock }}"
                               data-product-id="{{ $prod->id }}"
                               data-sale-price="{{ $prod->sale_price }}"
                               data-last-saved="{{ $stock }}"
                               oninput="onStockInput(this, {{ $prod->id }})"
                               onchange="onStockChange(this, {{ $prod->id }})"
                               onkeydown="if(event.key==='Enter'){this.blur();event.preventDefault();}"
                               class="w-24 text-center text-xl font-black font-mono rounded-xl py-0.5 px-2 border transition-all cursor-text bg-zinc-950/90 border-zinc-800 focus:border-[#F5B81C] focus:outline-none {{ $stock <= 0 ? 'text-zinc-600' : ($stock <= 10 ? 'text-amber-400' : 'text-emerald-400') }}">
                        <span class="save-indicator absolute -top-2 -right-2 text-[9px] font-mono opacity-0 transition-opacity duration-300 pointer-events-none bg-zinc-950 px-1 py-0.5 rounded border border-zinc-800"></span>
                    </div>

                    <!-- Desglose cajas & sueltas -->
                    <div class="flex items-center justify-center gap-2 mt-1.5 w-full text-[10px]">
                        <div class="flex items-center gap-1 bg-zinc-950/90 px-2 py-0.5 rounded-lg border border-zinc-800/80">
                            <span class="text-[8px] font-black text-zinc-500 uppercase">Cajas:</span>
                            <input type="number" 
                                   min="0" 
                                   value="{{ $stockPackages }}"
                                   id="stock-packages-cards-{{ $prod->id }}"
                                   data-product-id="{{ $prod->id }}"
                                   onchange="onStockPartChange({{ $prod->id }}, 'cards')"
                                   class="w-7 text-center font-mono font-bold text-xs bg-transparent border-0 text-white focus:outline-none p-0">
                        </div>
                        <div class="flex items-center gap-1 bg-zinc-950/90 px-2 py-0.5 rounded-lg border border-zinc-800/80">
                            <span class="text-[8px] font-black text-zinc-500 uppercase">Sueltas:</span>
                            <input type="number" 
                                   min="0" 
                                   value="{{ $stockUnits }}"
                                   id="stock-units-cards-{{ $prod->id }}"
                                   data-product-id="{{ $prod->id }}"
                                   onchange="onStockPartChange({{ $prod->id }}, 'cards')"
                                   class="w-7 text-center font-mono font-bold text-xs bg-transparent border-0 text-white focus:outline-none p-0">
                        </div>
                    </div>
                </div>

                <!-- Botones de Ajuste Rápido y Editar -->
                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-zinc-900/80">
                    <div class="inline-flex items-center gap-1 shrink-0">
                        <button type="button" 
                                onclick="stepStock({{ $prod->id }}, -10)"
                                title="-10 unidades"
                                class="px-1.5 py-1 rounded-lg text-[9px] font-mono font-black bg-zinc-900/90 text-zinc-400 hover:text-rose-400 border border-zinc-800 transition-all active:scale-95 cursor-pointer">
                            -10
                        </button>
                        <button type="button" 
                                onclick="stepStock({{ $prod->id }}, -1)"
                                title="-1 unidad"
                                class="px-1.5 py-1 rounded-lg text-[9px] font-mono font-black bg-zinc-900/90 text-zinc-300 hover:text-rose-400 border border-zinc-800 transition-all active:scale-95 cursor-pointer">
                            -1
                        </button>
                        <button type="button" 
                                onclick="stepStock({{ $prod->id }}, 1)"
                                title="+1 unidad"
                                class="px-1.5 py-1 rounded-lg text-[9px] font-mono font-black bg-zinc-900/90 text-zinc-300 hover:text-emerald-400 border border-zinc-800 transition-all active:scale-95 cursor-pointer">
                            +1
                        </button>
                        <button type="button" 
                                onclick="stepStock({{ $prod->id }}, 10)"
                                title="+10 unidades"
                                class="px-1.5 py-1 rounded-lg text-[9px] font-mono font-black bg-zinc-900/90 text-zinc-300 hover:text-emerald-400 border border-zinc-800 transition-all active:scale-95 cursor-pointer">
                            +10
                        </button>
                    </div>

                    <div class="inline-flex items-center gap-1.5 ml-auto shrink-0">
                        <button type="button"
                                onclick="openEditDrawer({{ $prod->id }}, {{ json_encode($prod->name) }}, {{ json_encode($prod->category) }}, {{ json_encode($prod->unit) }}, {{ $prod->sale_price }}, {{ $prod->cost_price ?? 0 }}, {{ $prod->units_per_package }}, {{ $prod->stock_warehouse }}, {{ $prod->is_active ? 1 : 0 }}, {{ json_encode($prod->image_url) }}, true)"
                                title="Cambiar Foto"
                                class="p-1.5 rounded-xl bg-zinc-900/80 hover:bg-zinc-800 border border-zinc-800 text-zinc-400 hover:text-[#F5B81C] text-xs transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </button>
                        <button type="button"
                                onclick="openEditDrawer({{ $prod->id }}, {{ json_encode($prod->name) }}, {{ json_encode($prod->category) }}, {{ json_encode($prod->unit) }}, {{ $prod->sale_price }}, {{ $prod->cost_price ?? 0 }}, {{ $prod->units_per_package }}, {{ $prod->stock_warehouse }}, {{ $prod->is_active ? 1 : 0 }}, {{ json_encode($prod->image_url) }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-zinc-900/80 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-xs font-semibold transition-all cursor-pointer shadow-sm active:scale-95">
                            <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Editar</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
        @endforeach
    </div>

</div>

<!-- ========================================================
     6. DRAWER LATERAL: REGISTRAR NUEVA BEBIDA
     ======================================================== -->
<div class="drawer-overlay" id="product-drawer-overlay" onclick="closeDrawer()"></div>

<div class="drawer-panel" id="drawer-create-product">
    <div class="drawer-header">
        <div>
            <h2 class="text-base font-black text-white tracking-tight">Registrar Nueva Bebida</h2>
            <span class="text-xs font-bold text-[#F5B81C] tracking-wide block mt-0.5">Ingreso al Almacén</span>
        </div>
        <button onclick="closeDrawer()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="drawer-body space-y-4">
        @csrf
        <div>
            <label for="d_name" class="block text-xs font-bold text-zinc-300 mb-1.5">Nombre de la Bebida <span class="text-[#F5B81C]">*</span></label>
            <input type="text" name="name" id="d_name" required placeholder="Ej. Fernet Branca 750ml"
                   class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C] transition-all">
        </div>

        <!-- Imagen de la Bebida -->
        <div class="p-3.5 rounded-xl bg-zinc-900/70 border border-zinc-800 space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-zinc-300">Foto de la Bebida</label>
                <span class="text-[10px] text-zinc-500 font-mono">Opcional</span>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-xl bg-zinc-950 border border-zinc-800 overflow-hidden flex items-center justify-center shrink-0">
                    <img id="cr_preview_img" src="" alt="" class="w-full h-full object-contain hidden p-1">
                    <div id="cr_preview_placeholder" class="text-zinc-600 flex flex-col items-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div class="flex-1 space-y-1">
                    <input type="hidden" name="image_path" id="cr_image_path" value="">
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-bold transition-all cursor-pointer border border-zinc-700">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Subir archivo...</span>
                        <input type="file" name="image" id="cr_file_input" accept="image/*" class="hidden" onchange="previewUploadImage(this, 'cr_preview_img', 'cr_preview_placeholder')">
                    </label>
                    <p class="text-[10px] text-zinc-500">O elige del catálogo de abajo:</p>
                </div>
            </div>

            @if(!empty($drinkImages))
            <div class="pt-2 border-t border-zinc-800/80">
                <div class="grid grid-cols-6 gap-1.5 max-h-32 overflow-y-auto p-1 bg-zinc-950/60 rounded-xl border border-zinc-800/60">
                    @foreach($drinkImages as $dImg)
                    <button type="button"
                            onclick="pickCatalogImage('{{ $dImg['path'] }}', '{{ $dImg['url'] }}', 'cr_image_path', 'cr_preview_img', 'cr_preview_placeholder', this)"
                            title="{{ $dImg['filename'] }}"
                            class="catalog-thumb-btn aspect-square rounded-lg bg-zinc-900 border border-zinc-800 hover:border-[#F5B81C] p-1 flex items-center justify-center transition-all cursor-pointer">
                        <img src="{{ $dImg['url'] }}" alt="{{ $dImg['filename'] }}" class="w-full h-full object-contain pointer-events-none">
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="d_category" class="block text-xs font-bold text-zinc-300 mb-1.5">Categoría <span class="text-[#F5B81C]">*</span></label>
                <select name="category" id="d_category" required class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                    <option value="Licores">Licores (Botellas)</option>
                    <option value="Mixers">Gaseosas & Aguas (Mixers)</option>
                    <option value="Cervezas">Cervezas</option>
                    <option value="Cigarros">Cigarros</option>
                    <option value="Otros">Otros Insumos</option>
                </select>
            </div>
            <div>
                <label for="d_unit" class="block text-xs font-bold text-zinc-300 mb-1.5">Presentación <span class="text-[#F5B81C]">*</span></label>
                <input type="text" name="unit" id="d_unit" required value="Botella 750ml" placeholder="Ej. Botella 750ml"
                       class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="d_sale_price" class="block text-xs font-bold text-zinc-300 mb-1.5">Precio de Venta <span class="text-[#F5B81C]">*</span></label>
                <div class="flex items-center rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 focus-within:border-[#F5B81C] transition-all">
                    <span class="text-xs font-mono font-bold text-[#F5B81C] mr-2 shrink-0">Bs.</span>
                    <input type="number" step="0.50" name="sale_price" id="d_sale_price" required value="0"
                           class="w-full text-sm font-mono font-bold bg-transparent border-0 text-white focus:outline-none p-0">
                </div>
            </div>
            <div>
                <label for="d_cost_price" class="block text-xs font-bold text-zinc-300 mb-1.5">Costo de Compra</label>
                <div class="flex items-center rounded-xl bg-zinc-900 border border-zinc-800 px-3 py-2 focus-within:border-[#F5B81C] transition-all">
                    <span class="text-xs font-mono font-bold text-zinc-500 mr-2 shrink-0">Bs.</span>
                    <input type="number" step="0.50" name="cost_price" id="d_cost_price" value="0"
                           class="w-full text-sm font-mono bg-transparent border-0 text-zinc-300 focus:outline-none p-0">
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="d_units_per_package" class="block text-xs font-bold text-zinc-300 mb-1.5">Unidades por Caja <span class="text-[#F5B81C]">*</span></label>
                <input type="number" name="units_per_package" id="d_units_per_package" required value="6"
                       class="w-full px-3.5 py-2.5 rounded-xl text-sm font-mono bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
            </div>
            <div>
                <label for="d_stock_warehouse" class="block text-xs font-bold text-zinc-300 mb-1.5">Stock en Bodega <span class="text-[#F5B81C]">*</span></label>
                <input type="number" name="stock_warehouse" id="d_stock_warehouse" required value="0"
                       class="w-full px-3.5 py-2.5 rounded-xl text-sm font-mono font-bold bg-zinc-900 border border-zinc-800 text-[#F5B81C] focus:outline-none focus:border-[#F5B81C] transition-all">
            </div>
        </div>
        <div class="pt-5 border-t border-zinc-800 flex items-center justify-end gap-2.5 mt-6 w-full">
            <button type="button" onclick="closeDrawer()" 
                    class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl border border-zinc-800 text-zinc-400 hover:text-white text-xs sm:text-sm font-bold text-center transition-all cursor-pointer">
                Cancelar
            </button>
            <button type="submit" 
                    class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs sm:text-sm font-black uppercase tracking-wider text-center transition-all cursor-pointer shadow-md">
                Guardar Bebida
            </button>
        </div>
    </form>
</div>

<!-- ========================================================
     7. DRAWER LATERAL: EDITAR BEBIDA
     ======================================================== -->
<div class="drawer-panel" id="drawer-edit-product">
    <div class="drawer-header">
        <div>
            <h2 class="text-base font-black text-white tracking-tight" id="edit-prod-title">Editar Bebida</h2>
            <span class="text-xs font-bold text-[#F5B81C] tracking-wide block mt-0.5" id="edit-prod-badge"></span>
        </div>
        <button type="button" onclick="closeDrawer()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <form method="POST" id="edit-prod-form" action="" enctype="multipart/form-data" onsubmit="event.preventDefault(); handleEditProductSubmit(event); return false;" class="drawer-body space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="_ajax" value="1">
        <div>
            <label for="ed_name" class="block text-xs font-bold text-zinc-300 mb-1.5">Nombre de la Bebida <span class="text-[#F5B81C]">*</span></label>
            <input type="text" name="name" id="ed_name" required
                   class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
        </div>

        <!-- Sección de Imagen de la Bebida -->
        <div id="ed_image_section" class="p-3.5 rounded-xl bg-zinc-900/70 border border-zinc-800 space-y-3 transition-all duration-300">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-zinc-300">Foto de la Bebida</label>
                <span class="text-[10px] text-zinc-500 font-mono">PNG / JPG / WEBP</span>
            </div>

            <div class="flex items-center gap-3">
                <!-- Miniatura de Vista Previa -->
                <div class="w-16 h-16 rounded-xl bg-zinc-950 border border-zinc-800 overflow-hidden flex items-center justify-center shrink-0 relative">
                    <img id="ed_preview_img" src="" alt="" class="w-full h-full object-contain hidden p-1">
                    <div id="ed_preview_placeholder" class="text-zinc-600 flex flex-col items-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div class="flex-1 space-y-1.5">
                    <input type="hidden" name="image_path" id="ed_image_path" value="">
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-bold transition-all cursor-pointer border border-zinc-700">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Subir nueva foto...</span>
                        <input type="file" name="image" id="ed_file_input" accept="image/*" class="hidden" onchange="previewUploadImage(this, 'ed_preview_img', 'ed_preview_placeholder')">
                    </label>
                    <p class="text-[10px] text-zinc-500">O elige una foto del catálogo de abajo:</p>
                </div>
            </div>

            <!-- Malla de Selección de Catálogo Drinks -->
            @if(!empty($drinkImages))
            <div class="pt-2 border-t border-zinc-800/80">
                <span class="block text-[10px] font-bold text-zinc-400 mb-1.5">Catálogo en /images/drinks ({{ count($drinkImages) }} fotos disponibles):</span>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-1.5 max-h-36 overflow-y-auto p-1.5 bg-zinc-950/60 rounded-xl border border-zinc-800/60">
                    @foreach($drinkImages as $dImg)
                    <button type="button"
                            onclick="pickCatalogImage('{{ $dImg['path'] }}', '{{ $dImg['url'] }}', 'ed_image_path', 'ed_preview_img', 'ed_preview_placeholder', this)"
                            title="{{ $dImg['filename'] }}"
                            class="catalog-thumb-btn aspect-square rounded-lg bg-zinc-900 border border-zinc-800 hover:border-[#F5B81C] p-1 flex items-center justify-center transition-all cursor-pointer">
                        <img src="{{ $dImg['url'] }}" alt="{{ $dImg['filename'] }}" class="w-full h-full object-contain pointer-events-none">
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="ed_category" class="block text-xs font-bold text-zinc-300 mb-1.5">Categoría <span class="text-[#F5B81C]">*</span></label>
                <select name="category" id="ed_category" required class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                    <option value="Licores">Licores (Botellas)</option>
                    <option value="Mixers">Gaseosas & Aguas (Mixers)</option>
                    <option value="Cervezas">Cervezas</option>
                    <option value="Cigarros">Cigarros</option>
                    <option value="Otros">Otros Insumos</option>
                </select>
            </div>
            <div>
                <label for="ed_unit" class="block text-xs font-bold text-zinc-300 mb-1.5">Presentación <span class="text-[#F5B81C]">*</span></label>
                <input type="text" name="unit" id="ed_unit" required placeholder="Ej. Botella 750ml"
                       class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="ed_sale_price" class="block text-xs font-bold text-zinc-300 mb-1.5">Precio de Venta <span class="text-[#F5B81C]">*</span></label>
                <div class="flex items-center rounded-xl bg-zinc-900 border border-zinc-800 px-3.5 py-2 focus-within:border-[#F5B81C] transition-all">
                    <span class="text-xs font-mono font-bold text-[#F5B81C] mr-2 shrink-0">Bs.</span>
                    <input type="number" step="0.50" name="sale_price" id="ed_sale_price" required
                           class="w-full text-sm font-mono font-bold bg-transparent border-0 text-white focus:outline-none p-0">
                </div>
            </div>
            <div>
                <label for="ed_cost_price" class="block text-xs font-bold text-zinc-300 mb-1.5">Costo de Compra</label>
                <div class="flex items-center rounded-xl bg-zinc-900 border border-zinc-800 px-3.5 py-2 focus-within:border-[#F5B81C] transition-all">
                    <span class="text-xs font-mono font-bold text-zinc-500 mr-2 shrink-0">Bs.</span>
                    <input type="number" step="0.50" name="cost_price" id="ed_cost_price"
                           class="w-full text-sm font-mono bg-transparent border-0 text-zinc-300 focus:outline-none p-0">
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="ed_units_per_package" class="block text-xs font-bold text-zinc-300 mb-1.5">Unidades por Caja <span class="text-[#F5B81C]">*</span></label>
                <input type="number" name="units_per_package" id="ed_units_per_package" required
                       class="w-full px-3.5 py-2.5 rounded-xl text-sm font-mono bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
            </div>
            <div>
                <label for="ed_stock_warehouse" class="block text-xs font-bold text-zinc-300 mb-1.5">Stock en Bodega <span class="text-[#F5B81C]">*</span></label>
                <input type="number" name="stock_warehouse" id="ed_stock_warehouse" required
                       class="w-full px-3.5 py-2.5 rounded-xl text-sm font-mono font-bold bg-zinc-900 border border-zinc-800 text-[#F5B81C] focus:outline-none focus:border-[#F5B81C] transition-all">
            </div>
        </div>
        <div>
            <label for="ed_is_active" class="block text-xs font-bold text-zinc-300 mb-1.5">Estado</label>
            <select name="is_active" id="ed_is_active" class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                <option value="1">Activo (Disponible para venta en barras)</option>
                <option value="0">Pausado (No mostrar en ventas)</option>
            </select>
        </div>
        <div class="pt-5 border-t border-zinc-800 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mt-6 w-full">
            <button type="button" onclick="confirmDeleteProduct()" class="w-full sm:w-auto text-center sm:text-left py-2 text-xs text-rose-400 hover:text-rose-300 font-bold transition-all cursor-pointer">
                Eliminar Bebida
            </button>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <button type="button" onclick="closeDrawer()" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl border border-zinc-800 text-zinc-400 hover:text-white text-xs sm:text-sm font-bold text-center transition-all cursor-pointer">
                    Cancelar
                </button>
                <button type="button" onclick="handleEditProductSubmit(event)" id="edit-prod-submit-btn" class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-[#F5B81C] hover:bg-[#e5ac18] text-black text-xs sm:text-sm font-black uppercase tracking-wider text-center transition-all cursor-pointer shadow-md">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>
    <form id="delete-prod-inline-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<!-- Toast Efímero Estilo iOS para Bodega Central -->
<div id="bodega-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full text-xs font-semibold text-white bg-zinc-900 border border-zinc-700 shadow-2xl opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-2">
    <span id="bodega-toast-dot" class="w-2 h-2 rounded-full bg-[#F5B81C]"></span>
    <span id="bodega-toast-text">Notificación</span>
</div>

<!-- ========================================================
     8. JAVASCRIPT REACTIVO (SIN RECARGA & SINCRONIZADO)
     ======================================================== -->
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE = '/products';
let debounceTimers = {};

/* --- SELECTOR DE VISTA (TARJETAS POR DEFECTO / TABLA) --- */
function switchViewMode(mode) {
    const tableContainer = document.getElementById('view-table-container');
    const cardsContainer = document.getElementById('view-cards-container');
    const tableBtn = document.getElementById('view-mode-table-btn');
    const cardsBtn = document.getElementById('view-mode-cards-btn');

    if (mode === 'cards') {
        tableContainer.classList.add('hidden');
        cardsContainer.classList.remove('hidden');

        cardsBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer bg-[#F5B81C] text-black shadow-sm';
        tableBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer text-zinc-400 hover:text-white';
        localStorage.setItem('tiochu_bodega_view', 'cards');
    } else {
        tableContainer.classList.remove('hidden');
        cardsContainer.classList.add('hidden');

        tableBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer bg-[#F5B81C] text-black shadow-sm';
        cardsBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer text-zinc-400 hover:text-white';
        localStorage.setItem('tiochu_bodega_view', 'table');
    }
}

// Inicializar vista guardada (POR DEFECTO EN TARJETAS PARA ESCRITORIO Y CELULAR)
(function() {
    const saved = localStorage.getItem('tiochu_bodega_view');
    const initial = saved || 'cards';
    switchViewMode(initial);
})();

/* --- HELPERS DE ELEMENTOS Y SINCRONIZACIÓN --- */
function getInputs(id) {
    return [
        document.getElementById('stock-input-table-' + id),
        document.getElementById('stock-input-cards-' + id)
    ].filter(Boolean);
}

function getSaveEls(id) {
    const tableInp = document.getElementById('stock-input-table-' + id);
    const cardInp = document.getElementById('stock-input-cards-' + id);
    return [
        tableInp?.parentElement?.querySelector('.save-indicator'),
        cardInp?.parentElement?.querySelector('.save-indicator')
    ].filter(Boolean);
}

function applyColorToAll(id, val) {
    getInputs(id).forEach(inp => {
        inp.classList.remove('text-zinc-600', 'text-amber-400', 'text-emerald-400');
        if (val <= 0) inp.classList.add('text-zinc-600');
        else if (val <= 10) inp.classList.add('text-amber-400');
        else inp.classList.add('text-emerald-400');
    });
}

function syncAllInputs(id, total) {
    const item = document.querySelector('.product-item[data-id="' + id + '"]');
    const uPkg = parseInt(item?.dataset.unitsPerPackage) || 1;
    const pkgs = Math.floor(Math.max(0, total) / uPkg);
    const units = Math.max(0, total) % uPkg;

    // Actualizar inputs principales
    getInputs(id).forEach(inp => {
        inp.value = total;
        inp.dataset.lastSaved = total;
    });

    // Actualizar inputs de desglose en ambas vistas
    const pt = document.getElementById('stock-packages-table-' + id);
    const ut = document.getElementById('stock-units-table-' + id);
    const pc = document.getElementById('stock-packages-cards-' + id);
    const uc = document.getElementById('stock-units-cards-' + id);

    if (pt) pt.value = pkgs;
    if (ut) ut.value = units;
    if (pc) pc.value = pkgs;
    if (uc) uc.value = units;

    applyColorToAll(id, total);
}

/* --- INDICADORES VISUALES --- */
function showSaving(id) {
    getSaveEls(id).forEach(el => {
        el.textContent = 'Guardando...';
        el.style.color = '#F5B81C';
        el.style.opacity = '1';
    });
}

function showSaved(id) {
    getSaveEls(id).forEach(el => {
        el.textContent = 'Guardado';
        el.style.color = '#34d399';
        el.style.opacity = '1';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.style.opacity = '0', 2000);
    });
}

function showError(id) {
    getSaveEls(id).forEach(el => {
        el.textContent = 'Error';
        el.style.color = '#f87171';
        el.style.opacity = '1';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.style.opacity = '0', 3000);
    });
}

/* --- ACTUALIZAR KPIS GLOBALES --- */
function refreshKPIs() {
    let total = 0, val = 0, low = 0;
    document.querySelectorAll('#table-products-tbody .product-item').forEach(row => {
        const id = row.dataset.id;
        const inp = document.getElementById('stock-input-table-' + id);
        const s = inp ? (parseInt(inp.value) || 0) : (parseInt(row.dataset.stock) || 0);
        row.dataset.stock = s;
        total += s;
        val += s * (parseFloat(row.dataset.price) || 0);
        if (s <= 10) low++;
    });

    const kb = document.getElementById('kpi-total-bottles');
    const kv = document.getElementById('kpi-valuation');
    const kl = document.getElementById('kpi-low-stock');

    if (kb) kb.innerHTML = total.toLocaleString() + ' <span class="text-xs font-normal text-zinc-400">botellas</span>';
    if (kv) kv.innerHTML = '<span class="text-sm text-[#F5B81C] mr-0.5 font-bold">Bs.</span>' + Math.round(val).toLocaleString();
    if (kl) {
        kl.innerHTML = low + ' <span class="text-xs font-normal text-zinc-400">alertas</span>';
        if (low > 0) {
            kl.classList.remove('text-zinc-400');
            kl.classList.add('text-amber-400');
        } else {
            kl.classList.remove('text-amber-400');
            kl.classList.add('text-zinc-400');
        }
    }
}

/* --- ENVÍO AL SERVIDOR (AJAX PATCH) --- */
function pushStock(productId, newStock, oldStock) {
    showSaving(productId);
    fetch(BASE + '/' + productId + '/quick-stock', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ stock: newStock }),
    })
    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(data => {
        syncAllInputs(productId, data.stock_warehouse);
        showSaved(productId);
        refreshKPIs();
    })
    .catch(() => {
        syncAllInputs(productId, oldStock);
        showError(productId);
    });
}

function pushStockParts(productId, packages, units, oldStock) {
    showSaving(productId);
    fetch(BASE + '/' + productId + '/quick-stock', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ stock_packages: packages, stock_units: units }),
    })
    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(data => {
        syncAllInputs(productId, data.stock_warehouse);
        showSaved(productId);
        refreshKPIs();
    })
    .catch(() => {
        syncAllInputs(productId, oldStock);
        showError(productId);
    });
}

/* --- STEPPER: -10 / -1 / +1 / +10 --- */
function stepStock(productId, delta) {
    const inps = getInputs(productId);
    if (!inps.length) return;
    const old = parseInt(inps[0].value) || 0;
    const nv = Math.max(0, old + delta);

    syncAllInputs(productId, nv);

    clearTimeout(debounceTimers[productId]);
    debounceTimers[productId] = setTimeout(() => pushStock(productId, nv, old), 400);
    refreshKPIs();
}

/* --- INPUT DIRECTO DE BOTELLAS --- */
function onStockInput(inp, productId) {
    const total = Math.max(0, parseInt(inp.value) || 0);
    applyColorToAll(productId, total);
}

function onStockChange(inp, productId) {
    const old = parseInt(inp.dataset.lastSaved) || 0;
    const nv = Math.max(0, parseInt(inp.value) || 0);
    syncAllInputs(productId, nv);
    pushStock(productId, nv, old);
    refreshKPIs();
}

/* --- CAMBIO EN CAJAS O SUELTAS --- */
function onStockPartChange(productId, sourceView) {
    const packagesInput = document.getElementById('stock-packages-' + sourceView + '-' + productId);
    const unitsInput = document.getElementById('stock-units-' + sourceView + '-' + productId);
    const mainInput = document.getElementById('stock-input-' + sourceView + '-' + productId);

    const packages = Math.max(0, parseInt(packagesInput?.value) || 0);
    const units = Math.max(0, parseInt(unitsInput?.value) || 0);
    const item = document.querySelector('.product-item[data-id="' + productId + '"]');
    const uPkg = parseInt(item?.dataset.unitsPerPackage) || 1;
    const old = parseInt(mainInput?.dataset.lastSaved) || 0;
    const total = (packages * uPkg) + units;

    syncAllInputs(productId, total);
    pushStockParts(productId, packages, units, old);
    refreshKPIs();
}

/* --- FILTROS POR CATEGORÍA --- */
function filterCat(cat, btn) {
    document.querySelectorAll('.category-btn').forEach(b => {
        b.className = 'category-btn px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap text-zinc-400 hover:text-white hover:bg-zinc-900 border border-transparent';
    });
    btn.className = 'category-btn px-3 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap bg-[#F5B81C] text-black shadow-sm';

    document.querySelectorAll('.product-item').forEach(item => {
        item.style.display = (cat === 'all' || item.dataset.subcat === cat) ? '' : 'none';
    });
}

/* --- BUSCADOR EN VIVO --- */
function searchProducts(q) {
    const query = q.toLowerCase().trim();
    document.querySelectorAll('.product-item').forEach(item => {
        item.style.display = (!query || item.dataset.name.includes(query)) ? '' : 'none';
    });
}

/* --- PREVISUALIZACIÓN Y SELECCIÓN DE IMÁGENES --- */
function previewUploadImage(input, previewId, placeholderId) {
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function pickCatalogImage(path, url, inputId, previewId, placeholderId, btnEl) {
    const pathInput = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);

    if (pathInput) pathInput.value = path;
    if (preview) {
        preview.src = url;
        preview.classList.remove('hidden');
    }
    if (placeholder) placeholder.classList.add('hidden');

    if (btnEl && btnEl.parentElement) {
        btnEl.parentElement.querySelectorAll('.catalog-thumb-btn').forEach(b => {
            b.classList.remove('border-[#F5B81C]', 'bg-zinc-800', 'ring-1', 'ring-[#F5B81C]');
            b.classList.add('border-zinc-800', 'bg-zinc-900');
        });
        btnEl.classList.remove('border-zinc-800', 'bg-zinc-900');
        btnEl.classList.add('border-[#F5B81C]', 'bg-zinc-800', 'ring-1', 'ring-[#F5B81C]');
    }
}

/* --- DRAWERS DE CREACIÓN Y EDICIÓN --- */
function openCreateDrawer() {
    const crPreview = document.getElementById('cr_preview_img');
    const crPlaceholder = document.getElementById('cr_preview_placeholder');
    const crFile = document.getElementById('cr_file_input');
    const crPath = document.getElementById('cr_image_path');

    if (crPreview) { crPreview.src = ''; crPreview.classList.add('hidden'); }
    if (crPlaceholder) crPlaceholder.classList.remove('hidden');
    if (crFile) crFile.value = '';
    if (crPath) crPath.value = '';

    document.querySelectorAll('#drawer-create-product .catalog-thumb-btn').forEach(b => {
        b.classList.remove('border-[#F5B81C]', 'bg-zinc-800', 'ring-1', 'ring-[#F5B81C]');
        b.classList.add('border-zinc-800', 'bg-zinc-900');
    });

    document.getElementById('product-drawer-overlay').classList.add('is-open');
    document.getElementById('drawer-create-product').classList.add('is-open');
    document.body.classList.add('drawer-open');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('d_name').focus(), 250);
}

function openEditDrawer(id, name, cat, unit, saleP, costP, uPkg, stock, active, imgUrl, focusImage = false) {
    const editForm = document.getElementById('edit-prod-form');
    editForm.action = BASE + '/' + id;
    editForm.dataset.productId = id;
    document.getElementById('delete-prod-inline-form').action = BASE + '/' + id;
    document.getElementById('edit-prod-title').textContent = name;
    document.getElementById('edit-prod-badge').textContent = cat;
    document.getElementById('ed_name').value = name;
    document.getElementById('ed_unit').value = unit;
    document.getElementById('ed_sale_price').value = saleP;
    document.getElementById('ed_cost_price').value = costP;
    document.getElementById('ed_units_per_package').value = uPkg;
    document.getElementById('ed_stock_warehouse').value = stock;
    document.getElementById('ed_is_active').value = active;
    for (let o of document.getElementById('ed_category').options) o.selected = o.value === cat;

    // Reset y configuración de imagen previa
    const previewImg = document.getElementById('ed_preview_img');
    const placeholder = document.getElementById('ed_preview_placeholder');
    const imagePathInput = document.getElementById('ed_image_path');
    const fileInput = document.getElementById('ed_file_input');
    if (fileInput) fileInput.value = '';
    if (imagePathInput) imagePathInput.value = '';

    if (imgUrl) {
        previewImg.src = imgUrl;
        previewImg.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    } else {
        previewImg.src = '';
        previewImg.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');
    }

    // Resetear selección en catálogo
    document.querySelectorAll('#drawer-edit-product .catalog-thumb-btn').forEach(b => {
        b.classList.remove('border-[#F5B81C]', 'bg-zinc-800', 'ring-1', 'ring-[#F5B81C]');
        b.classList.add('border-zinc-800', 'bg-zinc-900');
    });

    document.getElementById('product-drawer-overlay').classList.add('is-open');
    document.getElementById('drawer-edit-product').classList.add('is-open');
    document.body.classList.add('drawer-open');
    document.body.style.overflow = 'hidden';

    if (focusImage) {
        setTimeout(() => {
            const imgSection = document.getElementById('ed_image_section');
            if (imgSection) {
                imgSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                imgSection.classList.add('ring-2', 'ring-[#F5B81C]');
                setTimeout(() => imgSection.classList.remove('ring-2', 'ring-[#F5B81C]'), 1500);
            }
        }, 150);
    }
}

function closeDrawer() {
    document.getElementById('product-drawer-overlay').classList.remove('is-open');
    document.getElementById('drawer-create-product').classList.remove('is-open');
    document.getElementById('drawer-edit-product').classList.remove('is-open');
    document.body.classList.remove('drawer-open');
    document.body.style.overflow = '';
}

/* --- TOAST DISCRETO BODEGA CENTRAL --- */
function showAppToast(text, type = 'success') {
    const toast = document.getElementById('bodega-toast');
    const toastText = document.getElementById('bodega-toast-text');
    const toastDot = document.getElementById('bodega-toast-dot');
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
    }, 2400);
}

function calcSubcat(name, cat) {
    const n = name.toLowerCase();
    if (n.includes('singani')) return 'Singani';
    if (n.includes('whisky') || n.includes('whiskey') || n.includes('jack daniel')) return 'Whisky';
    if (n.includes('ron') || n.includes('abuelo') || n.includes('havana') || n.includes('fernet')) return 'Ron';
    if (n.includes('gin') || n.includes('tanqueray') || n.includes('beefeater') || n.includes('dharma') || n.includes('ganesha') || n.includes('james cook')) return 'Gin';
    if (n.includes('tequila') || n.includes('jager') || n.includes('cuervo') || n.includes('olmeca')) return 'Tequila';
    if (cat === 'Mixers' || n.includes('coca') || n.includes('ginger') || n.includes('agua') || n.includes('sprite') || n.includes('aquarius') || n.includes('tónica') || n.includes('tonica')) return 'Mixer';
    if (cat === 'Cervezas' || n.includes('cerveza') || n.includes('huari') || n.includes('paceña')) return 'Cerveza';
    return 'Otro';
}

function updateProductDOM(p) {
    const id = p.id;
    const items = document.querySelectorAll('.product-item[data-id="' + id + '"]');
    const sub = calcSubcat(p.name, p.category);
    const saleP = parseFloat(p.sale_price) || 0;
    const costP = parseFloat(p.cost_price) || 0;
    const stock = parseInt(p.stock_warehouse) || 0;
    const uPkg = parseInt(p.units_per_package) || 1;

    items.forEach(item => {
        item.dataset.name = p.name.toLowerCase();
        item.dataset.subcat = sub;
        item.dataset.price = saleP;
        item.dataset.stock = stock;
        item.dataset.unitsPerPackage = uPkg;

        // Actualizar botones de editar en este item
        const editBtns = item.querySelectorAll('button[onclick*="openEditDrawer"]');
        editBtns.forEach(btn => {
            const hasFocusImg = btn.getAttribute('onclick')?.includes(', true');
            btn.setAttribute('onclick', `openEditDrawer(${id}, ${JSON.stringify(p.name)}, ${JSON.stringify(p.category)}, ${JSON.stringify(p.unit)}, ${saleP}, ${costP}, ${uPkg}, ${stock}, ${p.is_active ? 1 : 0}, ${JSON.stringify(p.image_url)}${hasFocusImg ? ', true' : ''})`);
        });

        // Actualizar visual de Tarjeta
        if (item.classList.contains('group')) {
            const titleEl = item.querySelector('h3');
            if (titleEl) titleEl.textContent = p.name;

            const unitSpans = item.querySelectorAll('.flex.items-center.justify-between.text-\\[10px\\] span');
            if (unitSpans.length >= 2) {
                unitSpans[0].textContent = p.unit;
                unitSpans[1].textContent = uPkg + ' unid/caja';
            }

            const catBadge = item.querySelector('.uppercase');
            if (catBadge) catBadge.textContent = sub;

            const priceBadge = item.querySelector('.font-mono.font-bold');
            if (priceBadge) {
                priceBadge.innerHTML = `<span class="text-[#F5B81C] text-[10px] mr-1">Bs.</span>${saleP.toFixed(2)}`;
            }

            if (p.image_url) {
                const imgs = item.querySelectorAll('img');
                imgs.forEach(img => img.src = p.image_url);
            }

            // Resaltado visual instantáneo
            item.classList.add('ring-2', 'ring-[#F5B81C]');
            setTimeout(() => item.classList.remove('ring-2', 'ring-[#F5B81C]'), 1500);
        } else {
            // Actualizar visual de Fila de Tabla
            const nameEl = item.querySelector('.font-black.text-white.text-xs');
            if (nameEl) nameEl.textContent = p.name;

            const unitEl = item.querySelector('.text-\\[10px\\].text-zinc-400');
            if (unitEl) unitEl.textContent = `${p.unit} • ${uPkg} unid. x caja`;

            const catBadge = item.querySelector('td:nth-child(2) span');
            if (catBadge) catBadge.textContent = sub;

            const priceTd = item.querySelector('td:nth-child(3)');
            if (priceTd) priceTd.innerHTML = `<span class="text-[#F5B81C] text-[10px] mr-0.5">Bs.</span>${saleP.toFixed(2)}`;

            const initialEl = item.querySelector('.text-\\[\\#F5B81C\\]');
            if (initialEl) initialEl.textContent = p.name.charAt(0).toUpperCase();

            // Resaltado de fila
            item.classList.add('bg-zinc-800/80');
            setTimeout(() => item.classList.remove('bg-zinc-800/80'), 1500);
        }
    });

    syncAllInputs(id, stock);
}

/* --- ENVÍO ASÍNCRONO DEL FORMULARIO DE EDICIÓN (SIN RECARGA NI PÉRDIDA DE SCROLL) --- */
window.handleEditProductSubmit = async function(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const form = document.getElementById('edit-prod-form');
    if (!form) return false;

    // Preservar la posición exacta del scroll del contenedor principal <main>
    const mainContainer = document.querySelector('main');
    const savedScrollTop = mainContainer ? mainContainer.scrollTop : (window.pageYOffset || document.documentElement.scrollTop || 0);

    const submitBtn = document.getElementById('edit-prod-submit-btn') || form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : 'Guardar Cambios';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Guardando...</span>';
    }

    try {
        const formData = new FormData(form);
        const prodId = form.dataset.productId || form.action.split('/').filter(Boolean).pop();
        const targetUrl = BASE + '/' + prodId;

        const res = await fetch(targetUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: formData
        });

        const data = await res.json();

        if (res.ok && data.success) {
            closeDrawer();

            // Restaurar instantáneamente el scroll al punto exacto donde estaba el usuario
            if (mainContainer) {
                mainContainer.scrollTop = savedScrollTop;
            } else {
                window.scrollTo(0, savedScrollTop);
            }

            if (data.product) {
                updateProductDOM(data.product);
            }
            showAppToast(data.message || 'Bebida actualizada');
            refreshKPIs();
        } else {
            const err = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Error al guardar');
            showAppToast(err, 'error');
        }
    } catch(err) {
        console.error('Error al actualizar bebida:', err);
        showAppToast('Error de conexión al guardar cambios', 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    return false;
};

document.getElementById('edit-prod-form')?.addEventListener('submit', window.handleEditProductSubmit);

async function confirmDeleteProduct() {
    if (!confirm('¿Eliminar definitivamente esta bebida del inventario?')) return;
    const form = document.getElementById('delete-prod-inline-form');
    const mainContainer = document.querySelector('main');
    const savedScrollTop = mainContainer ? mainContainer.scrollTop : (window.pageYOffset || document.documentElement.scrollTop || 0);

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: new FormData(form)
        });
        const data = await res.json();
        closeDrawer();

        if (mainContainer) {
            mainContainer.scrollTop = savedScrollTop;
        } else {
            window.scrollTo(0, savedScrollTop);
        }

        const prodId = form.action.split('/').filter(Boolean).pop();
        document.querySelectorAll('.product-item[data-id="' + prodId + '"]').forEach(el => el.remove());
        showAppToast(data.message || 'Bebida eliminada del catálogo');
        refreshKPIs();
    } catch(e) {
        console.error('Error al eliminar producto:', e);
        showAppToast('Error al eliminar la bebida', 'error');
    }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });
</script>

@endsection
