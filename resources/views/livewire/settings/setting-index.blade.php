<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="main-content-layout flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Pengaturan Toko & Struk', 
            'subtitle' => 'Atur identitas cafe & informasi WiFi pada struk'
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- Flash Message --}}
            @if (session()->has('success'))
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 flex items-center gap-3 animate-fade-in">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <p class="text-emerald-800 dark:text-emerald-300 font-medium text-xs sm:text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                
                {{-- KOLOM KIRI: FORM PENGATURAN (7 / 12) --}}
                <div class="xl:col-span-7 2xl:col-span-8">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden transition-colors">
                        
                        <div class="p-5 sm:p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Identitas Toko & Format Struk</h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Semua perubahan teks akan langsung terupdate secara real-time pada pratinjau struk di samping.</p>
                        </div>

                        <form wire:submit.prevent="update" class="p-5 sm:p-6 space-y-6">
                            
                            {{-- SECTION 1: LOGO CAFE / HEADER STRUK --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Logo Struk & Identitas Visual</h3>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Upload logo cafe untuk dicetak di header atas struk</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0" title="Aktifkan/Nonaktifkan cetak logo pada struk">
                                        <input type="checkbox" wire:model.live="show_logo_receipt" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-600"></div>
                                        <span class="ml-2 text-[11px] font-semibold text-slate-600 dark:text-slate-300 hidden sm:inline">Cetak Logo</span>
                                    </label>
                                </div>

                                <div class="p-4 rounded-xl border border-slate-200/90 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 space-y-4">
                                    <div class="flex flex-col sm:flex-row items-center gap-5">
                                        {{-- Image Display Area / Thumbnail --}}
                                        <div class="relative group w-24 h-24 sm:w-28 sm:h-28 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 flex items-center justify-center p-2 overflow-hidden shadow-xs shrink-0">
                                            @if ($new_logo)
                                                <img src="{{ $new_logo->temporaryUrl() }}" alt="Preview Logo Baru" class="w-full h-full object-contain filter grayscale contrast-150">
                                                <span class="absolute top-1 right-1 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow-xs">Baru</span>
                                            @elseif ($shop_logo)
                                                <img src="{{ asset('storage/' . $shop_logo) }}" alt="Logo Saat Ini" class="w-full h-full object-contain filter grayscale contrast-150">
                                            @else
                                                <div class="text-center text-slate-400 dark:text-slate-500">
                                                    <svg class="w-8 h-8 mx-auto stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    <span class="text-[10px] block mt-1 font-medium">Belum ada logo</span>
                                                </div>
                                            @endif

                                            {{-- Loading Spinner saat upload gambar --}}
                                            <div wire:loading wire:target="new_logo" class="absolute inset-0 bg-slate-900/70 backdrop-blur-xs flex flex-col items-center justify-center text-white text-xs font-semibold">
                                                <svg class="animate-spin w-6 h-6 text-white mb-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span>Mengunggah...</span>
                                            </div>
                                        </div>

                                        {{-- Actions & Upload Controls --}}
                                        <div class="flex-1 w-full space-y-2.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <label for="shop_logo_input" class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 active:scale-95 text-white text-xs font-semibold transition-colors shadow-xs">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                    <span>{{ ($shop_logo || $new_logo) ? 'Ganti Logo' : 'Upload Logo' }}</span>
                                                </label>
                                                <input id="shop_logo_input" type="file" wire:model="new_logo" accept="image/*" class="sr-only" wire:key="logo-input-{{ $new_logo ? 'selected' : ($shop_logo ? 'has-logo' : 'empty') }}">

                                                @if ($new_logo)
                                                    <button type="button" wire:click="cancelNewLogo" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold transition-all cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        <span>Batal</span>
                                                    </button>
                                                @elseif ($shop_logo)
                                                    <button type="button" onclick="confirmRemoveLogo()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-rose-200/80 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-xs font-semibold transition-all active:scale-95 cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        <span>Hapus Logo</span>
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                                <p>Format yang didukung: <strong>PNG, JPG, JPEG, WebP</strong> (Maks. 5MB).</p>
                                                <p class="text-slate-400 dark:text-slate-500 mt-0.5">💡 <em>Gunakan gambar siluet/logo berlatar transparan atau putih dengan kontras jelas untuk hasil cetak thermal hitam-putih yang tajam.</em></p>
                                            </div>

                                            @error('new_logo') 
                                                <span class="text-rose-500 text-xs font-medium block">{{ $message }}</span> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>


                            </div>

                            {{-- SECTION 2: INFORMASI CAFE --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Informasi Cafe</h3>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Nama Cafe --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                            Nama Cafe / Outlet <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                            </span>
                                            <input type="text" wire:model.live="shop_name" 
                                                class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                                placeholder="Contoh: Kopi Senja / Cafe Nusantara">
                                        </div>
                                        @error('shop_name') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- No Telepon --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                            Nomor WhatsApp / Telepon
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            </span>
                                            <input type="text" wire:model.live="phone" 
                                                class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                                placeholder="Contoh: 0812-3456-7890">
                                        </div>
                                        @error('phone') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Alamat Lengkap --}}
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                            Alamat Outlet / Cafe
                                        </label>
                                        <div class="relative">
                                            <textarea wire:model.live="address" rows="2" 
                                                class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                                placeholder="Jl. Kopi No. 12, Tegal"></textarea>
                                        </div>
                                        @error('address') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 3: AKSES WIFI PENGUNJUNG --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Akses WiFi Pengunjung</h3>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Informasi akan dicetak pada baris bawah struk transaksi</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                            Nama Network (SSID)
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                                            </span>
                                            <input type="text" wire:model.live="wifi_name" 
                                                class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 transition-all" 
                                                placeholder="Contoh: Cafe_Free_WiFi">
                                        </div>
                                        @error('wifi_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                            Password WiFi
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                            </span>
                                            <input type="text" wire:model.live="wifi_password" 
                                                class="w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 transition-all" 
                                                placeholder="Contoh: ngopidulu2026">
                                        </div>
                                        @error('wifi_password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 4: FOOTER STRUK --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                    </div>
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Pesan Footer Struk</h3>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                        Ucapan / Catatan Bawah
                                    </label>
                                    <div class="relative">
                                        <input type="text" wire:model.live="receipt_footer" 
                                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                            placeholder="Contoh: Terima kasih atas kunjungannya! ☕">
                                    </div>
                                    @error('receipt_footer') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- SECTION 5: OTOMATISASI CETAK STRUK & TIKET DAPUR --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Otomatisasi Cetak Struk</h3>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Atur apakah struk langsung dicetak begitu transaksi selesai di kasir</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Switch 1: Auto Print Struk Pelanggan --}}
                                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40 flex items-center justify-between gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-900 dark:text-white mb-0.5">
                                                Cetak Struk Pelanggan
                                            </label>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                                Otomatis cetak struk pembayaran belanja
                                            </p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                            <input type="checkbox" wire:model.live="auto_print_receipt" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-600"></div>
                                        </label>
                                    </div>

                                    {{-- Switch 2: Auto Print Tiket Dapur / Kitchen --}}
                                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40 flex items-center justify-between gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-900 dark:text-white mb-0.5">
                                                Cetak Tiket Dapur / Kitchen
                                            </label>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                                Otomatis cetak tiket pesanan barista/dapur
                                            </p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                            <input type="checkbox" wire:model.live="auto_print_kitchen" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-600"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 6: PAYMENT GATEWAY MIDTRANS --}}
                            <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Payment Gateway (Midtrans)</h3>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Status koneksi pembayaran online QRIS & Self-Order</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $midtransIsProduction ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $midtransIsProduction ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ $midtransIsProduction ? 'Production Mode' : 'Sandbox (Testing)' }}
                                    </span>
                                </div>

                                <div class="p-4 rounded-xl border border-slate-200/90 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 space-y-3 text-xs">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Merchant ID</span>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $midtransMerchantId ?: '(Belum diatur)' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Client Key</span>
                                            <span class="font-mono text-slate-800 dark:text-slate-200">{{ $midtransClientKey ? substr($midtransClientKey, 0, 12) . '...' : '(Belum diatur)' }}</span>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                                        <span class="text-slate-500 dark:text-slate-400 text-[11px] font-medium block mb-1">
                                            <strong>URL Webhook / Notification:</strong> (Salin URL ini ke Dashboard Midtrans &rarr; Settings &rarr; Configuration)
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <input type="text" readonly value="{{ url('/api/midtrans/notification') }}" id="midtransWebhookInput"
                                                   class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-1.5 font-mono text-[11px] text-slate-700 dark:text-slate-300 select-all">
                                            <button type="button" 
                                                    onclick="navigator.clipboard.writeText(document.getElementById('midtransWebhookInput').value); alert('URL Webhook berhasil disalin!');"
                                                    class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shrink-0 cursor-pointer">
                                                Salin
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit CTA --}}
                            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/80 flex justify-end">
                                <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-6 py-2.5 sm:py-3 rounded-lg transition-all font-semibold text-xs sm:text-sm shadow-sm active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Simpan Pengaturan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- KOLOM KANAN: LIVE PREVIEW STRUK (5 / 12) --}}
                <div class="xl:col-span-5 2xl:col-span-4 flex flex-col items-center">
                    <div class="xl:sticky xl:top-6 w-full flex flex-col items-center">
                        
                        <div class="w-full flex items-center justify-between mb-3 px-1">
                            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Preview Struk Real-Time</span>
                            </h3>
                            <span class="text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold px-2 py-0.5 rounded-full">
                                Ukuran 58mm
                            </span>
                        </div>

                        {{-- Frame Container Struk --}}
                        <div class="w-full bg-slate-100 dark:bg-slate-800/80 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 flex justify-center shadow-inner">
                            <div class="receipt-paper w-full max-w-[240px] bg-white shadow-xl rounded-t-sm p-3 text-slate-950 font-mono select-none"
                                 style="font-family: 'Courier New', Courier, monospace !important; border: 1px solid #e2e8f0;">
                                
                                <style>
                                    .receipt-paper * {
                                        font-family: 'Courier New', Courier, monospace, 'Lucida Console' !important;
                                        color: #000 !important;
                                        line-height: 1.45;
                                    }
                                    .receipt-paper .divider {
                                        border-bottom: 1px dashed #000;
                                        margin: 8px 0;
                                        width: 100%;
                                    }
                                </style>

                                {{-- 1. HEADER CAFE & LOGO --}}
                                <div class="text-center mb-2">
                                    @if ($show_logo_receipt && ($new_logo || $shop_logo))
                                        <div class="flex justify-center mb-1.5">
                                            @if ($new_logo)
                                                <img src="{{ $new_logo->temporaryUrl() }}" alt="Logo Cafe" class="max-h-12 max-w-[130px] object-contain filter grayscale contrast-200">
                                            @elseif ($shop_logo)
                                                <img src="{{ asset('storage/' . $shop_logo) }}" alt="Logo Cafe" class="max-h-12 max-w-[130px] object-contain filter grayscale contrast-200">
                                            @endif
                                        </div>
                                    @endif

                                    <div class="font-black text-xs uppercase tracking-tight">
                                        {{ $shop_name ?: 'POS CAFE & ROASTERY' }}
                                    </div>
                                    @if(!empty($address))
                                        <div class="text-[9.5px] leading-tight mt-0.5">{{ $address }}</div>
                                    @endif
                                    @if(!empty($phone))
                                        <div class="text-[9.5px] leading-tight">Telp: {{ $phone }}</div>
                                    @endif
                                </div>

                                <div class="divider"></div>

                                {{-- 2. METADATA TRANSAKSI & TIPE PESANAN --}}
                                <div class="text-[10px] space-y-0.5 my-1">
                                    <div class="flex justify-between">
                                        <span>No. Inv</span>
                                        <span class="font-bold">INV-{{ date('Ymd') }}-001</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Waktu</span>
                                        <span>{{ date('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Kasir</span>
                                        <span>{{ auth()->user()->name ?? 'Staff' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Pesanan</span>
                                        <span class="font-bold uppercase">DINE IN (MEJA 04)</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Pelanggan</span>
                                        <span class="font-bold">Budi Santoso</span>
                                    </div>
                                </div>

                                <div class="divider"></div>

                                {{-- 3. DAFTAR MENU / DETAIL ITEMS --}}
                                <div class="space-y-2 text-[10.5px] my-1">
                                    <div>
                                        <div class="font-bold text-[11px]">Iced Caramel Latte</div>
                                        <div class="flex justify-between text-[10px]">
                                            <span>1 x 32.000</span>
                                            <span class="font-bold">32.000</span>
                                        </div>
                                        <div class="text-[9px] italic pl-1.5 text-slate-700">* Less Sugar | Normal Ice</div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-[11px]">Butter Croissant</div>
                                        <div class="flex justify-between text-[10px]">
                                            <span>1 x 25.000</span>
                                            <span class="font-bold">25.000</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="divider"></div>

                                {{-- 4. PERHITUNGAN PEMBAYARAN --}}
                                <div class="text-[10.5px] space-y-0.5 my-1">
                                    <div class="flex justify-between">
                                        <span>Subtotal</span>
                                        <span>57.000</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Diskon (0%)</span>
                                        <span>0</span>
                                    </div>
                                </div>

                                <div class="flex justify-between text-xs font-black py-1 my-1 border-y border-dashed border-black">
                                    <span>TOTAL</span>
                                    <span>Rp 57.000</span>
                                </div>

                                <div class="text-[10.5px] space-y-0.5 my-1">
                                    <div class="flex justify-between">
                                        <span>Bayar (TUNAI)</span>
                                        <span>100.000</span>
                                    </div>
                                    <div class="flex justify-between font-bold">
                                        <span>Kembali</span>
                                        <span>43.000</span>
                                    </div>
                                </div>

                                {{-- 5. WIFI CAFE (INLINE) --}}
                                @if(!empty($wifi_name) || !empty($wifi_password))
                                    <div class="divider"></div>
                                    <div class="text-center text-[9.5px] my-1">
                                        <span>WiFi: <strong>{{ $wifi_name ?: '-' }}</strong></span>
                                        @if(!empty($wifi_password))
                                            <span> | Pass: <strong>{{ $wifi_password }}</strong></span>
                                        @endif
                                    </div>
                                @endif

                                <div class="divider"></div>

                                {{-- 6. FOOTER --}}
                                <div class="text-center text-[9.5px] mt-2">
                                    <div>{{ $receipt_footer ?: 'Terima kasih atas kunjungannya!' }}</div>
                                    <div class="text-[8px] text-slate-600 mt-1 tracking-wide">-- Have a Good Coffee Day --</div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    function confirmRemoveLogo() {
        Swal.fire({
            title: 'Hapus Logo Struk?',
            html: `<div class="text-left text-xs space-y-2">
                    <p class="text-slate-600 dark:text-slate-400">File logo cafe akan dihapus dari sistem dan struk akan kembali dicetak dalam format teks tanpa logo.</p>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Hapus Logo',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('removeLogo');
            }
        });
    }
</script>
@endpush