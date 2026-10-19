<!-- Script previo a renderizado para evitar parpadeo (FOUC) del sidebar al recargar -->
<script>
    (function() {
        var saved = localStorage.getItem('tiochu_sidebar_collapsed');
        if (saved === 'true' || (saved === null && window.innerWidth < 1024)) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    })();
</script>

<style>
    /* Transición suave para expandir / contraer */
    #main-sidebar {
        transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Estado colapsado (Solo Logos) */
    html.sidebar-collapsed #main-sidebar {
        width: 4.25rem !important;
        min-width: 4.25rem !important;
        max-width: 4.25rem !important;
    }

    @media (max-width: 640px) {
        html.sidebar-collapsed #main-sidebar {
            width: 3.5rem !important;
            min-width: 3.5rem !important;
            max-width: 3.5rem !important;
        }
    }

    @media (max-width: 768px) {
        html:not(.sidebar-collapsed) #main-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            z-index: 50 !important;
            box-shadow: 0 0 50px rgba(0,0,0,0.9) !important;
        }
    }
    html.sidebar-collapsed .sidebar-text,
    html.sidebar-collapsed .sidebar-category,
    html.sidebar-collapsed .sidebar-header-expanded,
    html.sidebar-collapsed .sidebar-user-expanded,
    html.sidebar-collapsed .sidebar-dot {
        display: none !important;
    }
    html.sidebar-collapsed .sidebar-header-collapsed,
    html.sidebar-collapsed .sidebar-user-collapsed,
    html.sidebar-collapsed .sidebar-divider {
        display: flex !important;
    }
    html.sidebar-collapsed .sidebar-item {
        justify-content: center !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    html.sidebar-collapsed .sidebar-item > div {
        justify-content: center !important;
    }

    /* Scrollbar sutil y elegante cuando la pantalla se hace pequeña o con zoom */
    #main-sidebar nav::-webkit-scrollbar {
        width: 3px;
    }
    #main-sidebar nav::-webkit-scrollbar-track {
        background: transparent;
    }
    #main-sidebar nav::-webkit-scrollbar-thumb {
        background: #27272a;
        border-radius: 4px;
    }
    #main-sidebar nav::-webkit-scrollbar-thumb:hover {
        background: #F5B81C;
    }
</style>

