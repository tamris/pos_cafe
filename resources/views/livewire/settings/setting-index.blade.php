<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="if (window.innerWidth >= 1280) { sidebarOpen = true } else { sidebarOpen = false }">
    
    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
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
                            
                            {{-- SECTION 1: INFORMASI UMUM --}}
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-700/80">
                                    <span class="text-base">☕</span>
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Informasi Cafe</h3>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Nama Cafe --}}
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Nama Cafe / Outlet <span class="text-rose-500">*</span></label>
                                        <input type="text" wire:model.live="shop_name" 
                                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                            placeholder="Contoh: Kopi Senja / Cafe Nusantara">
                                        @error('shop_name') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- No Telepon --}}
                                    <div class="sm:col-span-2 sm:max-w-md">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Nomor WhatsApp / Telepon</label>
                                        <input type="text" wire:model.live="phone" 
                                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                            placeholder="Contoh: 0812-3456-7890">
                                        @error('phone') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Alamat Lengkap --}}
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Alamat Outlet / Cafe</label>
                                        <textarea wire:model.live="address" rows="2" 
                                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                            placeholder="Jl. Kopi No. 12, Tegal"></textarea>
                                        @error('address') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 2: AKSES WIFI PENGUNJUNG --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-700/80">
                                    <span class="text-base">📶</span>
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Akses WiFi Pengunjung (Cetak di Struk)</h3>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/80 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1 uppercase">Nama Network (SSID)</label>
                                        <input type="text" wire:model.live="wifi_name" 
                                            class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400" 
                                            placeholder="Contoh: Cafe_Free_WiFi">
                                        @error('wifi_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1 uppercase">Password WiFi</label>
                                        <input type="text" wire:model.live="wifi_password" 
                                            class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400" 
                                            placeholder="Contoh: ngopidulu2026">
                                        @error('wifi_password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 3: FOOTER STRUK --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-700/80">
                                    <span class="text-base">📝</span>
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Pesan Footer Struk</h3>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Ucapan / Catatan Bawah</label>
                                    <input type="text" wire:model.live="receipt_footer" 
                                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                        placeholder="Contoh: Terima kasih atas kunjungannya! ☕">
                                    @error('receipt_footer') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Submit CTA --}}
                            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/80 flex justify-end">
                                <button type="submit" class="w-full sm:w-auto bg-slate-900 dark:bg-blue-600 text-white px-6 py-2.5 sm:py-3 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-all font-semibold text-xs sm:text-sm shadow-sm active:scale-95 flex items-center justify-center gap-2">
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
                            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                <span>🧾</span>
                                <span>Preview Struk Real-Time</span>
                            </h3>
                            <span class="text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold px-2 py-0.5 rounded-full">
                                Ukuran 58mm / 80mm
                            </span>
                        </div>

                        {{-- Frame Container Struk --}}
                        <div class="w-full bg-slate-100 dark:bg-slate-800/80 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 flex justify-center shadow-inner">
                            <div class="receipt-paper w-full max-w-[280px] bg-white shadow-xl rounded-t-sm p-4 text-slate-950 font-mono select-none"
                                 style="font-family: 'Courier New', Courier, monospace !important; border: 1px solid #e2e8f0;">
                                
                                <style>
                                    .receipt-paper * {
                                        font-family: 'Courier New', Courier, monospace !important;
                                        line-height: 1.25;
                                        color: #050505 !important;
                                    }
                                    .receipt-divider { border-bottom: 1px dashed #000; margin: 6px 0; }
                                </style>

                                {{-- HEADER --}}
                                <div class="text-center mb-2">
                                    <div class="text-lg mb-1">☕</div>
                                    <h2 class="font-black text-sm uppercase tracking-tight">
                                        {{ $shop_name ?: 'NAMA CAFE / TOKO' }}
                                    </h2>
                                    <p class="text-[10px] mt-0.5 leading-tight">{{ $address ?: 'Alamat belum diatur' }}</p>
                                    <p class="text-[10px]">Telp: {{ $phone ?: '-' }}</p>
                                </div>

                                <div class="receipt-divider"></div>

                                {{-- BADGE ORDER SAMPLE --}}
                                <div class="border border-black py-1 px-2 text-center font-black text-[11px] my-1 uppercase">
                                    [ DINE IN - MEJA 04 ]
                                </div>

                                {{-- META DATA --}}
                                <div class="text-[9.5px] space-y-0.5 my-1.5">
                                    <p>No Inv: INV-{{ date('Ymd') }}-001</p>
                                    <p>Waktu : {{ date('d/m/Y H:i') }}</p>
                                    <p>Kasir : {{ auth()->user()->name ?? 'Kasir Cafe' }}</p>
                                    <p>Cust  : <strong>Budi Santoso</strong></p>
                                </div>

                                <div class="receipt-divider"></div>

                                {{-- ITEMS SAMPLE --}}
                                <div class="space-y-1.5 text-[11px] my-2">
                                    <div>
                                        <p class="font-bold">Iced Caramel Latte</p>
                                        <div class="flex justify-between">
                                            <span>1 x 32.000</span>
                                            <span>32.000</span>
                                        </div>
                                        <p class="text-[9px] italic text-slate-600 pl-1">* Less Sugar | Normal Ice</p>
                                    </div>
                                    <div>
                                        <p class="font-bold">Butter Croissant</p>
                                        <div class="flex justify-between">
                                            <span>1 x 25.000</span>
                                            <span>25.000</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="receipt-divider"></div>

                                {{-- CALCULATIONS --}}
                                <div class="text-[11px] space-y-0.5">
                                    <div class="flex justify-between">
                                        <span>Subtotal</span>
                                        <span>57.000</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Diskon (0%)</span>
                                        <span>0</span>
                                    </div>
                                </div>

                                <div class="flex justify-between text-xs font-black border-y border-dashed border-black py-1.5 my-1.5">
                                    <span>TOTAL</span>
                                    <span>Rp 57.000</span>
                                </div>

                                <div class="text-[11px] space-y-0.5">
                                    <div class="flex justify-between">
                                        <span>Bayar (CASH)</span>
                                        <span>100.000</span>
                                    </div>
                                    <div class="flex justify-between font-bold">
                                        <span>Kembali</span>
                                        <span>43.000</span>
                                    </div>
                                </div>

                                {{-- WIFI BOX --}}
                                @if(!empty($wifi_name) || !empty($wifi_password))
                                    <div class="border border-dashed border-black p-1.5 my-2 text-center text-[9.5px] space-y-0.5 bg-slate-50">
                                        <p class="font-black tracking-wider">📶 FREE WIFI CAFE</p>
                                        @if(!empty($wifi_name))
                                            <p>SSID: <strong>{{ $wifi_name }}</strong></p>
                                        @endif
                                        @if(!empty($wifi_password))
                                            <p>Pass: <strong>{{ $wifi_password }}</strong></p>
                                        @endif
                                    </div>
                                @else
                                    <div class="receipt-divider"></div>
                                @endif

                                {{-- FOOTER --}}
                                <div class="text-center text-[9px] mt-2 space-y-0.5">
                                    <p class="font-black">{{ $shop_name ?: 'POS CAFE' }}</p>
                                    <p>{{ $receipt_footer ?: 'Terima kasih telah berkunjung! ☕' }}</p>
                                    <p class="text-[8px] tracking-widest text-slate-500 pt-1">-- POWERED BY POS CAFE --</p>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
</div>