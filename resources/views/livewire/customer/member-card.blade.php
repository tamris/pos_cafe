<div class="min-h-screen bg-[#faf8f5] text-slate-800 font-sans selection:bg-[#0e382c] selection:text-white flex flex-col justify-center items-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8 relative overflow-x-hidden">

    {{-- Subtle Ambient Glow --}}
    <div class="absolute -top-24 -left-24 w-80 h-80 bg-emerald-200/25 rounded-full blur-3xl pointer-events-none -z-0"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-amber-200/20 rounded-full blur-3xl pointer-events-none -z-0"></div>

    {{-- Main Container (Perfect Vertical Centering via my-auto) --}}
    <div class="w-full max-w-md sm:max-w-xl lg:max-w-4xl xl:max-w-5xl my-auto relative z-10">

        @if($customer)
            {{-- MEMBER FOUND: ELEGANT EDITORIAL HEADER --}}
            <div class="w-full mb-5 sm:mb-7">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#0e382c] block mb-1">
                    Membership Noli Space
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-heading">
                    Kartu Member <span class="font-editorial italic font-normal text-[#0e382c]">Digital.</span>
                </h2>
            </div>

            {{-- 2-COLUMN BALANCED LAYOUT --}}
            <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

                {{-- LEFT COLUMN: DIGITAL MEMBER CARD & SHORTCUTS (5-COLS ON DESKTOP) --}}
                <div class="lg:col-span-5 space-y-4">
                    
                    {{-- Digital Member Card --}}
                    <div class="relative bg-gradient-to-tr from-[#08231c] via-[#0e382c] to-[#134e3f] rounded-3xl p-6 sm:p-7 text-white shadow-xl shadow-[#0e382c]/20 border border-emerald-400/25 overflow-hidden group hover:scale-[1.01] transition-transform">
                        {{-- Ambient Light Shimmer inside card --}}
                        <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-400/15 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>

                        {{-- Card Top: Brand & Golden Chip --}}
                        <div class="flex justify-between items-start mb-6 relative z-10">
                            <div>
                                <span class="text-[10px] tracking-widest text-emerald-300 font-extrabold uppercase block">DIGITAL MEMBER</span>
                                <h3 class="text-base sm:text-lg font-black tracking-tight text-white font-heading mt-0.5">
                                    {{ $setting->shop_name ?? 'Noli Coffe & Space' }}
                                </h3>
                            </div>
                            {{-- Golden SIM Chip --}}
                            <div class="w-11 h-9 rounded-xl bg-gradient-to-br from-amber-200 via-amber-400 to-amber-600 flex items-center justify-center shadow-md p-1 border border-amber-300/60 shrink-0">
                                <div class="w-full h-full border border-amber-900/30 rounded flex flex-col justify-around p-0.5">
                                    <div class="h-0.5 bg-amber-900/40 w-full"></div>
                                    <div class="h-0.5 bg-amber-900/40 w-full"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Center: Points Balance --}}
                        <div class="my-5 relative z-10">
                            <span class="text-[11px] text-emerald-200/80 font-medium block">Total Saldo Poin:</span>
                            <div class="flex items-baseline gap-2 mt-1">
                                <span class="text-4xl sm:text-5xl font-black text-amber-300 tracking-tight font-heading">⭐ {{ $customer->points_balance }}</span>
                                <span class="text-xs text-emerald-200 font-bold uppercase tracking-wider">Poin</span>
                            </div>
                            <p class="text-xs text-emerald-100/90 mt-1.5 leading-relaxed">
                                Senilai <strong class="text-white font-extrabold">Rp {{ number_format(\App\Services\LoyaltyService::calculatePointValue($customer->points_balance, $setting), 0, ',', '.') }}</strong> untuk potongan belanja di kasir.
                            </p>
                        </div>

                        {{-- Card Bottom: Name & Phone --}}
                        <div class="pt-4 border-t border-white/15 flex justify-between items-end relative z-10 gap-3">
                            <div class="truncate">
                                <span class="text-[10px] text-emerald-200/70 block uppercase tracking-wider font-semibold">Nama Pemegang</span>
                                <span class="text-sm font-black text-white tracking-wide block font-heading truncate">{{ $customer->name }}</span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] text-emerald-200/70 block uppercase tracking-wider font-semibold">No. WhatsApp</span>
                                <span class="text-xs font-mono font-bold text-emerald-100 block tracking-wider">{{ $customer->phone }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- How to Redeem Hint Box --}}
                    <div class="bg-white rounded-3xl p-5 border border-stone-200/80 shadow-sm flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-800 border border-amber-200/60 flex items-center justify-center shrink-0 text-sm mt-0.5">
                            <i class="fas fa-coins text-amber-700"></i>
                        </div>
                        <div class="text-xs text-stone-600 leading-relaxed">
                            <strong class="block text-slate-900 font-bold mb-0.5">Cara Pakai Poin:</strong>
                            Sebutkan nomor WhatsApp kamu ke kasir saat memesan. Minimal penukaran adalah <strong>{{ $setting->point_min_redeem ?? 5 }} poin</strong> untuk langsung dapat potongan belanja!
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-2">
                        <a href="{{ route('customer.order') }}" 
                           class="w-full py-3.5 px-4 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-[0.99] text-white font-black text-xs transition flex items-center justify-center gap-2 shadow-md shadow-[#0e382c]/15">
                            <i class="fas fa-bag-shopping text-emerald-400"></i>
                            <span>Pesan Sekarang Dari Meja</span>
                        </a>
                        <div class="text-center pt-1">
                            <a href="{{ route('customer.landing') }}" 
                               class="text-xs font-bold text-stone-500 hover:text-slate-800 transition">
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: SEARCH / SWITCH NUMBER + HISTORY (7-COLS ON DESKTOP) --}}
                <div class="lg:col-span-7 bg-white rounded-3xl border border-stone-200/80 shadow-sm overflow-hidden flex flex-col">
                    
                    {{-- Search Form on Top --}}
                    <div class="p-5 sm:p-6 border-b border-stone-100 bg-stone-50/50">
                        <form wire:submit.prevent="checkPoints" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-magnifying-glass text-stone-400 text-xs"></i>
                                    <span>Cari / Ganti Nomor Member</span>
                                </label>
                                <span class="text-[11px] text-stone-400 font-medium">WhatsApp ID</span>
                            </div>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                                        <i class="fab fa-whatsapp text-emerald-600 text-base"></i>
                                    </div>
                                    <input type="tel" wire:model="phone" placeholder="Contoh: 081234567890"
                                           class="w-full pl-10 pr-3.5 py-2.5 sm:py-3 text-sm font-semibold rounded-2xl border border-stone-200/90 bg-white text-slate-900 placeholder:text-stone-400 focus:outline-none focus:border-[#0e382c] focus:ring-4 focus:ring-[#0e382c]/10 transition-all">
                                </div>
                                <button type="submit" 
                                        class="px-5 py-2.5 sm:py-3 bg-[#0e382c] hover:bg-[#134e3f] active:scale-[0.98] text-white rounded-2xl font-black text-xs shadow-sm transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                    <span wire:loading.remove>Cari</span>
                                    <span wire:loading class="flex items-center gap-1">
                                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </span>
                                </button>
                            </div>
                            @error('phone') 
                                <span class="text-rose-500 text-[11px] font-medium block mt-1">{{ $message }}</span> 
                            @enderror
                        </form>
                    </div>

                    {{-- History List --}}
                    <div class="p-5 sm:p-6 flex-1 flex flex-col">
                        <div class="flex justify-between items-center pb-3 border-b border-stone-100 mb-2">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-clock-rotate-left text-[#0e382c]"></i>
                                <span>Riwayat Mutasi Poin</span>
                            </h3>
                            <span class="text-[11px] text-stone-400 font-medium">10 Terakhir</span>
                        </div>

                        @if(count($histories) > 0)
                            <div class="divide-y divide-stone-100 max-h-[380px] overflow-y-auto pr-1">
                                @foreach($histories as $h)
                                    <div class="py-3.5 flex justify-between items-center text-xs sm:text-sm first:pt-2 last:pb-2">
                                        <div class="pr-3">
                                            <p class="font-bold text-slate-900">{{ $h->description ?: ($h->type === 'earn' ? 'Poin Belanja Masuk' : 'Poin Ditukar Diskon') }}</p>
                                            <p class="text-[11px] text-stone-400 mt-0.5 flex items-center gap-1.5">
                                                <span>{{ $h->created_at->format('d M Y, H:i') }}</span>
                                                @if($h->transaction)
                                                    <span class="text-stone-300">•</span>
                                                    <span class="font-mono text-[10px] text-stone-500">{{ $h->transaction->invoice_number }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="font-black text-sm sm:text-base block {{ $h->points > 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                                {{ $h->points > 0 ? '+' . $h->points : $h->points }} Poin
                                            </span>
                                            <span class="text-[10px] sm:text-[11px] text-stone-400 block mt-0.5">Saldo: {{ $h->balance_after }} pt</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-stone-400 text-xs my-auto">
                                <i class="fas fa-receipt text-3xl text-stone-300 block mb-2"></i>
                                <span>Belum ada riwayat transaksi poin untuk nomor ini.</span>
                            </div>
                        @endif
                    </div>

                </div>

            </div>

        @else
            {{-- INITIAL / SEARCH STATE (NO CUSTOMER LOADED) --}}
            <div class="w-full max-w-md mx-auto bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/90 shadow-xl shadow-stone-900/5 space-y-6">
                
                <div class="text-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#0e382c] block mb-1">
                        Member Loyalty
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-heading">
                        Cek Saldo <span class="font-editorial italic font-normal text-[#0e382c]">Member Kamu.</span>
                    </h2>
                    <p class="text-xs text-stone-500 mt-1.5 leading-relaxed">
                        Masukkan nomor WhatsApp yang pernah kamu gunakan saat berbelanja atau klaim struk di kafe.
                    </p>
                </div>

                <form wire:submit.prevent="checkPoints" class="space-y-4">
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600">
                                <i class="fab fa-whatsapp text-lg"></i>
                            </div>
                            <input type="tel" wire:model="phone" placeholder="Contoh: 081234567890" autofocus
                                   class="w-full pl-11 pr-4 py-3.5 text-base sm:text-sm font-semibold rounded-2xl border border-stone-200/90 bg-white text-slate-900 placeholder:text-stone-400 focus:outline-none focus:border-[#0e382c] focus:ring-4 focus:ring-[#0e382c]/10 transition-all shadow-2xs">
                        </div>
                        @error('phone') 
                            <span class="text-rose-500 text-[11px] font-medium block mt-1.5">{{ $message }}</span> 
                        @enderror
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-6 bg-[#0e382c] hover:bg-[#134e3f] active:scale-[0.98] text-white rounded-2xl font-black text-xs sm:text-sm shadow-md shadow-[#0e382c]/20 transition flex items-center justify-center gap-2 cursor-pointer">
                        <span wire:loading.remove>Cek Saldo Sekarang</span>
                        <span wire:loading class="flex items-center gap-1">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </span>
                    </button>
                </form>

                @if($searched)
                    <div class="p-4 bg-amber-50/70 border border-amber-200/80 rounded-2xl text-center space-y-1.5">
                        <h3 class="text-xs font-bold text-slate-900">Nomor Belum Terdaftar</h3>
                        <p class="text-[11px] text-stone-600 leading-relaxed">
                            Nomor <strong class="text-slate-900">{{ $phone }}</strong> belum memiliki riwayat member. Belanja di kafe dan scan QR di struk untuk langsung menjadi member!
                        </p>
                    </div>
                @endif

                <div class="pt-4 border-t border-stone-100 flex justify-between items-center text-xs text-stone-500">
                    <span>1 Poin = Rp 1.000</span>
                    <span>Tanpa Password</span>
                    <span>Klaim di Struk</span>
                </div>

                <div class="text-center pt-1">
                    <a href="{{ route('customer.landing') }}" class="text-xs font-bold text-stone-500 hover:text-slate-800 transition">
                        Kembali ke Beranda
                    </a>
                </div>

            </div>
        @endif

    </div>

    {{-- Clean Subtle Footer (shrink-0 at bottom) --}}
    <footer class="w-full text-center text-xs text-stone-400 font-medium pt-6 pb-2 relative z-10 shrink-0">
        &copy; {{ date('Y') }} {{ $setting->shop_name ?? 'Noli Coffe & Space' }}. Crafted with care.
    </footer>

</div>
