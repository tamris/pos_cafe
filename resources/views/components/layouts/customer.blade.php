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
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
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

    {{-- FontAwesome & Google Fonts --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }
        * { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        
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
    </style>

    @livewireStyles
</head>

<body class="bg-stone-100 text-stone-900 antialiased min-h-full selection:bg-amber-600 selection:text-white flex flex-col justify-start">
    
    <div class="w-full min-h-screen flex flex-col justify-between">
        {{ $slot }}
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
