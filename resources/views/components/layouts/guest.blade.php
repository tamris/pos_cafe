<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $shopName = \App\Models\Setting::first()?->shop_name ?? 'POS Cafe';
    @endphp
    <title>{{ $title ?? 'Masuk - ' . $shopName }}</title>
    
    {{-- 1. CDN Tailwind & Config untuk Dark Mode --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151f32',
                            950: '#0a0f1d',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');
        * { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
    </style>

    @livewireStyles
</head>

<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300"
      x-data="{ 
          darkMode: localStorage.getItem('darkMode') === 'true',
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('darkMode', this.darkMode);
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }"
      x-init="
          if (darkMode) document.documentElement.classList.add('dark');
          $watch('darkMode', val => val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark'));
      ">

    {{ $slot }}

    @livewireScripts
</body>

</html>
