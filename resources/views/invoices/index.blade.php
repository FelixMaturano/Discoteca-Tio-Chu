@extends('layouts.app')

@section('title', 'Facturas & Tarjetas')

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
         1. CABECERA PRINCIPAL: TÍTULO & SELECTOR DE NOCHE
         ======================================================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-5 py-3.5 rounded-2xl theme-card border theme-border">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white font-sans">
                Facturas & Tarjetas (Tarjeteos)
            </h1>
            <p class="text-xs text-zinc-400 mt-0.5">
                Control de facturas emitidas y cobros pasados por máquina de tarjeta
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <!-- Selector de Noche -->
            @if($allSessions->isNotEmpty())
                <form method="GET" action="{{ route('invoices.index') }}" class="flex items-center">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs hover:border-[#F5B81C] transition-colors">
                        <svg class="w-3.5 h-3.5 text-[#F5B81C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <select name="session_id" id="session_id" onchange="this.form.submit()" 
                                class="bg-transparent border-0 text-xs font-semibold text-white focus:outline-none cursor-pointer pr-1">
                            @foreach($allSessions as $s)
                                <option value="{{ $s->id }}" {{ $session && $session->id === $s->id ? 'selected' : '' }} class="bg-zinc-950 text-white">
                                    {{ $s->day_name }} {{ \Carbon\Carbon::parse($s->session_date)->format('d/m/Y') }} ({{ $s->isOpen() ? 'En Vivo' : 'Cerrada' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif

            <!-- Estado Noche Cerrada (Discreto y sin alarmas) -->
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
        </div>
    </div>

    @if(!$session)
        <!-- Estado Vacío -->
        <div class="p-12 text-center rounded-2xl theme-card border theme-border">
            <h3 class="text-sm font-bold uppercase tracking-wider text-white">No hay ninguna noche abierta o seleccionada</h3>
            <p class="text-xs text-zinc-400 mt-1 mb-4">Apertura una noche para registrar facturas y cobros con tarjeta.</p>
            <a href="{{ route('sessions.create') }}" class="px-4 py-2 rounded-xl bg-[#F5B81C] text-black font-bold text-xs hover:bg-[#e5ac18] transition-all dilemo-btn inline-block">
                + Aperturar Nueva Noche
            </a>
        </div>
    @else

        <!-- ========================================================
             2. MÉTRICAS CLARAS Y DIRECTAS PARA DON LUDO
             ======================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3.5">
            
            <!-- 1. Pasado por Tarjeta (Bruto) -->
            <div class="theme-card rounded-2xl p-3.5 sm:p-5 border theme-border">
                <span class="text-[11px] sm:text-xs font-semibold text-zinc-400 uppercase tracking-wider block truncate">Pasado por Tarjeta</span>
                <div class="mt-1 sm:mt-2 text-lg sm:text-2xl font-black font-mono text-white tracking-tight truncate">
                    <span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>{{ number_format($totalTarjeta, 2) }}
                </div>
                <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-0.5 sm:mt-1 truncate">Vouchers pasados</p>
            </div>

            <!-- 2. Comisión del Banco -->
            <div class="theme-card rounded-2xl p-3.5 sm:p-5 border theme-border">
                <span class="text-[11px] sm:text-xs font-semibold text-zinc-400 uppercase tracking-wider block truncate">
                    Comisión Banco ({{ number_format(($session->pos_commission_rate ?? 0.035) * 100, 1) }}%)
                </span>
                <div class="mt-1 sm:mt-2 text-lg sm:text-2xl font-black font-mono text-rose-400 tracking-tight truncate">
                    - <span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>{{ number_format($totalTarjetaComision, 2) }}
                </div>
                <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-0.5 sm:mt-1 truncate">Retención banco</p>
            </div>

            <!-- 3. Plata que Entra al Banco (Neto) -->
            <div class="theme-card rounded-2xl p-3.5 sm:p-5 border theme-border">
                <span class="text-[11px] sm:text-xs font-semibold text-zinc-400 uppercase tracking-wider block truncate">Neto al Banco</span>
                <div class="mt-1 sm:mt-2 text-lg sm:text-2xl font-black font-mono text-[#F5B81C] tracking-tight truncate">
                    <span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>{{ number_format($totalTarjetaNeto, 2) }}
                </div>
                <p class="text-[10px] sm:text-[11px] text-zinc-400 mt-0.5 sm:mt-1 truncate">Dinero neto acreditado</p>
            </div>

            <!-- 4. Facturas en Efectivo -->
            <div class="theme-card rounded-2xl p-3.5 sm:p-5 border theme-border">
                <span class="text-[11px] sm:text-xs font-semibold text-zinc-400 uppercase tracking-wider block truncate">Facturas en Efectivo</span>
                <div class="mt-1 sm:mt-2 text-lg sm:text-2xl font-black font-mono text-white tracking-tight truncate">
                    <span class="text-xs sm:text-sm mr-0.5 font-sans font-bold">Bs.</span>{{ number_format($totalEfectivo, 2) }}
                </div>
                <p class="text-[10px] sm:text-[11px] text-zinc-500 mt-0.5 sm:mt-1 truncate">Recibido en mano</p>
            </div>

        </div>

        <!-- ========================================================
             3. FORMULARIO DE REGISTRO & TABLA DE HISTORIAL
             ======================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- Panel Izquierdo: Formulario Rápido Rediseñado -->
            <div class="theme-card rounded-2xl p-5 border theme-border h-fit">
                <div class="flex items-center justify-between border-b theme-border pb-3 mb-4">
                    <div>
                        <h2 class="text-sm font-black text-white uppercase tracking-wider font-sans">
                            Registrar Factura
                        </h2>
                        <p class="text-[11px] text-zinc-400 mt-0.5">
                            Control de vouchers y comprobantes
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-zinc-950 border border-zinc-800 shrink-0" title="Número autoincremental automático">
                        <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider">N°</span>
                        <span class="text-xs font-mono font-black text-[#F5B81C]">#{{ $nextCorrelative }}</span>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('invoices.store') }}" class="space-y-4 {{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }}">
                    @csrf
                    <input type="hidden" name="night_session_id" value="{{ $session->id }}">
                    <input type="hidden" name="correlative_num" value="{{ $nextCorrelative }}">

                    <!-- Forma de Pago: Tarjeta por defecto -->
                    <input type="hidden" name="payment_method" id="payment_method_input" value="tarjeta">

                    <!-- 2. Barra (Píldoras Switch Modo iPhone) -->
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-400 uppercase tracking-wider mb-1.5">Barra</label>
                        <input type="hidden" name="bar_name" id="bar_name_input" value="Principal">
                        <div class="grid grid-cols-3 p-1 rounded-xl bg-zinc-950 border border-zinc-800 gap-1">
                            <button type="button"
                                    id="btn-bar-Principal"
                                    onclick="selectBar('Principal')"
                                    class="py-1.5 px-2 rounded-lg text-xs font-black transition-all duration-200 cursor-pointer bg-[#F5B81C] text-black shadow-[0_0_12px_rgba(245,184,28,0.35)] text-center truncate active:scale-95">
                                Principal
                            </button>
                            <button type="button"
                                    id="btn-bar-Subterraneo"
                                    onclick="selectBar('Subterraneo')"
                                    class="py-1.5 px-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer text-zinc-400 hover:text-white text-center truncate active:scale-95">
                                Subterráneo
                            </button>
                            <button type="button"
                                    id="btn-bar-Tienda"
                                    onclick="selectBar('Tienda')"
                                    class="py-1.5 px-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer text-zinc-400 hover:text-white text-center truncate active:scale-95">
                                Tienda
                            </button>
                        </div>
                    </div>

                    <!-- 3. Monto Cobrado (Compacto y Prominente) -->
                    <div>
                        <label for="amount" class="block text-[11px] font-bold text-zinc-400 uppercase tracking-wider mb-1.5">Monto Cobrado</label>
                        <div class="inline-flex items-center rounded-xl bg-zinc-950 border border-zinc-800 px-3.5 py-1.5 focus-within:border-[#F5B81C] focus-within:ring-1 focus-within:ring-[#F5B81C]/40 transition-all">
                            <span class="text-sm font-mono font-bold text-[#F5B81C] mr-2 shrink-0">Bs.</span>
                            <input type="number" step="0.5" inputmode="decimal" name="amount" id="amount" required placeholder="0.00"
                                   class="w-36 text-xl font-mono font-black bg-transparent border-0 text-white placeholder-zinc-600 focus:outline-none p-0">
                        </div>
                    </div>

                    <!-- 4. Notas o N° Voucher (Desplegable y Opcional) -->
                    <div class="pt-1 border-t border-zinc-800/60">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-zinc-400">Notas (opcional)</span>
                            <button type="button" id="toggle-notes-btn" onclick="toggleNotesField()" class="inline-flex items-center gap-1 text-[11px] text-[#F5B81C] hover:text-[#e5ac18] font-bold transition-colors cursor-pointer">
                                <svg id="notes-plus-icon" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span id="notes-btn-text">Agregar</span>
                            </button>
                        </div>
                        <div id="notes-field-container" class="hidden mt-2 transition-all duration-200">
                            <input type="text" name="notes" id="notes" placeholder="Ej. Voucher #1234, Tarjeta BCP..."
                                   class="w-full text-xs rounded-xl px-3 py-2 bg-zinc-950 border border-zinc-800 text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C]">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-[#F5B81C] text-black font-black text-xs uppercase tracking-wider rounded-xl hover:bg-[#e5ac18] active:scale-95 transition-all dilemo-btn cursor-pointer shadow-sm">
                        + Registrar Factura
                    </button>
                </form>
            </div>

            <!-- Panel Derecho: Tabla de Historial -->
            <div class="lg:col-span-2 rounded-2xl theme-card border theme-border overflow-hidden">
                <div class="px-5 py-3.5 border-b theme-border bg-zinc-950 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-300 font-sans">
                        Historial de Facturas Emitidas ({{ $invoices->count() }})
                    </h3>
                    <span class="text-xs font-mono font-bold text-[#F5B81C]">
                        Total Facturado: Bs. {{ number_format($totalGeneral, 2) }}
                    </span>
                </div>

                <div class="overflow-x-auto max-h-[520px]">
                    <table class="w-full min-w-[560px] text-xs text-left border-collapse">
                        <thead class="bg-zinc-950 border-b theme-border text-zinc-400 uppercase text-[11px] tracking-wider sticky top-0 font-semibold">
                            <tr>
                                <th class="px-3 py-3 w-12 text-center">N°</th>
                                <th class="px-3.5 py-3">Forma Pago</th>
                                <th class="px-3.5 py-3">Barra</th>
                                <th class="px-3.5 py-3 text-right">Monto Bruto</th>
                                <th class="px-3.5 py-3 text-right">Comisión Banco</th>
                                <th class="px-3.5 py-3 text-right">Neto al Banco</th>
                                <th class="px-3.5 py-3">Notas</th>
                                <th class="px-3 py-3 text-right w-16">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y theme-border font-sans" id="invoices-table-body">
                            @forelse($invoices as $inv)
                                <tr id="invoice-row-{{ $inv->id }}" class="hover:bg-zinc-900/30 transition-all duration-200">
                                    <td class="px-3 py-2.5 text-center font-bold font-mono text-zinc-300">#{{ $inv->correlative_num }}</td>
                                    <td class="px-3.5 py-2.5">
                                        @if($inv->payment_method === 'tarjeta')
                                            <span class="inline-flex items-center gap-1 text-[#F5B81C] font-semibold text-xs">
                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                Tarjeta
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-zinc-300 font-semibold text-xs">
                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                Efectivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2.5 text-zinc-300 font-medium">
                                        {{ $inv->bar_name ?? 'Principal' }}
                                    </td>
                                    <td class="px-3.5 py-2.5 text-right font-bold font-mono text-white">
                                        Bs. {{ number_format($inv->amount, 2) }}
                                    </td>
                                    <td class="px-3.5 py-2.5 text-right font-mono text-rose-400 font-semibold">
                                        {{ $inv->commission_amount > 0 ? '- Bs. ' . number_format($inv->commission_amount, 2) : '—' }}
                                    </td>
                                    <td class="px-3.5 py-2.5 text-right font-black font-mono text-[#F5B81C]">
                                        Bs. {{ number_format($inv->net_amount, 2) }}
                                    </td>
                                    <td class="px-3.5 py-2.5 text-zinc-400 text-[11px] truncate max-w-[140px]">{{ $inv->notes ?? '—' }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <button type="button" 
                                                onclick="deleteInvoice({{ $inv->id }})" 
                                                class="text-rose-400 hover:text-rose-300 font-semibold text-xs cursor-pointer transition-colors active:scale-95 {{ !$session->isOpen() ? 'pointer-events-none opacity-50' : '' }}">
                                            Borrar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="empty-invoices-row">
                                    <td colspan="8" class="px-4 py-8 text-center text-zinc-500 font-sans">
                                        No hay facturas registradas en esta noche.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    @endif

</div>

<script>
/* --- CONTROL DE BARRA (PÍLDORAS MODO IPHONE) --- */
function selectBar(bar) {
    document.getElementById('bar_name_input').value = bar;
    const bars = ['Principal', 'Subterraneo', 'Tienda'];
    bars.forEach(b => {
        const btn = document.getElementById('btn-bar-' + b);
        if (btn) {
            if (b === bar) {
                btn.className = 'py-1.5 px-2 rounded-lg text-xs font-black transition-all duration-200 cursor-pointer bg-[#F5B81C] text-black shadow-[0_0_12px_rgba(245,184,28,0.35)] text-center truncate active:scale-95';
            } else {
                btn.className = 'py-1.5 px-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer text-zinc-400 hover:text-white text-center truncate active:scale-95';
            }
        }
    });
}

/* --- TOGGLE DE CAMPO NOTAS / VOUCHER --- */
function toggleNotesField() {
    const container = document.getElementById('notes-field-container');
    const input = document.getElementById('notes');
    const btnText = document.getElementById('notes-btn-text');
    const icon = document.getElementById('notes-plus-icon');

    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        input.focus();
        btnText.textContent = 'Ocultar';
        icon.style.transform = 'rotate(45deg)';
    } else {
        container.classList.add('hidden');
        btnText.textContent = 'Agregar';
        icon.style.transform = 'rotate(0deg)';
    }
}

/* --- ELIMINACIÓN ASINCRÓNICA SIN PARPADEO NI BANNERS VERDES --- */
async function deleteInvoice(id) {
    if (!confirm('¿Eliminar esta factura definitivamente?')) return;
    const row = document.getElementById('invoice-row-' + id);
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const res = await fetch('{{ url("/invoices") }}/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (res.ok) {
            if (row) {
                row.style.transition = 'all 0.25s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(14px)';
                setTimeout(() => {
                    row.remove();
                    window.location.reload();
                }, 220);
            } else {
                window.location.reload();
            }
        } else {
            window.location.reload();
        }
    } catch (err) {
        window.location.reload();
    }
}
</script>
@endsection
