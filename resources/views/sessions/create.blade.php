@extends('layouts.app')

@section('title', 'Nueva Noche')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-zinc-800/80 pb-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono font-bold text-[#F5B81C] uppercase tracking-wider">
                    Apertura Operativa
                </span>
                <span class="text-zinc-600 text-xs">•</span>
                <span class="text-xs text-zinc-400 font-mono">Nueva Jornada</span>
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight">Aperturar Nueva Noche</h2>
            <p class="text-xs text-zinc-400 mt-1">Inicializa automáticamente los inventarios de las 3 barras y la planilla de asistencia.</p>
        </div>
        <a href="{{ route('sessions.index') }}" 
           class="px-3.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white text-xs font-semibold flex items-center gap-1.5 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Volver</span>
        </a>
    </div>

    @if(isset($openSession) && $openSession)
        <div class="p-4 rounded-2xl border border-amber-500/30 bg-amber-500/10 shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-[#F5B81C] flex items-center justify-center font-bold text-sm shrink-0 border border-amber-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-black text-[#F5B81C] uppercase tracking-wider font-mono">Jornada Anterior Aún Abierta</h4>
                    <p class="text-xs text-zinc-300 mt-0.5">
                        La noche del <strong class="text-white">{{ $openSession->day_name }} ({{ \Carbon\Carbon::parse($openSession->session_date)->format('d/m/Y') }})</strong> está en curso. Debes cerrarla antes de aperturar una nueva.
                    </p>
                </div>
            </div>
            <a href="{{ route('closing.index', ['session_id' => $openSession->id]) }}" 
               class="px-4 py-2 bg-[#F5B81C] hover:bg-[#e5ac18] text-zinc-950 font-black text-xs uppercase tracking-wider rounded-xl transition-all shadow-sm whitespace-nowrap">
                Ir a Cerrar Noche
            </a>
        </div>
    @endif

    <!-- Form Panel -->
    <form method="POST" action="{{ route('sessions.store') }}" class="theme-card p-6 sm:p-8 rounded-2xl border border-zinc-800/80 bg-[#09090b] shadow-xl space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="session_date" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2 font-mono">
                    Fecha de la Noche <span class="text-[#F5B81C]">*</span>
                </label>
                <input type="date" name="session_date" id="session_date" required 
                       value="{{ old('session_date', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-sm font-mono text-white focus:outline-none focus:border-[#F5B81C] transition-all [color-scheme:dark]">
                <p class="text-[11px] text-zinc-500 mt-1.5">Inicio de la jornada nocturna.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2 font-mono">
                    Día Detectado (Automático)
                </label>
                <div class="flex items-center h-[42px] px-4 rounded-xl bg-zinc-950 border border-zinc-800 text-sm font-bold text-[#F5B81C] font-mono" id="detected_day_display">
                    Calculando...
                </div>
                <p class="text-[11px] text-zinc-500 mt-1.5">Fija la tarifa y turnos del personal.</p>
            </div>
        </div>

        <div>
            <label for="pos_commission_rate" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2 font-mono">
                Tasa de Comisión Tarjetero / POS (Decimal) <span class="text-[#F5B81C]">*</span>
            </label>
            <div class="flex items-center gap-3">
                <input type="number" step="0.0001" inputmode="decimal" name="pos_commission_rate" id="pos_commission_rate" required value="{{ old('pos_commission_rate', 0.0130) }}"
                       class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-sm font-mono font-bold text-white focus:outline-none focus:border-[#F5B81C] transition-all">
                <span class="text-xs font-mono font-bold text-[#F5B81C] whitespace-nowrap bg-zinc-950 px-3.5 py-2.5 rounded-xl border border-zinc-800">
                    = 1.30%
                </span>
            </div>
            <p class="text-[11px] text-zinc-500 mt-1.5 font-mono">Por defecto 0.0130 (1.30% bancario estándar).</p>
        </div>

        <div>
            <label for="notes" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2 font-mono">
                Notas del Evento (Opcional)
            </label>
            <textarea name="notes" id="notes" rows="2" placeholder="Ej. Fiesta temática, artista invitado, feriado..."
                      class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C] transition-all font-sans">{{ old('notes') }}</textarea>
        </div>

        <div class="pt-5 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5 sm:gap-3 border-t border-zinc-800/80">
            <a href="{{ route('sessions.index') }}" 
               class="w-full sm:w-auto text-center px-4 py-2.5 rounded-xl border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-white text-xs font-bold transition-all">
                Cancelar
            </a>
            <button type="submit" 
                    class="w-full sm:w-auto px-5 py-2.5 bg-[#F5B81C] hover:bg-[#e5ac18] text-zinc-950 text-xs font-black tracking-wider uppercase rounded-xl transition-all shadow-sm active:scale-95">
                Aperturar Noche
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('session_date');
    const dayDisplay = document.getElementById('detected_day_display');
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    function updateDay() {
        if (!dateInput.value) return;
        const parts = dateInput.value.split('-');
        if (parts.length === 3) {
            const dateObj = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            const dayName = dias[dateObj.getDay()];
            dayDisplay.textContent = dayName;
        }
    }

    dateInput.addEventListener('input', updateDay);
    dateInput.addEventListener('change', updateDay);
    updateDay();
});
</script>
@endsection
