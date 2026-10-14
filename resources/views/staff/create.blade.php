@extends('layouts.app')

@section('title', 'Nuevo Personal')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-zinc-800/80 pb-4">
        <div>
            <h2 class="text-xl font-black text-white tracking-tight">Registrar Personal</h2>
        </div>
        <a href="{{ route('staff.index') }}" 
           class="bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Volver a la Lista</span>
        </a>
    </div>

    <!-- Form Panel -->
    <form method="POST" action="{{ route('staff.store') }}" class="p-6 sm:p-8 rounded-2xl theme-card border theme-border shadow-xl space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-xs font-bold text-zinc-300 mb-1.5">
                Nombre Completo o Apodo <span class="text-[#F5B81C]">*</span>
            </label>
            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                   placeholder="Ej. MAURI, ALEX, KELLY, ARIEL"
                   class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white placeholder-zinc-500 uppercase focus:outline-none focus:border-[#F5B81C] transition-all">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="role" class="block text-xs font-bold text-zinc-300 mb-1.5">
                    Cargo / Rol <span class="text-[#F5B81C]">*</span>
                </label>
                <select name="role" id="role" required 
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                    <option value="Mesero" class="bg-zinc-900">Mesero</option>
                    <option value="Limpieza" class="bg-zinc-900">Limpieza</option>
                    <option value="Seguridad" class="bg-zinc-900">Seguridad</option>
                    <option value="Bartender Principal" class="bg-zinc-900">Bartender Principal</option>
                    <option value="Bartender Subterráneo" class="bg-zinc-900">Bartender Subterráneo</option>
                    <option value="DJ / Sonido" class="bg-zinc-900">DJ / Sonido</option>
                    <option value="Administrador" class="bg-zinc-900">Administrador</option>
                </select>
            </div>

            <div>
                <label for="assigned_bar" class="block text-xs font-bold text-zinc-300 mb-1.5">
                    Barra / Área Asignada
                </label>
                <select name="assigned_bar" id="assigned_bar" 
                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all cursor-pointer">
                    <option value="Meseros" class="bg-zinc-900">Meseros</option>
                    <option value="Barra Principal" class="bg-zinc-900">Barra Principal</option>
                    <option value="Barra Subterráneo" class="bg-zinc-900">Barra Subterráneo</option>
                    <option value="Seguridad" class="bg-zinc-900">Seguridad</option>
                    <option value="Tienda" class="bg-zinc-900">Tienda</option>
                    <option value="General" class="bg-zinc-900">General</option>
                </select>
            </div>
        </div>

        <!-- Días que Asiste de Turno -->
        <div class="p-3.5 rounded-xl bg-zinc-900/80 border border-zinc-800 space-y-2">
            <span class="block text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Días de Turno Programados</span>
            <div class="grid grid-cols-3 gap-2 pt-1">
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_friday" value="1" {{ old('works_friday', true) ? 'checked' : '' }} 
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Viernes</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_saturday" value="1" {{ old('works_saturday', true) ? 'checked' : '' }} 
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Sábado</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 cursor-pointer transition-all">
                    <input type="checkbox" name="works_sunday" value="1" {{ old('works_sunday', true) ? 'checked' : '' }} 
                           class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-[#F5B81C] focus:ring-0 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-200">Domingo</span>
                </label>
            </div>
        </div>

        <!-- Tarifas de Pago por Noche -->
        <div>
            <label class="block text-xs font-bold text-zinc-300 mb-1.5">
                Tarifas de Pago por Noche (Bs.)
            </label>
            <div class="grid grid-cols-4 gap-2">
                <div>
                    <label for="default_pay" class="block text-[10px] font-mono text-zinc-400 mb-1">Base</label>
                    <input type="number" step="5" name="default_pay" id="default_pay" required value="{{ old('default_pay', 100) }}"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="friday_pay" class="block text-[10px] font-mono text-zinc-400 mb-1">Vie</label>
                    <input type="number" step="5" name="friday_pay" id="friday_pay" value="{{ old('friday_pay', 100) }}"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="saturday_pay" class="block text-[10px] font-mono text-zinc-400 mb-1">Sáb</label>
                    <input type="number" step="5" name="saturday_pay" id="saturday_pay" value="{{ old('saturday_pay', 110) }}"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
                <div>
                    <label for="sunday_pay" class="block text-[10px] font-mono text-zinc-400 mb-1">Dom</label>
                    <input type="number" step="5" name="sunday_pay" id="sunday_pay" value="{{ old('sunday_pay', 110) }}"
                           class="w-full px-2.5 py-2 rounded-xl text-xs font-mono font-bold bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                </div>
            </div>
        </div>

        <div>
            <label for="phone" class="block text-xs font-bold text-zinc-300 mb-1.5">
                Teléfono / Celular (Opcional)
            </label>
            <div class="flex items-center rounded-xl bg-zinc-900 border border-zinc-800 px-3.5 py-2 focus-within:border-[#F5B81C] transition-all">
                <svg class="w-4 h-4 text-zinc-500 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="Ej. 70012345"
                       class="w-full text-sm font-mono bg-transparent border-0 text-white placeholder-zinc-500 focus:outline-none p-0">
            </div>
        </div>

        <div class="pt-5 border-t border-zinc-800 flex items-center justify-end gap-2.5">
            <a href="{{ route('staff.index') }}" 
               class="px-4 py-2 rounded-xl border border-zinc-800 text-zinc-400 hover:text-white text-xs font-bold transition-all">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-5 py-2 rounded-xl bg-[#F5B81C] hover:bg-[#e5ac18] text-zinc-950 text-xs font-black uppercase tracking-wider transition-all cursor-pointer shadow-sm">
                Guardar Personal
            </button>
        </div>
    </form>
</div>
@endsection
