@extends('layouts.app')

@section('title', 'Servidor')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12 w-full">

    <!-- Cabecera Limpia -->
    <div class="pb-3 border-b border-zinc-800">
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Servidor</h1>
        <p class="text-xs text-zinc-400 mt-1">
            Activa esta opción para abrir el sistema en tu celular.
        </p>
    </div>

    <!-- Tarjeta Principal con Control Único -->
    <div class="p-6 rounded-2xl border bg-black border-zinc-800 space-y-6">
        
        <!-- Alerta de Configuración Inicial (si no hay token) -->
        <div id="token-card" class="{{ $hasToken ? 'hidden' : 'block' }} p-4 rounded-xl bg-zinc-950 border border-amber-500/30 space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#F5B81C]"></span>
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Configuración inicial</h3>
            </div>
            <p class="text-xs text-zinc-400">
                Pega tu clave de acceso para activar el enlace móvil en esta computadora:
            </p>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <input type="text" 
                       id="token-input" 
                       placeholder="Pega la clave aquí..." 
                       class="flex-1 px-3.5 py-2.5 rounded-xl bg-black border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-[#F5B81C]">
                <button type="button" 
                        onclick="saveToken()" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold bg-[#F5B81C] text-black hover:bg-[#e5ac18] transition-all cursor-pointer shrink-0 active:scale-95">
                    Guardar y Activar
                </button>
            </div>
        </div>

        <!-- Fila del Switch Único -->
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span id="status-dot" class="w-2.5 h-2.5 rounded-full {{ $isRunning ? 'bg-emerald-400 animate-pulse' : 'bg-zinc-600' }}"></span>
                    <h2 class="text-base font-bold text-white">Ver en Celular</h2>
                </div>
                <p id="status-desc" class="text-xs text-zinc-400 mt-0.5">
                    {{ $isRunning ? 'Activo. El sistema está disponible para tu celular.' : 'Apagado. El sistema solo funciona en esta computadora.' }}
                </p>
            </div>

            <!-- ÚNICO CONTROL: Switch iOS -->
            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                <input type="checkbox" id="server-switch" onchange="toggleServer()" class="sr-only peer" {{ $isRunning ? 'checked' : '' }}>
                <div class="w-14 h-7 bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#F5B81C]"></div>
            </label>
        </div>

        <!-- Contenido cuando está Activo: Enlace y QR -->
        <div id="active-box" class="{{ $isRunning ? 'block' : 'hidden' }} pt-4 border-t border-zinc-800/80 space-y-6">
            
            <!-- Campo de Enlace + Botón Copiar -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-zinc-300">Enlace directo</label>
                <div class="flex items-center gap-2">
                    <input type="text" 
                           id="public-url-input" 
                           readonly 
                           value="{{ $publicUrl }}" 
                           class="flex-1 px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white select-all focus:outline-none">
                    <button type="button" 
                            onclick="copyUrl()" 
                            class="px-4 py-2.5 rounded-xl text-xs font-bold bg-[#F5B81C] text-black hover:bg-[#e5ac18] transition-all cursor-pointer shrink-0 active:scale-95">
                        Copiar
                    </button>
                </div>
            </div>

            <!-- Código QR Centrado -->
            <div class="flex flex-col items-center justify-center p-6 rounded-2xl bg-zinc-950 border border-zinc-800 text-center space-y-3">
                <div class="p-3 bg-white rounded-2xl shadow-xl shrink-0">
                    <img id="qr-img" 
                         src="{{ $publicUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($publicUrl) : '' }}" 
                         alt="QR Celular" 
                         class="w-48 h-48 object-contain rounded-lg">
                </div>
                <p class="text-xs text-zinc-400">
                    Apunta con la cámara de tu celular al código para entrar directo.
                </p>
            </div>

        </div>

    </div>

</div>

<!-- Toast Sutil -->
<div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full text-xs font-medium text-white bg-zinc-900 border border-zinc-700 shadow-xl opacity-0 pointer-events-none transition-all duration-300">
    <span id="toast-text">Notificación</span>
</div>

<script>
let isRunning = {{ $isRunning ? 'true' : 'false' }};
let hasToken = {{ $hasToken ? 'true' : 'false' }};
let isBusy = false;

function showToast(text) {
    const toast = document.getElementById('toast');
    const toastText = document.getElementById('toast-text');
    if (!toast || !toastText) return;
    toastText.textContent = text;
    toast.classList.remove('opacity-0', 'pointer-events-none');
    setTimeout(() => {
        toast.classList.add('opacity-0', 'pointer-events-none');
    }, 2400);
}

function copyUrl() {
    const input = document.getElementById('public-url-input');
    if (!input || !input.value) return;
    navigator.clipboard.writeText(input.value).then(() => {
        showToast('Enlace copiado');
    }).catch(() => {
        input.select();
        document.execCommand('copy');
        showToast('Enlace copiado');
    });
}

async function saveToken() {
    const input = document.getElementById('token-input');
    const tokenVal = input ? input.value.trim() : '';
    if (!tokenVal) {
        showToast('Por favor escribe o pega la clave');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    try {
        const res = await fetch("{{ route('server.save_token') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ token: tokenVal })
        });
        const data = await res.json();
        if (data.success) {
            hasToken = true;
            document.getElementById('token-card')?.classList.add('hidden');
            showToast('Clave guardada con éxito');
            // Intentar encender
            toggleServer();
        } else {
            showToast(data.message || 'Error al guardar');
        }
    } catch(e) {
        showToast('Error de conexión al guardar');
    }
}

async function toggleServer() {
    if (isBusy) return;
    
    if (!hasToken) {
        document.getElementById('token-card')?.classList.remove('hidden');
        document.getElementById('token-input')?.focus();
        showToast('Ingresa la clave para activar');
        const switchEl = document.getElementById('server-switch');
        if (switchEl) switchEl.checked = false;
        return;
    }

    isBusy = true;
    const switchEl = document.getElementById('server-switch');
    const statusDesc = document.getElementById('status-desc');
    statusDesc.textContent = switchEl.checked ? 'Conectando...' : 'Desconectando...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const response = await fetch("{{ route('server.toggle') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            isRunning = !isRunning;
            applyUI(isRunning, data.public_url);
            showToast(isRunning ? 'Conexión activada' : 'Conexión apagada');
        } else {
            if (data.needs_token) {
                hasToken = false;
                document.getElementById('token-card')?.classList.remove('hidden');
            }
            switchEl.checked = isRunning;
            applyUI(isRunning, null);
            showToast(data.message || 'Error al conectar');
        }
    } catch (e) {
        switchEl.checked = isRunning;
        applyUI(isRunning, null);
        showToast('Error de conexión');
    } finally {
        isBusy = false;
    }
}

function applyUI(running, url) {
    const switchEl = document.getElementById('server-switch');
    const dot = document.getElementById('status-dot');
    const desc = document.getElementById('status-desc');
    const box = document.getElementById('active-box');
    const input = document.getElementById('public-url-input');
    const qr = document.getElementById('qr-img');

    if (switchEl) switchEl.checked = running;

    if (running) {
        if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse';
        if (desc) desc.textContent = 'Activo. El sistema está disponible para tu celular.';
        if (url) {
            if (input) input.value = url;
            if (qr) qr.src = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(url);
        }
        if (box) box.classList.remove('hidden');
    } else {
        if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-zinc-600';
        if (desc) desc.textContent = 'Apagado. El sistema solo funciona en esta computadora.';
        if (box) box.classList.add('hidden');
    }
}
</script>
@endsection
