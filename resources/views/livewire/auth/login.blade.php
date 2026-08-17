@php
    $setting = \App\Models\Setting::first();
    $currentShopName = $setting?->shop_name ?? 'POS Cafe';
    $wifiName = $setting?->wifi_name ?: 'Cafe_Free_WiFi';
    $wifiPass = $setting?->wifi_password ?: 'ngopidulu2026';
    $totalMenu = \App\Models\Product::count();
    $activeShift = \App\Models\CashierShift::with('user')->where('status', 'open')->latest()->first();
    
    $logoPath = null;
    if (file_exists(public_path('images/logo.png'))) {
        $logoPath = asset('images/logo.png');
    } elseif (file_exists(public_path('images/logo.svg'))) {
        $logoPath = asset('images/logo.svg');
    } elseif (file_exists(public_path('images/logo.jpg'))) {
        $logoPath = asset('images/logo.jpg');
    }
@endphp

<div class="min-h-screen w-full flex flex-col justify-between p-3.5 sm:p-6 lg:p-8 bg-slate-50 dark:bg-slate-950 transition-colors duration-300" 
     x-data="{ showPassword: false }">
    
    {{-- ========================================================================= --}}
    {{-- 1. TOP BAR HEADER                                                         --}}
    {{-- ========================================================================= --}}
    <header class="w-full max-w-5xl mx-auto flex items-center justify-between py-2 sm:py-3">
        <div class="flex items-center space-x-3">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="{{ $currentShopName }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl object-contain bg-white dark:bg-slate-800 p-0.5 border border-slate-200 dark:border-slate-700 shadow-2xs">
            @else
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-center shadow-2xs">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"></path>
                    </svg>
                </div>
            @endif
            <div>
                <span class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white leading-tight block">{{ $currentShopName }}</span>
                <span class="text-[9px] sm:text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block uppercase tracking-wider">Point of Sale & Management</span>
            </div>
        </div>

        {{-- Theme Switcher Button --}}
        <button @click="toggleTheme()" 
            type="button"
            class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-all text-xs font-medium flex items-center gap-1.5 sm:gap-2 shadow-2xs focus:outline-none"
            title="Ganti Tema">
            <template x-if="darkMode">
                <div class="flex items-center gap-1.5 text-emerald-400">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                    </svg>
                    <span class="hidden sm:inline">Mode Terang</span>
                </div>
            </template>
            <template x-if="!darkMode">
                <div class="flex items-center gap-1.5 text-slate-600">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <span class="hidden sm:inline">Mode Gelap</span>
                </div>
            </template>
        </button>
    </header>

    {{-- ========================================================================= --}}
    {{-- 2. BENTO GRID CAFE HUB                                                    --}}
    {{-- ========================================================================= --}}
    <main class="w-full max-w-5xl mx-auto my-auto py-3 sm:py-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-stretch">
            
            {{-- ================================================================= --}}
            {{-- BENTO TILE 1: MAIN LOGIN FORM (7 / 12)                            --}}
            {{-- ================================================================= --}}
            <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-8 lg:p-9 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
                <div>
                    {{-- Form Header --}}
                    <div class="mb-5 sm:mb-6">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-400 text-[10px] sm:text-[11px] font-semibold mb-2 sm:mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Portal Akses Kasir & Manajemen</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Masuk ke Sistem
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Gunakan akun kasir atau administrator untuk melanjutkan.
                        </p>
                    </div>

                    {{-- Status / Error Alert --}}
                    @if (session()->has('success') || session()->has('status'))
                        <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl text-xs font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ session('success') ?? session('status') }}</span>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form wire:submit.prevent="login" class="space-y-3.5 sm:space-y-4">
                        
                        {{-- Email Input --}}
                        <div class="space-y-1 sm:space-y-1.5">
                            <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Email Akun <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                                    </svg>
                                </span>
                                <input type="email" id="email" wire:model.defer="email" placeholder="kasir@cafe.com" autofocus
                                    class="w-full pl-10 pr-3.5 py-2.5 sm:py-3 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all">
                            </div>
                            @error('email')
                                <p class="mt-1 text-xs text-rose-500 dark:text-rose-400 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Password Input --}}
                        <div class="space-y-1 sm:space-y-1.5">
                            <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                                    </svg>
                                </span>
                                <input :type="showPassword ? 'text' : 'password'" id="password" wire:model.defer="password" placeholder="••••••••"
                                    class="w-full pl-10 pr-10 py-2.5 sm:py-3 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all">
                                
                                {{-- Show/Hide Toggle Button --}}
                                <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors"
                                    title="Tampilkan / Sembunyikan Sandi">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <svg x-show="showPassword" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs text-rose-500 dark:text-rose-400 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Remember Me Checkbox --}}
                        <div class="flex items-center justify-between pt-1">
                            <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="remember" wire:model="remember"
                                    class="w-4 h-4 text-emerald-600 border-slate-300 dark:border-slate-600 rounded focus:ring-emerald-500 bg-white dark:bg-slate-800 cursor-pointer">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Ingat saya</span>
                            </label>
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-2">
                            <button type="submit" 
                                wire:loading.attr="disabled" 
                                wire:target="login"
                                class="w-full h-11 sm:h-12 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-all duration-150 active:scale-[0.99] disabled:opacity-75 disabled:cursor-not-allowed select-none flex items-center justify-center gap-2 cursor-pointer">
                                
                                {{-- Spinner (Hanya muncul saat loading) --}}
                                <svg wire:loading wire:target="login" class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>

                                {{-- Teks Tombol --}}
                                <span wire:loading.remove wire:target="login">Masuk ke Akun</span>
                                <span wire:loading wire:target="login" class="tracking-wide">Memverifikasi Akun...</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Card Footnote --}}
                <div class="mt-5 sm:mt-6 pt-3.5 sm:pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10px] sm:text-[11px] text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path></svg>
                        <span>Sesi Aman & Terenkripsi</span>
                    </span>
                    <span>Sistem POS Cafe</span>
                </div>
            </div>

            {{-- ================================================================= --}}
            {{-- BENTO TILE 2: SIDE WIDGETS COLUMN (5 / 12)                        --}}
            {{-- ================================================================= --}}
            <div class="lg:col-span-5 flex flex-col gap-3.5 sm:gap-4 lg:gap-5 justify-between">
                
                {{-- WIDGET A: LIVE REALTIME CLOCK --}}
                <div class="bg-slate-900 dark:bg-slate-900 text-white rounded-2xl p-4 sm:p-5 lg:p-6 border border-slate-800 shadow-sm flex flex-col justify-between"
                     x-data="{ 
                        clock: '', 
                        date: '',
                        update() {
                            const now = new Date();
                            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            this.date = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
                            this.clock = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
                        }
                     }"
                     x-init="update(); setInterval(() => update(), 1000)">
                    
                    <div class="flex items-center justify-between mb-2 sm:mb-3">
                        @if($activeShift)
                            <div class="inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-0.5 rounded-md bg-emerald-950/60 border border-emerald-800/60 text-emerald-400 text-[10px] font-bold uppercase tracking-wider" title="Shift kasir aktif: {{ $activeShift->user?->name ?? 'Kasir' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Shift Sedang Aktif</span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-0.5 rounded-md bg-slate-800 border border-slate-700 text-slate-300 text-[10px] font-bold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                <span>Shift Belum Dibuka</span>
                            </div>
                        @endif
                        <span class="text-[11px] sm:text-xs text-slate-400 font-mono" x-text="date"></span>
                    </div>

                    <div class="my-1.5 sm:my-2">
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-extrabold font-mono tracking-tight text-white" x-text="clock">
                            {{ date('H:i:s') }}
                        </div>
                        <p class="text-[11px] sm:text-xs text-slate-400 mt-1">Waktu Server & Transaksi Kasir</p>
                    </div>

                    <div class="pt-2.5 sm:pt-3 border-t border-slate-800 flex items-center justify-between text-[11px] sm:text-xs text-slate-400">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Menu Terdaftar:</span>
                        </span>
                        <span class="font-bold text-slate-200">{{ $totalMenu }} Item Menu</span>
                    </div>
                </div>

                {{-- WIDGET B: WIFI ACCESS BADGE --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center justify-between transition-colors">
                    <div class="flex items-center space-x-3 sm:space-x-3.5 min-w-0">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[9px] sm:text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider block">WiFi Cafe Pengunjung</span>
                            <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white block truncate" title="{{ $wifiName }}">{{ $wifiName }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0 pl-2">
                        <span class="text-[9px] sm:text-[10px] text-slate-400 block font-medium">Password:</span>
                        <span class="text-[11px] sm:text-xs font-mono font-bold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">{{ $wifiPass }}</span>
                    </div>
                </div>

                {{-- WIDGET C: QUICK FEATURE HIGHLIGHT --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
                    <div class="flex items-center justify-between mb-2.5 sm:mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"></path></svg>
                            </div>
                            <h4 class="text-[11px] sm:text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Modul Operasional</h4>
                        </div>
                        <span class="text-[9px] sm:text-[10px] text-slate-400 font-medium">Siap Pakai</span>
                    </div>

                    <div class="grid grid-cols-3 gap-1.5 sm:gap-2 text-center text-[10px] sm:text-[11px]">
                        <div class="p-2 sm:p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800/80">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center mx-auto mb-1">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 block text-[11px] sm:text-xs">Kasir POS</span>
                            <span class="text-[9px] sm:text-[10px] text-slate-400">Meja & Struk</span>
                        </div>

                        <div class="p-2 sm:p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800/80">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center mx-auto mb-1">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 block text-[11px] sm:text-xs">Shift Kasir</span>
                            <span class="text-[9px] sm:text-[10px] text-slate-400">Rekap Kas</span>
                        </div>

                        <div class="p-2 sm:p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800/80">
                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center mx-auto mb-1">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 block text-[11px] sm:text-xs">Laba & HPP</span>
                            <span class="text-[9px] sm:text-[10px] text-slate-400">Margin Menu</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    {{-- ========================================================================= --}}
    {{-- 3. FOOTER                                                                 --}}
    {{-- ========================================================================= --}}
    <footer class="w-full max-w-5xl mx-auto text-center py-2 text-[11px] sm:text-xs text-slate-400 dark:text-slate-500">
        &copy; {{ date('Y') }} {{ $currentShopName }}. Point of Sale & Cafe Management System.
    </footer>

</div>