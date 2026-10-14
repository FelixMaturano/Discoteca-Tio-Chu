@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-1">
                Edición de Catálogo #{{ $product->id }}
            </span>
            <h2 class="text-xl font-black text-white tracking-tight">{{ $product->name }}</h2>
            <p class="text-xs text-zinc-400">Actualiza precios de venta, categoría o stock en almacén central.</p>
        </div>
        <a href="{{ route('products.index') }}" 
           class="glass-card hover:bg-white/10 text-zinc-300 hover:text-white px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver al Catálogo
        </a>
    </div>

    <!-- Form Panel -->
    <form method="POST" action="{{ route('products.update', $product) }}" class="glass-panel p-6 sm:p-8 rounded-2xl border border-white/10 shadow-2xl space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">
                Nombre de la Bebida / Insumo <span class="text-amber-400">*</span>
            </label>
            <input type="text" name="name" id="name" required value="{{ old('name', $product->name) }}"
                   class="glass-input w-full px-4 py-3 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 transition-all">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="category" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">
                    Categoría <span class="text-amber-400">*</span>
                </label>
                <select name="category" id="category" required 
                        class="glass-input w-full px-4 py-3 rounded-xl text-sm text-white bg-zinc-900/90 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 transition-all">
                    <option value="Licores" {{ $product->category === 'Licores' ? 'selected' : '' }} class="bg-zinc-900 text-white">Licores (Botellas)</option>
                    <option value="Mixers" {{ $product->category === 'Mixers' ? 'selected' : '' }} class="bg-zinc-900 text-white">Mixers / Gaseosas / Aguas</option>
                    <option value="Cervezas" {{ $product->category === 'Cervezas' ? 'selected' : '' }} class="bg-zinc-900 text-white">Cervezas</option>
                    <option value="Cigarros" {{ $product->category === 'Cigarros' ? 'selected' : '' }} class="bg-zinc-900 text-white">Cigarros</option>
                    <option value="Otros" {{ $product->category === 'Otros' ? 'selected' : '' }} class="bg-zinc-900 text-white">Otros / Insumos</option>
                </select>
            </div>

            <div>
                <label for="unit" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">
                    Presentación / Volumen <span class="text-amber-400">*</span>
                </label>
                <input type="text" name="unit" id="unit" required value="{{ old('unit', $product->unit) }}"
                       class="glass-input w-full px-4 py-3 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 transition-all">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label for="sale_price" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">
                    Precio Venta (Bs.) <span class="text-amber-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-3 text-xs font-mono font-bold text-amber-400">Bs.</span>
                    <input type="number" step="0.5" name="sale_price" id="sale_price" required value="{{ old('sale_price', $product->sale_price) }}"
                           class="glass-input w-full pl-11 pr-4 py-3 rounded-xl text-sm font-mono font-bold text-white focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 transition-all">
                </div>
            </div>

            <div>
                <label for="cost_price" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">
                    Costo Compra (Bs.)
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-3 text-xs font-mono font-bold text-zinc-500">Bs.</span>
                    <input type="number" step="0.5" name="cost_price" id="cost_price" value="{{ old('cost_price', $product->cost_price) }}"
                           class="glass-input w-full pl-11 pr-4 py-3 rounded-xl text-sm font-mono text-zinc-300 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 transition-all">
                </div>
            </div>

            <div>
                <label for="units_per_package" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">
                    Unid. x Caja / Paq.
                </label>
                <input type="number" name="units_per_package" id="units_per_package" required value="{{ old('units_per_package', $product->units_per_package) }}"
                       class="glass-input w-full px-4 py-3 rounded-xl text-sm font-mono text-white focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 transition-all">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="stock_warehouse" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">
                    Stock en Almacén
                </label>
                <input type="number" name="stock_warehouse" id="stock_warehouse" required value="{{ old('stock_warehouse', $product->stock_warehouse) }}"
                       class="glass-input w-full px-4 py-3 rounded-xl text-sm font-mono font-bold text-amber-400 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 transition-all">
            </div>

            <div>
                <label for="is_active" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">
                    Estado Operativo
                </label>
                <select name="is_active" id="is_active" 
                        class="glass-input w-full px-4 py-3 rounded-xl text-sm text-white bg-zinc-900/90 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 transition-all">
                    <option value="1" {{ $product->is_active ? 'selected' : '' }} class="bg-zinc-900 text-white">Activo (Disponible en Barras)</option>
                    <option value="0" {{ !$product->is_active ? 'selected' : '' }} class="bg-zinc-900 text-white">Inactivo / Descontinuado</option>
                </select>
            </div>
        </div>

        <div class="pt-6 flex items-center justify-between border-t border-white/10">
            <button type="button" 
                    onclick="if(confirm('¿Eliminar definitivamente este producto del catálogo?')) document.getElementById('delete-prod-form').submit();" 
                    class="text-xs text-rose-400 hover:text-rose-300 font-bold uppercase tracking-wider hover:underline transition-all">
                Eliminar Producto
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('products.index') }}" 
                   class="px-5 py-2.5 glass-card hover:bg-white/10 text-zinc-400 hover:text-white text-xs font-semibold rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-zinc-950 text-xs font-extrabold tracking-wider uppercase rounded-xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>

    <form id="delete-prod-form" method="POST" action="{{ route('products.destroy', $product) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
