<!DOCTYPE html>
<html lang="es" class="h-full" style="background-color: #000000;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tío Chu</title>
    
    <!-- Favicon Oficial Tío Chu -->
    <link rel="icon" type="image/png" href="{{ asset('images/LogoTioChu.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/LogoTioChu.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Sacramento&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Respaldo CDN de GSAP para que funcione inmediatamente en cualquier máquina -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <style>
        /* Optimización de aceleración por hardware GPU para 60/120 FPS */
        #splash-screen, #left-panel, #right-panel, #right-panel-img {
            will-change: transform, opacity;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        /* Anular fondo blanco/celeste del autocompletado del navegador */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #09090b inset !important;
            box-shadow: 0 0 0 1000px #09090b inset !important;
            -webkit-text-fill-color: #ffffff !important;
            caret-color: #ffffff !important;
            border-color: #27272a !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        input:-webkit-autofill:focus {
            border-color: #F5B81C !important;
            -webkit-box-shadow: 0 0 0 1000px #09090b inset, 0 0 0 1px #F5B81C !important;
            box-shadow: 0 0 0 1000px #09090b inset, 0 0 0 1px #F5B81C !important;
        }
    </style>
</head>

<body class="h-full text-zinc-100 font-sans antialiased selection:bg-[#F5B81C] selection:text-black overflow-x-hidden" style="background-color: #000000;">

    <!-- PANTALLA DE CARGA / SPLASH INICIAL (Logo Central de Tío Chu) -->
    <div id="splash-screen" class="fixed inset-0 z-50 flex items-center justify-center" style="background-color: #000000;">
        <div class="text-center px-4">
            <img id="splash-logo" 
                 src="{{ asset('images/LogoTioChu.png') }}" 
                 alt="Tío Chu" 
                 class="w-48 h-48 sm:w-60 sm:h-60 object-contain mx-auto select-none opacity-0">
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL: PUERTAS Y ESCENARIO CENTRAL -->
    <div class="min-h-screen w-full relative overflow-hidden" style="background-color: #000000;">
        
        <!-- ETAPA CENTRAL AL INICIAR SESIÓN: LOGO, BARRITA Y TEXTO 'BIENVENIDO' ESTILO IPHONE (z-10) -->
        <div id="submit-loading-stage" class="absolute inset-0 z-10 flex flex-col items-center justify-center pointer-events-none" style="background-color: #000000;">
            <div class="text-center px-4 flex flex-col items-center">
                <img id="submit-logo" 
                     src="{{ asset('images/LogoTioChu.png') }}" 
                     alt="Tío Chu" 
                     class="w-44 h-44 sm:w-56 sm:h-56 object-contain mx-auto select-none opacity-0">
                
                <!-- Escenario de la Barrita que se transforma en Cursiva (Escala prominente y visible) -->
                <div id="hello-stage" class="relative mt-6 flex items-center justify-center min-h-[140px] w-full max-w-xl sm:max-w-2xl px-4 select-none">
                    
                    <!-- 1. Barrita de carga inicial que se llena -->
                    <div id="loading-bar-container" class="w-64 sm:w-80 h-1.5 bg-zinc-900 border border-zinc-800 rounded-full overflow-hidden opacity-0">
                        <div id="loading-bar-fill" class="h-full rounded-full w-0" style="background-color: #F5B81C; box-shadow: 0 0 12px #F5B81C;"></div>
                    </div>

                    <!-- 2. Contenedor de la Caligrafía Cursiva 'Bienvenido' estilo iPhone Hello -->
                    <div id="cursive-container" class="absolute inset-0 flex items-center justify-center opacity-0 pointer-events-none">
                        <svg viewBox="0 0 520 130" class="w-full max-w-[500px] sm:max-w-[580px] h-28 sm:h-36 overflow-visible">
                            <defs>
                                <!-- Máscara de revelado progresivo de izquierda a derecha -->
                                <clipPath id="cursive-clip">
                                    <rect id="cursive-rect" x="0" y="0" width="0" height="130" />
                                </clipPath>
                                <!-- Resplandor dorado suave -->
                                <filter id="gold-glow" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="0" stdDeviation="4" flood-color="#F5B81C" flood-opacity="0.8"/>
                                </filter>
                            </defs>

                            <!-- Trazo dibujado estilo letra carta grande y legible -->
                            <text id="cursive-stroke"
                                  x="260" y="88" 
                                  text-anchor="middle"
                                  fill="transparent"
                                  stroke="#F5B81C"
                                  stroke-width="3"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-dasharray="1600"
                                  stroke-dashoffset="1600"
                                  clip-path="url(#cursive-clip)"
                                  filter="url(#gold-glow)"
                                  style="font-family: 'Dancing Script', 'Sacramento', cursive; font-size: 96px; font-weight: 700; letter-spacing: 0.02em;">
                                Bienvenido
                            </text>

                            <!-- Relleno dorado que florece cuando termina de escribirse -->
                            <text id="cursive-fill"
                                  x="260" y="88" 
                                  text-anchor="middle"
                                  fill="#F5B81C"
                                  opacity="0"
                                  clip-path="url(#cursive-clip)"
                                  filter="url(#gold-glow)"
                                  style="font-family: 'Dancing Script', 'Sacramento', cursive; font-size: 96px; font-weight: 700; letter-spacing: 0.02em;">
                                Bienvenido
                            </text>

                            <!-- Chispa / Destello de luz dorada en la punta del trazo -->
                            <circle id="pen-spark" cx="25" cy="80" r="4.5" fill="#FFFFFF" opacity="0" filter="url(#gold-glow)" />
                        </svg>
                    </div>

                </div>
            </div>
        </div>

        <!-- LAS DOS PUERTAS DESLIZANTES (z-20, CUBREN EL CENTRO HASTA QUE SE ABREN) -->
        <div id="doors-wrapper" class="min-h-screen w-full flex flex-col lg:flex-row relative z-20 pointer-events-auto">
            
            <!-- HOJA IZQUIERDA DE LA PUERTA: Formulario Estilo iOS Minimalista -->
            <div id="left-panel" class="w-full lg:w-1/2 min-h-screen flex flex-col justify-center items-center px-6 sm:px-12 py-10" style="background-color: #000000; will-change: transform;" inert>
                
                <div class="w-full max-w-sm flex flex-col items-center">
                    
                    <!-- Logo Oficial -->
                    <div id="form-logo" class="text-center mb-6 -mt-8 sm:-mt-12">
                        <img src="{{ asset('images/LogoTioChu.png') }}" 
                             alt="Logo Tío Chu" 
                             class="w-40 h-40 sm:w-48 sm:h-48 md:w-52 md:h-52 object-contain mx-auto select-none">
                    </div>



                    @if($errors->any())
                        <div class="w-full mb-4 text-center">
                            <p class="text-xs text-rose-400 font-medium">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <!-- Formulario -->
                    <form method="POST" action="{{ route('login') }}" class="w-full space-y-4" id="login-form" autocomplete="off">
                        @csrf

                        <!-- Campo Correo -->
                        <div class="space-y-1.5 form-field">
                            <label for="email" class="block text-xs font-medium text-zinc-400">
                                Correo electrónico
                            </label>
                            <input type="text" 
                                   name="email" 
                                   id="email" 
                                   required 
                                   autocomplete="username"
                                   value="{{ old('email') }}"
                                   placeholder="correo@ejemplo.com"
                                   class="w-full px-4 py-3 rounded-xl bg-black border border-zinc-800 text-sm text-white placeholder-zinc-600 focus:outline-none focus:border-[#F5B81C] focus:ring-1 focus:ring-[#F5B81C] transition-colors">
                        </div>

                        <!-- Campo Contraseña -->
                        <div class="space-y-1.5 form-field">
                            <label for="password" class="block text-xs font-medium text-zinc-400">
                                Contraseña
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full px-4 py-3 rounded-xl bg-black border border-zinc-800 text-sm text-white placeholder-zinc-600 focus:outline-none focus:border-[#F5B81C] focus:ring-1 focus:ring-[#F5B81C] transition-colors">
                        </div>


                        <!-- Botón de Ingreso iOS Minimalista Dorado -->
                        <div class="pt-2 form-field">
                            <button type="submit" 
                                    id="submit-btn"
                                    class="w-full py-3.5 px-4 bg-[#F5B81C] hover:bg-[#e6ab17] active:bg-[#d69d0f] text-black font-semibold text-sm rounded-xl transition-all cursor-pointer text-center shadow-lg shadow-[#F5B81C]/10">
                                Iniciar sesión
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- HOJA DERECHA DE LA PUERTA: Imagen fondoTioChuLogo.png -->
            <div id="right-panel" class="hidden lg:block lg:w-1/2 min-h-screen relative overflow-hidden" style="background-color: #000000; will-change: transform;">
                <img id="right-panel-img"
                     src="{{ asset('images/fondoTioChuLogo.png') }}" 
                     alt="Tío Chu Fondo" 
                     class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-black via-transparent to-transparent w-24"></div>
            </div>

        </div>

    </div>



    <script>
        // Mantener sesión viva para evitar token CSRF vencido
        setInterval(function() {
            fetch('{{ route("login") }}', { method: 'HEAD' })
                .catch(function(err) { console.log('Keep-alive error:', err); });
        }, 10 * 60 * 1000);

        // Core de Animación de Puerta Corrediza Doble (GSAP GPU Accelerated)
        window.addEventListener('load', () => {
            const gsap = window.gsap;

            if (!gsap) {
                const splash = document.getElementById('splash-screen');
                if (splash) splash.style.display = 'none';
                const leftPanel = document.getElementById('left-panel');
                if (leftPanel) leftPanel.removeAttribute('inert');
                return;
            }

            // Preparar las puertas fuera de la pantalla antes de la animación:
            gsap.set('#left-panel', { xPercent: -100 });
            gsap.set('#right-panel', { xPercent: 100 });

            // 1. TIMELINE DE ENTRADA (Cierre de Puertas desde los extremos)
            const tl = gsap.timeline({
                onComplete: () => {
                    const leftPanel = document.getElementById('left-panel');
                    if (leftPanel) leftPanel.removeAttribute('inert');
                }
            });

            // Entrada del logo splash central
            tl.to('#splash-logo', {
                opacity: 1,
                scale: 1,
                duration: 0.6,
                ease: 'power2.out'
            });

            // El logo central se difumina
            tl.to('#splash-logo', {
                opacity: 0,
                scale: 1.15,
                filter: 'blur(10px)',
                duration: 0.45,
                ease: 'power2.inOut',
                delay: 0.35
            });

            // Desaparece el splash
            tl.to('#splash-screen', {
                opacity: 0,
                duration: 0.3,
                ease: 'power1.out',
                onComplete: () => {
                    const splash = document.getElementById('splash-screen');
                    if (splash) splash.style.display = 'none';
                }
            }, "-=0.2");

            // EFECTO PUERTA: La izquierda entra desde la IZQUIERDA (-100% -> 0%)
            // y la derecha entra desde la DERECHA (100% -> 0%), uniéndose en el centro
            tl.to('#left-panel', {
                xPercent: 0,
                duration: 0.85,
                ease: 'power3.out'
            }, "-=0.15");

            tl.to('#right-panel', {
                xPercent: 0,
                duration: 0.85,
                ease: 'power3.out'
            }, "<");

            // 2. ANIMACIÓN DE SALIDA (Reversa: Apertura de Puertas, Logo en el medio y Barrita de Carga Amarilla)
            const form = document.getElementById('login-form');
            const submitBtn = document.getElementById('submit-btn');
            let isSubmitting = false;

            if (form) {
                form.addEventListener('submit', (e) => {
                    if (isSubmitting) return;

                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    e.preventDefault();
                    isSubmitting = true;

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-80', 'cursor-wait');
                    }

                    // Asegurar estado inicial de la barrita y caligrafía cursiva
                    gsap.set('#submit-logo', { opacity: 0, scale: 0.85 });
                    gsap.set('#loading-bar-container', { opacity: 0, scaleX: 1, filter: 'blur(0px)' });
                    gsap.set('#loading-bar-fill', { width: '0%' });
                    gsap.set('#cursive-container', { opacity: 0 });
                    gsap.set('#cursive-rect', { attr: { width: 0 } });
                    gsap.set('#cursive-stroke', { strokeDashoffset: 1600 });
                    gsap.set('#cursive-fill', { opacity: 0 });
                    gsap.set('#pen-spark', { opacity: 0, attr: { cx: 25 } });

                    const exitTl = gsap.timeline({
                        onComplete: () => {
                            form.submit();
                        }
                    });

                    // 1. Las dos puertas se abren hacia los lados
                    exitTl.to('#left-panel', {
                        xPercent: -100,
                        duration: 0.6,
                        ease: 'power3.inOut'
                    });

                    exitTl.to('#right-panel', {
                        xPercent: 100,
                        duration: 0.6,
                        ease: 'power3.inOut'
                    }, "<");

                    // 2. Al abrirse, el logo de Tío Chu aparece en el centro exacto
                    exitTl.to('#submit-logo', {
                        opacity: 1,
                        scale: 1,
                        duration: 0.4,
                        ease: 'power2.out'
                    }, "-=0.35");

                    // 3. Aparece la barrita
                    exitTl.to('#loading-bar-container', {
                        opacity: 1,
                        duration: 0.2,
                        ease: 'power1.out'
                    }, "-=0.15");

                    // 4. La barrita amarilla se llena rápidamente
                    exitTl.to('#loading-bar-fill', {
                        width: '100%',
                        duration: 0.45,
                        ease: 'power2.inOut'
                    });

                    // 5. METAMORFOSIS: La barrita se disuelve y se convierte en el trazo de letra carta
                    exitTl.to('#loading-bar-container', {
                        opacity: 0,
                        scaleX: 0.3,
                        filter: 'blur(6px)',
                        duration: 0.2,
                        ease: 'power2.in'
                    });

                    // Activar el lienzo de caligrafía cursiva
                    exitTl.set('#cursive-container', { opacity: 1 }, "-=0.08");
                    exitTl.to('#pen-spark', { opacity: 1, duration: 0.1 }, "<");

                    // 6. ANIMACIÓN IPHONE HELLO: La línea traza "Bienvenido" en letra carta de izquierda a derecha
                    exitTl.to('#cursive-stroke', {
                        strokeDashoffset: 0,
                        duration: 1.15,
                        ease: 'power2.inOut'
                    }, "-=0.05");

                    exitTl.to('#cursive-rect', {
                        attr: { width: 520 },
                        duration: 1.15,
                        ease: 'power2.inOut'
                    }, "<");

                    exitTl.to('#pen-spark', {
                        attr: { cx: 500 },
                        duration: 1.15,
                        ease: 'power2.inOut'
                    }, "<");

                    // 7. Al culminar el trazo, la caligrafía florece en amarillo dorado y la chispa se disuelve
                    exitTl.to('#cursive-fill', {
                        opacity: 1,
                        duration: 0.35,
                        ease: 'power1.out'
                    }, "-=0.2");

                    exitTl.to('#pen-spark', {
                        opacity: 0,
                        scale: 1.8,
                        duration: 0.25
                    }, "<");

                    // 8. Breve pausa ágil tras leer "Bienvenido" antes del submit
                    exitTl.to({}, { duration: 0.25 });
                });
            }
        });
    </script>
</body>

</html>