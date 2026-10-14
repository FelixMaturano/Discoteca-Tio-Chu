@extends('layouts.app')

@section('title', 'Multimedia')

@section('content')
<div class="space-y-5 sm:space-y-8 max-w-7xl mx-auto pb-12 w-full min-w-0 max-w-full overflow-x-hidden">

    <!-- Cabecera Minimalista iOS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b w-full min-w-0" style="border-color: #1e1f24;">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-[#F5B81C]/10 text-[#F5B81C] border border-[#F5B81C]/20">
                    Galería & Identidad
                </span>
                <span class="text-zinc-600 text-xs">•</span>
                <span class="text-xs text-zinc-500 font-mono">{{ $counts['all'] }} Recursos</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Multimedia</h1>
            <p class="text-xs text-zinc-400 mt-1">
                Administra el logo oficial, el fondo de acceso y la biblioteca visual de bebidas, local y personal.
            </p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button type="button" 
                    onclick="openUploadModal()"
                    class="w-full sm:w-auto justify-center inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#F5B81C] text-black hover:bg-[#e5ac18] transition-all shadow-lg shadow-[#F5B81C]/10 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Subir Imagen</span>
            </button>
        </div>
    </div>

    <!-- Sección 1: Recursos Oficiales del Sistema (Logo y Fondo) -->
    <section class="space-y-3 w-full min-w-0">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Recursos Oficiales del Sistema</h2>
            <span class="text-[11px] text-zinc-500">Personalizables</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 w-full min-w-0">
            
            <!-- Card 1: Logo Oficial de Tío Chu -->
            <div class="p-3.5 sm:p-4 rounded-2xl border flex flex-col justify-between gap-3.5 sm:gap-4" style="background-color: #050507; border-color: #1e1f24;">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-black border border-white/10 flex items-center justify-center p-2 shrink-0 overflow-hidden relative group">
                        <img src="{{ asset('images/LogoTioChu.png') }}?v={{ $systemLogo ? $systemLogo->updated_at?->timestamp : time() }}" 
                             alt="Logo Oficial" 
                             class="max-w-full max-h-full object-contain cursor-pointer transition-transform group-hover:scale-105"
                             onclick="openLightbox('{{ asset('images/LogoTioChu.png') }}?v={{ $systemLogo ? $systemLogo->updated_at?->timestamp : time() }}', 'Logo Oficial Tío Chu', '{{ $systemLogo?->dimensions ?? '—' }}', '{{ $systemLogo?->formatted_size ?? '—' }}')">
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-white truncate">Logo Oficial</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#F5B81C]/15 text-[#F5B81C] border border-[#F5B81C]/30 shrink-0">Activo</span>
                        </div>
                        <p class="text-[11px] text-zinc-500 font-mono mt-0.5 truncate">Logo del sistema</p>
                    </div>
                </div>

                @if($systemLogo)
                <div class="pt-2 border-t flex items-center justify-end" style="border-color: #1a1a1f;">
                    <form action="{{ route('media.replace', $systemLogo) }}" method="POST" enctype="multipart/form-data" class="w-full">
                        @csrf
                        <input type="file" name="replacement_image" id="logo-file-input" accept="image/*" class="hidden" onchange="this.form.submit()">
                        <button type="button" 
                                onclick="document.getElementById('logo-file-input').click()"
                                class="w-full px-3 py-2.5 rounded-xl text-xs font-semibold text-zinc-200 bg-zinc-900 hover:bg-zinc-800 border border-zinc-700/60 active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span>Cambiar Logo</span>
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Card 2: Fondo del Inicio de Sesión -->
            <div class="p-3.5 sm:p-4 rounded-2xl border flex flex-col justify-between gap-3.5 sm:gap-4" style="background-color: #050507; border-color: #1e1f24;">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-black border border-white/10 flex items-center justify-center shrink-0 overflow-hidden relative group">
                        <img src="{{ asset('images/fondoTioChuLogo.png') }}?v={{ $systemBackground ? $systemBackground->updated_at?->timestamp : time() }}" 
                             alt="Fondo Login" 
                             class="w-full h-full object-cover cursor-pointer transition-transform group-hover:scale-105"
                             onclick="openLightbox('{{ asset('images/fondoTioChuLogo.png') }}?v={{ $systemBackground ? $systemBackground->updated_at?->timestamp : time() }}', 'Fondo Login', '{{ $systemBackground?->dimensions ?? '—' }}', '{{ $systemBackground?->formatted_size ?? '—' }}')">
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-white truncate">Fondo de Login</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/15 text-blue-400 border border-blue-500/30 shrink-0">Pantalla</span>
                        </div>
                        <p class="text-[11px] text-zinc-500 font-mono mt-0.5 truncate">Portada de acceso</p>
                    </div>
                </div>

                @if($systemBackground)
                <div class="pt-2 border-t flex items-center justify-end" style="border-color: #1a1a1f;">
                    <form action="{{ route('media.replace', $systemBackground) }}" method="POST" enctype="multipart/form-data" class="w-full">
                        @csrf
                        <input type="file" name="replacement_image" id="bg-file-input" accept="image/*" class="hidden" onchange="this.form.submit()">
                        <button type="button" 
                                onclick="document.getElementById('bg-file-input').click()"
                                class="w-full px-3 py-2.5 rounded-xl text-xs font-semibold text-zinc-200 bg-zinc-900 hover:bg-zinc-800 border border-zinc-700/60 active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span>Cambiar Fondo</span>
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Card 3: Foto de Perfil -->
            <div class="p-3.5 sm:p-4 rounded-2xl border flex flex-col justify-between gap-3.5 sm:gap-4" style="background-color: #050507; border-color: #1e1f24;">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 border-zinc-700 bg-zinc-900 overflow-hidden shrink-0 flex items-center justify-center">
                        @if(Auth::user()?->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" 
                                 alt="{{ Auth::user()->name }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <span class="text-xl font-bold text-zinc-300">
                                {{ strtoupper(substr(Auth::user()->name ?? 'D', 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-white truncate">Foto de perfil</h3>
                        <p class="text-xs text-zinc-400 mt-0.5 truncate">{{ Auth::user()->name ?? 'Don Ludo' }} &bull; {{ Auth::user()->email ?? 'admin@tiochu.com' }}</p>
                    </div>
                </div>

                <div class="pt-2 border-t flex flex-col sm:flex-row items-stretch sm:items-center gap-2" style="border-color: #1a1a1f;">
                    <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="flex-1 w-full">
                        @csrf
                        <input type="file" name="avatar" id="media-profile-avatar-input" accept="image/*" class="hidden" onchange="this.form.submit()">
                        <button type="button" 
                                onclick="document.getElementById('media-profile-avatar-input').click()" 
                                class="w-full px-3 py-2.5 rounded-xl text-xs font-semibold bg-[#F5B81C] text-black hover:bg-[#e5ac18] active:scale-95 transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-sm text-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <circle cx="12" cy="13" r="3"/>
                            </svg>
                            <span>Cambiar foto</span>
                        </button>
                    </form>

                    @if(Auth::user()?->avatar)
                        <form action="{{ route('profile.avatar.remove') }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full sm:w-auto px-3 py-2.5 rounded-xl text-xs font-medium text-zinc-400 hover:text-white bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 transition-colors cursor-pointer text-center">
                                Quitar foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </section>

    <!-- Sección 2: Galería de Recursos Multimedia -->
    <section class="space-y-4 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Galería de Recursos</h2>
            
            <!-- Filtros por Categoría iOS -->
            <div class="flex items-center gap-1.5 p-1 rounded-xl bg-zinc-950 border border-zinc-800/80 overflow-x-auto w-full sm:w-auto max-w-full" style="scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                <a href="{{ route('media.index', ['category' => 'all']) }}" 
                   class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap {{ $category === 'all' ? 'bg-zinc-900 text-[#F5B81C] font-semibold' : 'text-zinc-400 hover:text-white' }}">
                    Todas ({{ $counts['all'] }})
                </a>
                <a href="{{ route('media.index', ['category' => 'drinks']) }}" 
                   class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap {{ $category === 'drinks' ? 'bg-zinc-900 text-[#F5B81C] font-semibold' : 'text-zinc-400 hover:text-white' }}">
                    Bebidas ({{ $counts['drinks'] }})
                </a>
                <a href="{{ route('media.index', ['category' => 'establishment']) }}" 
                   class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap {{ $category === 'establishment' ? 'bg-zinc-900 text-[#F5B81C] font-semibold' : 'text-zinc-400 hover:text-white' }}">
                    Local ({{ $counts['establishment'] }})
                </a>
                <a href="{{ route('media.index', ['category' => 'staff']) }}" 
                   class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap {{ $category === 'staff' ? 'bg-zinc-900 text-[#F5B81C] font-semibold' : 'text-zinc-400 hover:text-white' }}">
                    Personal ({{ $counts['staff'] }})
                </a>
                <a href="{{ route('media.index', ['category' => 'system']) }}" 
                   class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap {{ $category === 'system' ? 'bg-zinc-900 text-[#F5B81C] font-semibold' : 'text-zinc-400 hover:text-white' }}">
                    Sistema ({{ $counts['system'] }})
                </a>
            </div>
        </div>

        @if($mediaAssets->isEmpty())
            <!-- Estado Vacío Minimalista -->
            <div class="py-16 text-center rounded-2xl border border-dashed border-zinc-800/80 bg-zinc-950/40 px-4">
                <div class="w-12 h-12 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-500 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                        <circle cx="9" cy="9" r="2"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-white">No hay imágenes en esta categoría</h3>
                <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">
                    Sube fotos de bebidas (Casa Real, Fernet), instalaciones o colaboradores para usarlas en el sistema.
                </p>
                <button type="button" 
                        onclick="openUploadModal()"
                        class="mt-4 px-4 py-2.5 rounded-xl text-xs font-semibold bg-zinc-900 text-zinc-200 border border-zinc-700/60 hover:bg-zinc-800 transition-colors cursor-pointer">
                    Subir primera imagen
                </button>
            </div>
        @else
            <!-- Grilla de Imágenes: 2 cols en celulares, 3 en sm, 4 en md, 5 en lg -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-4 w-full min-w-0">
                @foreach($mediaAssets as $asset)
                    <div class="group rounded-2xl border overflow-hidden flex flex-col justify-between transition-all duration-200 hover:border-zinc-700/80 shadow-sm w-full min-w-0" 
                         style="background-color: #060608; border-color: #1e1f24;">
                        
                        <!-- Contenedor Visual de la Imagen (Cuadrado para uniformidad) -->
                        <div class="relative aspect-square w-full bg-black/60 flex items-center justify-center overflow-hidden cursor-pointer"
                             onclick="openLightbox('{{ $asset->url }}', '{{ addslashes($asset->title) }}', '{{ $asset->dimensions ?? '—' }}', '{{ $asset->formatted_size }}')">
                            
                            <img src="{{ $asset->url }}" 
                                 alt="{{ $asset->title }}" 
                                 loading="lazy"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">

                            <!-- Badge de Categoría Flotante -->
                            <div class="absolute top-2 left-2 z-10 pointer-events-none">
                                <span class="px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-semibold backdrop-blur-md border 
                                    {{ $asset->is_system ? 'bg-[#F5B81C]/20 text-[#F5B81C] border-[#F5B81C]/40' : 'bg-black/70 text-zinc-300 border-white/10' }}">
                                    {{ $asset->category_label }}
                                </span>
                            </div>
                        </div>

                        <!-- Metadatos y Acciones -->
                        <div class="p-2 sm:p-3 flex-1 flex flex-col justify-between gap-2 min-w-0 overflow-hidden">
                            <div class="min-w-0 overflow-hidden">
                                <p class="text-xs font-bold text-white truncate block w-full" title="{{ $asset->title }}">{{ $asset->title }}</p>
                                <div class="flex items-center gap-1 text-[10px] text-zinc-500 font-mono mt-0.5 truncate w-full">
                                    <span>{{ $asset->dimensions ?? '—' }}</span>
                                    <span>•</span>
                                    <span>{{ $asset->formatted_size }}</span>
                                </div>
                            </div>

                            <!-- Barra de Acciones: 4 Columnas adaptadas al ancho exacto del card -->
                            <div class="pt-2 border-t grid grid-cols-4 gap-1 w-full min-w-0" style="border-color: #1a1a1f;">
                                <!-- Copiar URL Directa -->
                                <button type="button" 
                                        onclick="copyMediaUrl('{{ $asset->url }}')"
                                        title="Copiar URL directa"
                                        class="h-7 sm:h-8 rounded-lg text-zinc-300 hover:text-white bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 transition-colors cursor-pointer flex items-center justify-center w-full">
                                    <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </button>

                                @if(!$asset->is_system)
                                    <!-- Establecer como Logo -->
                                    <form action="{{ route('media.setSystem', $asset) }}" method="POST" class="w-full inline" onsubmit="return confirm('¿Establecer como Logo Oficial?')">
                                        @csrf
                                        <input type="hidden" name="target" value="logo">
                                        <button type="submit" 
                                                title="Establecer como Logo Oficial" 
                                                class="h-7 sm:h-8 rounded-lg text-zinc-400 hover:text-[#F5B81C] bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 transition-colors cursor-pointer flex items-center justify-center w-full">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Reemplazar Archivo -->
                                    <form action="{{ route('media.replace', $asset) }}" method="POST" enctype="multipart/form-data" class="w-full inline">
                                        @csrf
                                        <input type="file" name="replacement_image" id="replace-file-{{ $asset->id }}" accept="image/*" class="hidden" onchange="this.form.submit()">
                                        <button type="button" 
                                                onclick="document.getElementById('replace-file-{{ $asset->id }}').click()"
                                                title="Reemplazar archivo" 
                                                class="h-7 sm:h-8 rounded-lg text-zinc-400 hover:text-white bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 transition-colors cursor-pointer flex items-center justify-center w-full">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Eliminar -->
                                    <form action="{{ route('media.destroy', $asset) }}" method="POST" class="w-full inline" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Eliminar de la galería" 
                                                class="h-7 sm:h-8 rounded-lg text-zinc-500 hover:text-rose-400 bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 transition-colors cursor-pointer flex items-center justify-center w-full">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <div class="col-span-3 flex items-center justify-center bg-zinc-900/40 rounded-lg border border-zinc-800/50">
                                        <span class="text-[9px] text-zinc-500 font-mono">Sistema</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </section>

</div>

<!-- Modal 1: Subir Nueva Imagen (Minimalismo iOS) -->
<div id="upload-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-3 sm:p-4">
    <div class="w-full max-w-md max-h-[92vh] overflow-y-auto rounded-2xl border p-4 sm:p-6 space-y-4 sm:space-y-5" style="background-color: #000000; border-color: #27272a;">
        <div class="flex items-center justify-between pb-3 border-b" style="border-color: #1e1f24;">
            <h3 class="text-sm font-bold text-white">Subir a Galería Multimedia</h3>
            <button type="button" onclick="closeUploadModal()" class="text-zinc-500 hover:text-white transition-colors cursor-pointer p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <!-- Zona de Arrastre / Previsualización -->
            <div>
                <label class="block text-xs font-medium text-zinc-400 mb-1.5">Archivo de Imagen</label>
                <div id="dropzone" 
                     onclick="document.getElementById('image-upload-input').click()"
                     class="border border-dashed rounded-xl p-4 sm:p-5 text-center cursor-pointer transition-colors hover:border-zinc-500"
                     style="background-color: #08080a; border-color: #27272a;">
                    
                    <input type="file" name="image" id="image-upload-input" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden" required onchange="handleImagePreview(this)">
                    
                    <div id="dropzone-empty" class="space-y-2">
                        <svg class="w-8 h-8 mx-auto text-zinc-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <p class="text-xs text-zinc-300 font-medium">Haz clic o arrastra una imagen</p>
                        <p class="text-[10px] text-zinc-500">PNG, JPG, WEBP o SVG (máx. 10MB)</p>
                    </div>

                    <div id="dropzone-preview" class="hidden">
                        <img id="preview-img" src="" alt="Preview" class="max-h-36 sm:max-h-40 mx-auto rounded-lg object-contain">
                        <p class="text-[10px] text-[#F5B81C] mt-2 font-medium">Clic para cambiar imagen</p>
                    </div>
                </div>
            </div>

            <!-- Título -->
            <div>
                <label for="title" class="block text-xs font-medium text-zinc-400 mb-1.5">Título o Identificador</label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       required 
                       placeholder="Ej: Singani Casa Real Etiqueta Negra"
                       class="w-full px-3.5 py-2.5 rounded-xl text-xs text-white placeholder-zinc-600 border focus:outline-none focus:border-[#F5B81C] transition-colors"
                       style="background-color: #08080a; border-color: #27272a;">
            </div>

            <!-- Categoría -->
            <div>
                <label for="category" class="block text-xs font-medium text-zinc-400 mb-1.5">Categoría</label>
                <select name="category" 
                        id="category" 
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs text-white border focus:outline-none focus:border-[#F5B81C] transition-colors cursor-pointer"
                        style="background-color: #08080a; border-color: #27272a;">
                    <option value="drinks" class="bg-black text-white">Bebidas & Licores</option>
                    <option value="establishment" class="bg-black text-white">Local & Discoteca</option>
                    <option value="staff" class="bg-black text-white">Personal & Bartenders</option>
                    <option value="general" class="bg-black text-white">General</option>
                </select>
            </div>

            <!-- Botones -->
            <div class="pt-2 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                <button type="button" 
                        onclick="closeUploadModal()"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-medium text-zinc-400 hover:text-white transition-colors cursor-pointer text-center">
                    Cancelar
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#F5B81C] text-black hover:bg-[#e5ac18] transition-colors shadow-lg shadow-[#F5B81C]/15 cursor-pointer text-center">
                    Guardar Imagen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Lightbox Visor Fullscreen -->
<div id="lightbox-modal" class="fixed inset-0 z-50 bg-black/95 backdrop-blur-xl hidden flex flex-col justify-between p-4 sm:p-6" onclick="closeLightbox(event)">
    <div class="flex items-center justify-between max-w-6xl w-full mx-auto pb-4">
        <div>
            <h4 id="lightbox-title" class="text-sm font-bold text-white">Título</h4>
            <div class="flex items-center gap-2 text-xs text-zinc-500 font-mono mt-0.5">
                <span id="lightbox-dim">—</span>
                <span>•</span>
                <span id="lightbox-size">—</span>
            </div>
        </div>
        <button type="button" onclick="closeLightboxDirect()" class="p-2 rounded-xl text-zinc-400 hover:text-white bg-zinc-900 border border-zinc-800 transition-colors cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="flex-1 flex items-center justify-center p-2 min-h-0 overflow-hidden">
        <img id="lightbox-img" src="" alt="Fullscreen Preview" class="max-h-full max-w-full object-contain rounded-xl shadow-2xl">
    </div>

    <div class="max-w-md w-full mx-auto pt-4 text-center">
        <button type="button" id="lightbox-copy-btn" onclick="copyCurrentLightboxUrl()" class="px-4 py-2 rounded-xl text-xs font-medium text-zinc-300 bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 transition-colors inline-flex items-center gap-2 cursor-pointer">
            <svg class="w-3.5 h-3.5 text-[#F5B81C]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            <span>Copiar Enlace Directo</span>
        </button>
    </div>
</div>

<!-- Toast Efímero Minimalista (Regla de design.md) -->
<div id="floating-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full border text-xs font-medium text-zinc-300 bg-zinc-950/90 border-white/10 shadow-2xl backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-2">
    <span class="w-1.5 h-1.5 rounded-full bg-[#F5B81C]"></span>
    <span id="toast-message">Notificación</span>
</div>

<script>
let currentLightboxUrl = '';

function showToast(message) {
    const toast = document.getElementById('floating-toast');
    const msgElem = document.getElementById('toast-message');
    msgElem.textContent = message;
    toast.classList.remove('opacity-0', 'pointer-events-none');
    toast.classList.add('opacity-100');
    setTimeout(() => {
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0', 'pointer-events-none');
    }, 2000);
}

function copyMediaUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('Enlace copiado al portapapeles');
    }).catch(() => {
        showToast('URL: ' + url);
    });
}

function copyCurrentLightboxUrl() {
    if (currentLightboxUrl) {
        copyMediaUrl(currentLightboxUrl);
    }
}

function openUploadModal() {
    document.getElementById('upload-modal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('upload-modal').classList.add('hidden');
    document.getElementById('image-upload-input').value = '';
    document.getElementById('dropzone-preview').classList.add('hidden');
    document.getElementById('dropzone-empty').classList.remove('hidden');
}

function handleImagePreview(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('dropzone-empty').classList.add('hidden');
            document.getElementById('dropzone-preview').classList.remove('hidden');
            
            // Sugerir título si está vacío
            const titleInput = document.getElementById('title');
            if (!titleInput.value) {
                const nameWithoutExt = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, " ");
                titleInput.value = nameWithoutExt.charAt(0).toUpperCase() + nameWithoutExt.slice(1);
            }
        };
        reader.readAsDataURL(file);
    }
}

function openLightbox(url, title, dim, size) {
    currentLightboxUrl = url;
    document.getElementById('lightbox-img').src = url;
    document.getElementById('lightbox-title').textContent = title;
    document.getElementById('lightbox-dim').textContent = dim || '—';
    document.getElementById('lightbox-size').textContent = size || '—';
    document.getElementById('lightbox-modal').classList.remove('hidden');
}

function closeLightbox(e) {
    if (e.target.id === 'lightbox-modal' || e.target.id === 'lightbox-img') {
        closeLightboxDirect();
    }
}

function closeLightboxDirect() {
    document.getElementById('lightbox-modal').classList.add('hidden');
}

// Drag & drop listeners en dropzone
const dropzone = document.getElementById('dropzone');
if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('border-[#F5B81C]');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('border-[#F5B81C]');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            const input = document.getElementById('image-upload-input');
            input.files = files;
            handleImagePreview(input);
        }
    }, false);
}

// Cerrar con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeUploadModal();
        closeLightboxDirect();
    }
});
</script>
@endsection
