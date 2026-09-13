<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? (\App\Models\Setting::first()?->shop_name ?? 'Self Order - POS Cafe') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Tailwind CSS CDN & Config --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        fore: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#0e382c', // Fore Signature Forest Green
                            950: '#07241c',
                        },
                        forest: {
                            DEFAULT: '#0e382c',
                            light: '#134e3f',
                            dark: '#08231c',
                        },
                        voca: {
                            cream: '#FAF9F5',
                            card: '#F5F3EC',
                            border: '#E8E5DD',
                            dark: '#1C1917',
                            muted: '#78716C',
                            bronze: '#8C6D46',
                            clay: '#4A3E3D',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Playfair Display', 'Cormorant Garamond', 'serif'],
                        editorial: ['Cormorant Garamond', 'Playfair Display', 'serif'],
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                        'card': '0 10px 30px -5px rgba(0, 0, 0, 0.06)',
                        'elevated': '0 20px 40px -15px rgba(0, 0, 0, 0.12)',
                        'glow': '0 0 25px rgba(224, 138, 60, 0.35)',
                    }
                }
            }
        }
    </script>

    {{-- FontAwesome & Google Fonts (Plus Jakarta Sans, Playfair Display & Cormorant Garamond) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;0,900;1,400;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        html { scroll-behavior: smooth; }
        [x-cloak] { display: none !important; }
        * { -webkit-tap-highlight-color: transparent; }
        
        /* Safe Area Padding for mobile notches */
        .pt-safe { padding-top: env(safe-area-inset-top, 0.5rem); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 1rem); }
        
        /* Smooth Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Smooth Animations */
        @keyframes popIn {
            0% { transform: scale(0.96); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-pop { animation: popIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        /* Steam Rise Keyframes for Coffee Visual */
        @keyframes steamRise {
            0% { transform: translateY(0) scaleX(0.8); opacity: 0; }
            20% { opacity: 0.6; }
            50% { transform: translateY(-16px) scaleX(1.1) translateX(3px); opacity: 0.4; }
            80% { opacity: 0.2; }
            100% { transform: translateY(-32px) scaleX(1.4) translateX(-4px); opacity: 0; }
        }

        /* Micro-animations for modern interactive cafe experience */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        .animate-float { animation: floatSlow 4s ease-in-out infinite; }
        .animate-float-delayed { animation: floatSlow 4.5s ease-in-out infinite 1.5s; }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .animate-shimmer {
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }

        /* Staggered Smooth Reveal Animations */
        @keyframes heroRevealUp {
            0% {
                opacity: 0;
                transform: translateY(24px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes heroRevealDown {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes heroRevealScale {
            0% {
                opacity: 0;
                transform: scale(0.94) translateY(18px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .reveal-down {
            animation: heroRevealDown 0.65s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .reveal-up {
            animation: heroRevealUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .reveal-scale {
            animation: heroRevealScale 0.85s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        .delay-400 { animation-delay: 400ms; }
        .delay-500 { animation-delay: 500ms; }
        .delay-600 { animation-delay: 600ms; }

        /* Boutique Splash Curtain Animation */
        @keyframes splashLogoIn {
            0% {
                opacity: 0;
                transform: scale(0.85) translateY(16px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes splashBarFill {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        @keyframes splashCurtainUp {
            0%, 65% {
                transform: translateY(0);
                opacity: 1;
            }
            95% {
                transform: translateY(-100%);
                opacity: 1;
            }
            100% {
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }
        }

        #cafe-splash {
            animation: splashCurtainUp 1.35s cubic-bezier(0.77, 0, 0.175, 1) forwards;
        }

        #cafe-splash .splash-logo {
            animation: splashLogoIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        #cafe-splash .splash-text-1 {
            animation: splashLogoIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
        }

        #cafe-splash .splash-text-2 {
            animation: splashLogoIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.18s both;
        }

        #cafe-splash .splash-bar {
            animation: splashBarFill 0.85s cubic-bezier(0.4, 0, 0.2, 1) 0.1s both;
        }

        #cafe-splash.splash-dismissed {
            animation: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition: opacity 0.25s ease, visibility 0.25s ease !important;
        }

        /* Option 3: Atmospheric Badge Shine & Living Micro-Motions */
        @keyframes badgeShine {
            0% { transform: translateX(-120%) rotate(25deg); }
            35%, 100% { transform: translateX(220%) rotate(25deg); }
        }
        .badge-shine {
            position: relative;
            overflow: hidden;
        }
        .badge-shine::after {
            content: '';
            position: absolute;
            top: -60%;
            left: -60%;
            width: 220%;
            height: 220%;
            background: linear-gradient(
                90deg,
                transparent 0%,
                rgba(255, 255, 255, 0.4) 50%,
                transparent 100%
            );
            transform: translateX(-120%) rotate(25deg);
            animation: badgeShine 4.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            pointer-events: none;
        }

        /* ========================================================================= */
        /* BEST PRACTICE SCROLL REVEAL (GPU-ACCELERATED NATIVE TRANSITIONS)          */
        /* ========================================================================= */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-on-scroll-left {
            opacity: 0;
            transform: translateX(-32px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-on-scroll-right {
            opacity: 0;
            transform: translateX(32px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-on-scroll-scale {
            opacity: 0;
            transform: scale(0.95) translateY(24px);
            transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1), transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        /* Active revealed state */
        .reveal-on-scroll.is-revealed,
        .reveal-on-scroll-left.is-revealed,
        .reveal-on-scroll-right.is-revealed,
        .reveal-on-scroll-scale.is-revealed,
        .is-revealed {
            opacity: 1;
            transform: none;
        }

        /* Stagger delays for grid / lists */
        .stagger-1 { transition-delay: 80ms; }
        .stagger-2 { transition-delay: 160ms; }
        .stagger-3 { transition-delay: 240ms; }
        .stagger-4 { transition-delay: 320ms; }

        @media (prefers-reduced-motion: reduce) {
            .reveal-on-scroll,
            .reveal-on-scroll-left,
            .reveal-on-scroll-right,
            .reveal-on-scroll-scale {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }
    </style>

    @livewireStyles
</head>

<body class="bg-[#f8faf9] text-slate-800 antialiased min-h-full selection:bg-[#0e382c] selection:text-white flex flex-col justify-start">
    
    <div class="w-full min-h-screen flex flex-col justify-between">
        {{ $slot }}
    </div>

    @livewireScripts
    @stack('scripts')

    {{-- Native Lightweight Scroll Reveal Observer with Livewire 3 Morph Sync --}}
    <script>
        (function() {
            let observer = null;

            function getObserver() {
                if (observer) return observer;
                if (!('IntersectionObserver' in window)) return null;

                observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.05,
                    rootMargin: '0px 0px 50px 0px'
                });

                return observer;
            }

            function checkAndReveal() {
                const obs = getObserver();
                const targets = document.querySelectorAll(
                    '.reveal-on-scroll:not(.is-revealed), .reveal-on-scroll-left:not(.is-revealed), .reveal-on-scroll-right:not(.is-revealed), .reveal-on-scroll-scale:not(.is-revealed)'
                );

                if (!obs) {
                    targets.forEach(el => el.classList.add('is-revealed'));
                    return;
                }

                targets.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    // If element is already in the viewport or above it (e.g. after scroll or morph), reveal immediately
                    if (rect.top < window.innerHeight + 60 && rect.bottom > -20) {
                        requestAnimationFrame(() => el.classList.add('is-revealed'));
                    } else if (rect.bottom <= -20) {
                        // Element is already scrolled past above viewport
                        el.classList.add('is-revealed');
                    } else {
                        // Element is below viewport, observe for scroll
                        obs.observe(el);
                    }
                });
            }

            function setupLivewireHooks() {
                if (!window.Livewire || window._livewireRevealHooksRegistered) return;
                window._livewireRevealHooksRegistered = true;

                // 1. Before Livewire morphs existing elements:
                // If el already had 'is-revealed', ensure incoming toEl retains 'is-revealed'
                // This prevents product cards from vanishing when clicking products or opening Quick View modal!
                Livewire.hook('morph.updating', ({ el, toEl }) => {
                    if (el && el.classList && el.classList.contains('is-revealed')) {
                        if (toEl && toEl.classList) {
                            toEl.classList.add('is-revealed');
                        }
                    }
                });

                // 2. When morph finishes (e.g. category changed, search input, modal toggle)
                Livewire.hook('morphed', () => {
                    requestAnimationFrame(checkAndReveal);
                });

                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        requestAnimationFrame(checkAndReveal);
                    });
                });
            }

            // Bind listeners
            document.addEventListener('DOMContentLoaded', () => {
                checkAndReveal();
                setupLivewireHooks();
            });

            document.addEventListener('livewire:init', () => {
                setupLivewireHooks();
                checkAndReveal();
            });

            document.addEventListener('livewire:navigated', () => {
                checkAndReveal();
                setupLivewireHooks();
            });

            window.addEventListener('scroll-reveal-refresh', checkAndReveal);

            // In case Livewire is already initialized before script runs
            if (window.Livewire) {
                setupLivewireHooks();
            }
        })();
    </script>
</body>
</html>
