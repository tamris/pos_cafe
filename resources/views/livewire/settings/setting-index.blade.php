<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', ['title' => 'Pengaturan Toko & Struk', 'subtitle' => 'Atur identitas cafe & informasi WiFi pada struk'])

        <main class="p-6">
            
            {{-- Flash Message --}}
            @if (session()->has('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3 animate-fade-in-down">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <p class="text-green-800 dark:text-green-300 font-medium text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- KOLOM KIRI: FORM INPUT --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-colors">
                        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Edit Informasi Cafe & Struk</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Perubahan akan langsung terlihat pada preview struk ala cafe di samping.</p>
                        </div>

                        <form wire:submit.prevent="update" class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nama Toko --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nama Cafe / Toko</label>
                                    <input type="text" wire:model.live="shop_name" 
                                        class="w-full px-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                        placeholder="Contoh: Cafe Noli">
                                    @error('shop_name') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- No Telepon --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">No. Telepon / WA Cafe</label>
                                    <input type="text" wire:model.live="phone" 
                                        class="w-full px-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                        placeholder="0812...">
                                    @error('phone') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Alamat --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Alamat Lengkap Cafe</label>
                                    <textarea wire:model.live="address" rows="2" 
                                        class="w-full px-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                        placeholder="Alamat cafe..."></textarea>
                                    @error('address') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- PENGATURAN WIFI CAFE --}}
                                <div class="md:col-span-2 border-t border-slate-100 dark:border-slate-700 pt-4">
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="text-base">📶</span>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Akses WiFi Pengunjung (Dicetak di Struk)</h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Network (SSID)</label>
                                            <input type="text" wire:model.live="wifi_name" 
                                                class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400" 
                                                placeholder="Contoh: CafeNoli_Guest">
                                            @error('wifi_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Password WiFi</label>
                                            <input type="text" wire:model.live="wifi_password" 
                                                class="w-full px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400" 
                                                placeholder="Contoh: nolicoffee2026">
                                            @error('wifi_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer Struk --}}
                                <div class="md:col-span-2 border-t border-slate-100 dark:border-slate-700 pt-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Catatan Bawah Struk (Footer)</label>
                                    <input type="text" wire:model.live="receipt_footer" 
                                        class="w-full px-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all" 
                                        placeholder="Terima kasih telah berkunjung ke Cafe Noli! ☕">
                                    @error('receipt_footer') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                                <button type="submit" class="bg-slate-900 dark:bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-all font-semibold shadow-lg shadow-slate-300 dark:shadow-none text-sm">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- KOLOM KANAN: LIVE PREVIEW STRUK ALA CAFE --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-6">
                        <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Preview Struk Ala Cafe</h3>
                        
                        <div class="bg-white mx-auto shadow-xl rounded-sm" 
                             style="width: 280px; 
                                    padding: 16px 12px; 
                                    color: #000; 
                                    border: 1px solid #e2e8f0;
                                    font-family: 'Courier New', Courier, monospace !important;">
                            
                            <style>
                                .receipt-preview * {
                                    font-family: 'Courier New', Courier, monospace !important;
                                    line-height: 1.25;
                                    color: #000 !important;
                                }
                                .receipt-bold { font-weight: 900 !important; }
                                .receipt-dashed { border-bottom: 1px dashed #000; margin: 6px 0; }
                                .receipt-text-sm { font-size: 11px; }
                                .receipt-text-xs { font-size: 9.5px; }
                            </style>

                            <div class="receipt-preview">
                                {{-- HEADER --}}
                                <div class="text-center mb-2">
                                    <div style="font-size: 18px; margin-bottom: 2px;">☕</div>
                                    <h2 class="receipt-bold" style="font-size: 15px; margin-bottom: 2px; text-transform: uppercase;">
                                        {{ $shop_name ?: 'CAFE NOLI' }}
                                    </h2>
                                    <p class="receipt-text-xs" style="margin-bottom: 2px;">{{ $address ?: 'Jl. Kopi Arabica No. 8, Jakarta' }}</p>
                                    <p class="receipt-text-xs">Telp: {{ $phone ?: '0812-3456-7890' }}</p>
                                </div>

                                <div class="receipt-dashed"></div>

                                {{-- BADGE ORDER --}}
                                <div style="border: 1.5px solid #000; padding: 4px; text-align: center; font-weight: bold; font-size: 11px; margin: 6px 0; text-transform: uppercase;">
                                    [ DINE IN - MEJA 04 ]
                                </div>

                                {{-- META META --}}
                                <div style="margin-bottom: 6px;" class="receipt-text-xs">
                                    <p>No Inv: INV-20260813-001</p>
                                    <p>Waktu : 13/08/2026 12:30</p>
                                    <p>Barista: Admin Cafe</p>
                                    <p>Pelanggan: <strong>Budi</strong></p>
                                </div>

                                <div class="receipt-dashed"></div>

                                {{-- ITEMS SAMPLE CAFE --}}
                                <div style="margin-bottom: 5px;" class="receipt-text-sm">
                                    <p class="receipt-bold">Iced Caramel Latte</p>
                                    <div style="display: flex; justify-content: space-between;">
                                        <span>1 x 32.000</span>
                                        <span>32.000</span>
                                    </div>
                                    <p class="receipt-text-xs" style="font-style: italic; margin-left: 4px;">* Sugar: Less Sugar | Ice: Normal</p>
                                </div>

                                <div style="margin-bottom: 5px;" class="receipt-text-sm">
                                    <p class="receipt-bold">Croissant Butter</p>
                                    <div style="display: flex; justify-content: space-between;">
                                        <span>1 x 25.000</span>
                                        <span>25.000</span>
                                    </div>
                                </div>

                                <div class="receipt-dashed"></div>

                                {{-- CALCULATIONS --}}
                                <div class="receipt-text-sm">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                        <span>Subtotal:</span>
                                        <span>Rp 57.000</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                        <span>Diskon:</span>
                                        <span>Rp 0</span>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: bold; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; margin: 4px 0;">
                                    <span>TOTAL:</span>
                                    <span>Rp 57.000</span>
                                </div>

                                <div class="receipt-text-sm">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                        <span>Bayar (CASH):</span>
                                        <span>Rp 100.000</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                        <span>Kembali:</span>
                                        <span>Rp 43.000</span>
                                    </div>
                                </div>

                                {{-- WIFI PREVIEW --}}
                                @if(!empty($wifi_name) || !empty($wifi_password))
                                    <div style="border: 1px dashed #000; padding: 6px; margin: 8px 0; text-align: center;" class="receipt-text-xs">
                                        <p class="receipt-bold" style="letter-spacing: 0.5px;">📶 FREE WIFI CAFE</p>
                                        @if(!empty($wifi_name))
                                            <p>SSID: <strong>{{ $wifi_name }}</strong></p>
                                        @endif
                                        @if(!empty($wifi_password))
                                            <p>PASS: <strong>{{ $wifi_password }}</strong></p>
                                        @endif
                                    </div>
                                @else
                                    <div class="receipt-dashed"></div>
                                @endif

                                {{-- FOOTER --}}
                                <div class="text-center receipt-text-xs" style="margin-top: 6px;">
                                    <p class="receipt-bold" style="font-size: 10px;">{{ $shop_name ?: 'CAFE NOLI' }}</p>
                                    <p style="margin-top: 2px;">{{ $receipt_footer ?: 'Terima kasih telah berkunjung! ☕' }}</p>
                                    <p style="margin-top: 6px; font-size: 8.5px; letter-spacing: 0.5px;">-- PAUSED TO REFRESH & ENJOY --</p>
                                </div>

                            </div>
                        </div>

                        {{-- RECEIPT RIPPLE BOTTOM --}}
                        <div class="bg-slate-50 dark:bg-slate-950 h-3 w-[280px] mx-auto relative -mt-1 overflow-hidden transition-colors">
                            <div class="absolute top-0 left-0 right-0 flex justify-center">
                                @for($i=0; $i<23; $i++)
                                    <div class="w-3 h-3 bg-white rounded-full -mt-2 mx-[0.5px]"></div>
                                @endfor
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </main>
    </div>
</div>