<!-- Sidebar Tío Chu: Colapsable, Sin Relleno "Club & Bar", Letras Grandes & Sin Scroll -->
<aside id="main-sidebar" 
       class="w-64 h-screen max-h-screen sticky top-0 flex flex-col justify-between shrink-0 overflow-hidden z-30 select-none bg-black border-r border-zinc-800/80 {{ session('animate_entrance') ? 'animate-entrance-sidebar' : '' }}" 
       style="width: 16rem; min-width: 16rem; max-width: 16rem; height: 100vh;">
    
    <!-- 1. Cabecera con Menú Hamburguesa -->
    <div class="px-2.5 py-2.5 border-b border-zinc-800/80 shrink-0">
        
        <!-- Cabecera Expandida -->
        <div class="sidebar-header-expanded p-2 rounded-xl bg-zinc-950/80 border border-zinc-800/90 flex items-center justify-between">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 shrink-0 rounded-lg overflow-hidden flex items-center justify-center bg-zinc-900 border border-zinc-800" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px;">
                    <img src="{{ asset('images/LogoTioChu.png') }}" 
                         alt="Logo Tío Chu" 
                         class="w-full h-full object-contain p-0.5"
                         style="width: 32px; height: 32px; object-fit: contain;">
                </div>
                <span class="text-sm font-black tracking-tight text-white block truncate uppercase font-sans">TÍO CHU</span>
            </div>

            <!-- Botón Hamburguesa (Hover Amarillo) -->
            <button id="toggle-sidebar-btn" 
                    type="button" 
                    title="Colapsar menú"
                    class="group/btn p-1.5 rounded-lg text-zinc-400 hover:text-[#F5B81C] hover:bg-[#F5B81C]/10 transition-colors duration-150 cursor-pointer shrink-0">
                <svg class="w-5 h-5 transition-transform duration-150 group-hover/btn:scale-105" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <line x1="4" x2="20" y1="6" y2="6"/>
                    <line x1="4" x2="20" y1="12" y2="12"/>
                    <line x1="4" x2="20" y1="18" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Cabecera Colapsada (Solo Hamburguesa + Mini Logo) -->
        <div class="sidebar-header-collapsed hidden flex-col items-center justify-center gap-2 py-1">
            <button id="toggle-sidebar-btn-collapsed" 
                    type="button" 
                    title="Expandir menú"
                    class="group/btn p-2 rounded-lg text-zinc-400 hover:text-[#F5B81C] hover:bg-[#F5B81C]/10 transition-colors duration-150 cursor-pointer">
                <svg class="w-5 h-5 transition-transform duration-150 group-hover/btn:scale-105" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <line x1="4" x2="20" y1="6" y2="6"/>
                    <line x1="4" x2="20" y1="12" y2="12"/>
                    <line x1="4" x2="20" y1="18" y2="18"/>
                </svg>
            </button>
            <div class="w-7 h-7 rounded-lg overflow-hidden flex items-center justify-center bg-zinc-900 border border-zinc-800" style="width: 28px; height: 28px; min-width: 28px; min-height: 28px;">
                <img src="{{ asset('images/LogoTioChu.png') }}" 
                     alt="Logo Tío Chu" 
                     class="w-full h-full object-contain p-0.5"
                     style="width: 28px; height: 28px; object-fit: contain;">
            </div>
        </div>

    </div>

    <!-- 2. Menú de Navegación Compacto: Cabe en 100vh y se desplaza suavemente si hay zoom -->
    <nav class="flex-1 px-2 py-2 space-y-2 overflow-y-auto overflow-x-hidden min-h-0 flex flex-col">
        
        <!-- Bloque 1: Operación -->
        <div class="space-y-1">
            <div class="sidebar-category px-2">
                <span class="text-[9px] font-black uppercase tracking-widest text-[#F5B81C]/90">
                    Operación
                </span>
            </div>
            <div class="sidebar-divider hidden w-6 mx-auto my-1 border-t border-zinc-800/80"></div>

            <!-- Resumen General -->
            <a href="{{ route('dashboard') }}" 
               title="Resumen General"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('dashboard') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="9" x="3" y="3" rx="1.5"/>
                        <rect width="7" height="5" x="14" y="3" rx="1.5"/>
                        <rect width="7" height="9" x="14" y="12" rx="1.5"/>
                        <rect width="7" height="5" x="3" y="16" rx="1.5"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Resumen General</span>
                </div>
                @if(request()->routeIs('dashboard'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Bodega Central -->
            <a href="{{ route('products.index') }}" 
               title="Bodega Central"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('products.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('products.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m16.5 9.4-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.29 7 12 12 20.71 7"/>
                        <line x1="12" x2="12" y1="22" y2="12"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Bodega Central</span>
                </div>
                @if(request()->routeIs('products.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Inventario Barras -->
            <a href="{{ route('barInventory.index') }}" 
               title="Inventario Barras"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('barInventory.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('barInventory.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 22h8"/>
                        <path d="M12 15v7"/>
                        <path d="m19 3-7 8-7-8h14z"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Inventario Barras</span>
                </div>
                @if(request()->routeIs('barInventory.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Ventas por Barra -->
            <a href="{{ route('sales.index') }}" 
               title="Ventas por Barra"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('sales.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('sales.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" x2="18" y1="20" y2="10"/>
                        <line x1="12" x2="12" y1="20" y2="4"/>
                        <line x1="6" x2="6" y1="20" y2="14"/>
                        <line x1="2" x2="22" y1="20" y2="20"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Ventas por Barra</span>
                </div>
                @if(request()->routeIs('sales.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>
        </div>

        <!-- Bloque 2: Caja y Cobros -->
        <div class="space-y-1">
            <div class="sidebar-category px-2">
                <span class="text-[9px] font-black uppercase tracking-widest text-[#F5B81C]/90">
                    Caja
                </span>
            </div>
            <div class="sidebar-divider hidden w-6 mx-auto my-1 border-t border-zinc-800/80"></div>

            <!-- Facturas & Tarjetas (Tarjeteos) -->
            <a href="{{ route('invoices.index') }}" 
               title="Facturas & Tarjetas"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('invoices.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('invoices.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"/>
                        <line x1="2" x2="22" y1="10" y2="10"/>
                        <path d="M6 15h4"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Facturas & Tarjetas</span>
                </div>
                @if(request()->routeIs('invoices.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>
            
            <!-- Cobros QR -->
            <a href="{{ route('qrs.index') }}" 
               title="Cobros QR"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('qrs.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('qrs.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="5" height="5" x="3" y="3" rx="1"/>
                        <rect width="5" height="5" x="16" y="3" rx="1"/>
                        <rect width="5" height="5" x="3" y="16" rx="1"/>
                        <path d="M21 16h-3a2 2 0 0 0-2 2v3"/>
                        <path d="M21 21v.01"/>
                        <path d="M12 7v3a2 2 0 0 1-2 2H7"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Cobros QR</span>
                </div>
                @if(request()->routeIs('qrs.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Cierre de Caja -->
            <a href="{{ route('closing.index') }}" 
               title="Cierre de Caja"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('closing.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('closing.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="3"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path d="m14.5 9.5-5 5"/>
                        <path d="M6 6h.01M18 6h.01M6 18h.01M18 18h.01"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Cierre de Caja</span>
                </div>
                @if(request()->routeIs('closing.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>
        </div>

        <!-- Bloque 3: Gestión -->
        <div class="space-y-1">
            <div class="sidebar-category px-2">
                <span class="text-[9px] font-black uppercase tracking-widest text-[#F5B81C]/90">
                    Gestión
                </span>
            </div>
            <div class="sidebar-divider hidden w-6 mx-auto my-1 border-t border-zinc-800/80"></div>

            <!-- Personal y Turnos -->
            <a href="{{ route('staff.index') }}" 
               title="Personal y Turnos"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('staff.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('staff.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Personal y Turnos</span>
                </div>
                @if(request()->routeIs('staff.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Pagos Personal -->
            <a href="{{ route('staffPayments.index') }}" 
               title="Pagos Personal"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('staffPayments.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('staffPayments.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="12" x="2" y="6" rx="2"/>
                        <circle cx="12" cy="12" r="2"/>
                        <path d="M6 12h.01M18 12h.01"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Pagos Personal</span>
                </div>
                @if(request()->routeIs('staffPayments.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Historial de Pagos -->
            <a href="{{ route('paymentHistory.index') }}" 
               title="Historial Pagos"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('paymentHistory.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('paymentHistory.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Historial Pagos</span>
                </div>
                @if(request()->routeIs('paymentHistory.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Historial Noches -->
            <a href="{{ route('sessions.index') }}" 
               title="Historial Noches"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('sessions.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('sessions.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Historial Noches</span>
                </div>
                @if(request()->routeIs('sessions.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Multimedia -->
            <a href="{{ route('media.index') }}" 
               title="Multimedia"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('media.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('media.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                        <circle cx="9" cy="9" r="2"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Multimedia</span>
                </div>
                @if(request()->routeIs('media.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Administración de Usuarios -->
            <a href="{{ route('users.index') }}" 
               title="Administración de Usuarios"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('users.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('users.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M20 8v6M23 11h-6"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Usuarios</span>
                </div>
                @if(request()->routeIs('users.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a>

            <!-- Servidor (Acceso Remoto para Celulares) -->
            <a href="{{ route('server.index') }}" 
               title="Servidor"
               class="sidebar-item group flex items-center justify-between px-2.5 py-1.5 rounded-lg border transition-all duration-150 {{ request()->routeIs('server.*') ? 'border-[#F5B81C] bg-[#F5B81C] text-black font-extrabold' : 'border-transparent text-zinc-300 font-semibold hover:border-zinc-800 hover:bg-zinc-900/80 hover:text-white' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('server.*') ? 'text-black' : 'text-[#F5B81C]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/>
                        <rect width="20" height="8" x="2" y="14" rx="2" ry="2"/>
                        <line x1="6" x2="6.01" y1="6" y2="6"/>
                        <line x1="6" x2="6.01" y1="18" y2="18"/>
                    </svg>
                    <span class="sidebar-text text-[13px] truncate">Servidor</span>
                </div>
                @if(request()->routeIs('server.*'))
                    <span class="sidebar-dot w-1.5 h-1.5 rounded-full bg-black shrink-0"></span>
                @endif
            </a> 
        </div>

    </nav>

    <!-- 3. Base Ficha de Usuario -->
    <div class="px-2.5 py-2 border-t border-zinc-800/80 shrink-0">
        
        <!-- Ficha de Usuario Expandida -->
        <div class="sidebar-user-expanded p-2 rounded-xl bg-zinc-950/80 border border-zinc-800/90 flex items-center justify-between">
            <div class="flex items-center gap-2.5 min-w-0">
                @if(Auth::user()?->avatar_url)
                    <div class="w-8 h-8 rounded-lg overflow-hidden shrink-0 border border-zinc-700" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; max-width: 32px; max-height: 32px;">
                        <img src="{{ Auth::user()->avatar_url }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="w-full h-full object-cover"
                             style="width: 32px; height: 32px; object-fit: cover; display: block;">
                    </div>
                @else
                    <div class="w-8 h-8 rounded-lg bg-[#F5B81C] text-black font-black text-xs flex items-center justify-center shrink-0 border border-zinc-700" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'D', 0, 1)) }}
                    </div>
                @endif

                <div class="truncate leading-tight">
                    <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name ?? 'Don Ludo' }}</p>
                    <p class="text-[10px] text-zinc-400 truncate">{{ Auth::user()->email ?? 'admin@tiochu.com' }}</p>
                </div>
            </div>

            <!-- Botón Salir Sutil -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        title="Cerrar sesión" 
                        class="p-1.5 text-zinc-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Ficha de Usuario Colapsada -->
        <div class="sidebar-user-collapsed hidden flex-col items-center justify-center gap-1.5 py-1">
            @if(Auth::user()?->avatar_url)
                <div class="w-8 h-8 rounded-lg overflow-hidden shrink-0 border border-zinc-700" title="{{ Auth::user()->name ?? 'Usuario' }}" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; max-width: 32px; max-height: 32px;">
                    <img src="{{ Auth::user()->avatar_url }}" 
                         alt="{{ Auth::user()->name }}" 
                         class="w-full h-full object-cover"
                         style="width: 32px; height: 32px; object-fit: cover; display: block;">
                </div>
            @else
                <div class="w-8 h-8 rounded-lg bg-[#F5B81C] text-black font-black text-xs flex items-center justify-center shrink-0 border border-zinc-700" title="{{ Auth::user()->name ?? 'Usuario' }}" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'D', 0, 1)) }}
                </div>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        title="Cerrar sesión" 
                        class="p-1 text-zinc-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>

    </div>
</aside>

<script>
    // Manejo interactivo del botón hamburguesa y persistencia
    document.addEventListener('DOMContentLoaded', function() {
        function toggleSidebar() {
            const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
            localStorage.setItem('tiochu_sidebar_collapsed', isCollapsed ? 'true' : 'false');
        }

        const btnExpanded = document.getElementById('toggle-sidebar-btn');
        const btnCollapsed = document.getElementById('toggle-sidebar-btn-collapsed');
        if (btnExpanded) btnExpanded.addEventListener('click', toggleSidebar);
        if (btnCollapsed) btnCollapsed.addEventListener('click', toggleSidebar);
    });
</script>
