<div class="min-h-screen bg-[#faf8f5] text-slate-800 font-sans selection:bg-[#0e382c] selection:text-white flex flex-col justify-center items-center py-10 sm:py-16 px-4 sm:px-6 relative overflow-x-hidden">

    {{-- Subtle Ambient Glow --}}
    <div class="absolute -top-24 -left-24 w-80 h-80 bg-emerald-200/25 rounded-full blur-3xl pointer-events-none -z-0"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-amber-200/20 rounded-full blur-3xl pointer-events-none -z-0"></div>

    {{-- Main Container (Perfect Vertical Centering via my-auto) --}}
    <div class="w-full max-w-md my-auto relative z-10">

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/90 shadow-xl shadow-stone-900/5">

            @if($isClaimed && $successData)
                @php
                    $isNewMember = !empty($successData['is_new_customer']) 
                        || ($successData['balance_now'] == $successData['points_earned'])
                        || ($successData['customer']->pointHistories()->count() <= 1);
                    $displayName = !empty($successData['customer']->name) && $successData['customer']->name !== 'Pelanggan' 
                        ? $successData['customer']->name 
                        : null;
                @endphp

                @if($isNewMember)
                    {{-- 1A. WELCOME NEW MEMBER CELEBRATION --}}
                    <div class="text-center space-y-5">
                        <div class="w-16 h-16 rounded-3xl bg-amber-50 text-amber-700 border border-amber-200/80 flex items-center justify-center mx-auto text-3xl shadow-xs">
                            🎉
                        </div>

                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#0e382c] block mb-1">
                                Selamat Bergabung
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-heading">
                                Resmi Menjadi <span class="font-editorial italic font-normal text-[#0e382c]">Member Baru!</span>
                            </h2>
                            <p class="text-xs text-stone-500 mt-2 leading-relaxed max-w-sm mx-auto">
                                Selamat {{ $displayName ? $displayName : 'kamu' }}! Akun member Noli Space kamu sudah aktif dan langsung menerima <strong>poin perdana</strong>.
                            </p>
                        </div>

                        {{-- Points Box --}}
                        <div class="bg-[#faf8f5] p-5 rounded-2xl border border-stone-200/90 text-center">
                            <span class="text-[11px] font-semibold text-stone-500 uppercase tracking-wider block">Bonus Poin Perdana:</span>
                            <div class="text-4xl font-black text-[#0e382c] tracking-tight font-heading mt-0.5">
                                +{{ $successData['points_earned'] }} <span class="text-base font-bold text-emerald-700">Poin</span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-dashed border-stone-200 flex justify-between items-center text-xs">
                                <span class="text-stone-500">Total Saldo Kamu:</span>
                                <span class="font-black text-slate-900">
                                    ⭐ {{ $successData['balance_now'] }} Poin
                                    <span class="text-emerald-700 font-bold ml-1">(Rp {{ number_format(\App\Services\LoyaltyService::calculatePointValue($successData['balance_now'], $setting), 0, ',', '.') }})</span>
                                </span>
                            </div>
                        </div>

                        {{-- Welcome Tip Box --}}
                        <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200/60 text-left text-xs text-emerald-950 flex items-start gap-2.5">
                            <i class="fas fa-sparkles text-amber-600 text-sm mt-0.5 shrink-0"></i>
                            <span class="leading-relaxed">
                                Cukup sebutkan nomor WhatsApp <strong>{{ $successData['customer']->phone }}</strong> ke kasir saat pesan berikutnya untuk langsung dapat potongan belanja!
                            </span>
                        </div>

                        <div class="pt-2 flex flex-col gap-2">
                            <a href="{{ route('customer.member') }}?phone={{ $successData['customer']->phone }}" 
                               class="w-full py-3.5 px-4 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-[0.98] text-white font-black text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-md shadow-[#0e382c]/20">
                                <i class="fas fa-id-card text-emerald-300"></i>
                                <span>Buka Kartu Member Saya</span>
                            </a>
                            <a href="{{ route('customer.landing') }}" 
                               class="py-2.5 text-xs font-bold text-stone-500 hover:text-slate-800 transition">
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>

                @else
                    {{-- 1B. EXISTING MEMBER POINT TOP-UP CELEBRATION --}}
                    <div class="text-center space-y-5">
                        <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-[#0e382c] border border-emerald-200/80 flex items-center justify-center mx-auto text-2xl shadow-xs">
                            <i class="fas fa-circle-check text-3xl text-emerald-600"></i>
                        </div>

                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-[#0e382c] block mb-1">Klaim Berhasil</span>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-heading">
                                Poin Resmi <span class="font-editorial italic font-normal text-[#0e382c]">Ditambahkan!</span>
                            </h2>
                            <p class="text-xs text-stone-500 mt-1.5">
                                Halo kembali, <strong class="text-slate-900">{{ $displayName ? $displayName : 'Sobat Noli' }}</strong>! Poin belanja dari struk ini telah ditambahkan ke akun member kamu.
                            </p>
                        </div>

                        {{-- Points Box --}}
                        <div class="bg-[#faf8f5] p-5 rounded-2xl border border-stone-200/90">
                            <span class="text-[11px] font-semibold text-stone-500 uppercase tracking-wider block">Poin Baru:</span>
                            <div class="text-4xl font-black text-[#0e382c] tracking-tight font-heading mt-0.5">
                                +{{ $successData['points_earned'] }} <span class="text-base font-bold text-emerald-700">Poin</span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-dashed border-stone-200 flex justify-between items-center text-xs">
                                <span class="text-stone-500">Total Saldo:</span>
                                <span class="font-black text-slate-900">
                                    ⭐ {{ $successData['balance_now'] }} Poin
                                    <span class="text-emerald-700 font-bold ml-1">(Rp {{ number_format(\App\Services\LoyaltyService::calculatePointValue($successData['balance_now'], $setting), 0, ',', '.') }})</span>
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 flex flex-col gap-2">
                            <a href="{{ route('customer.member') }}?phone={{ $successData['customer']->phone }}" 
                               class="w-full py-3.5 px-4 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-[0.98] text-white font-black text-xs transition flex items-center justify-center gap-2 shadow-md shadow-[#0e382c]/20">
                                <i class="fas fa-id-card text-emerald-300"></i>
                                <span>Buka Kartu Member Saya</span>
                            </a>
                            <a href="{{ route('customer.landing') }}" 
                               class="py-2.5 text-xs font-bold text-stone-500 hover:text-slate-800 transition">
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                @endif

            @elseif($errorMessage)
                {{-- 2. ERROR / ALREADY CLAIMED / EXPIRED STATE --}}
                @php
                    $isAlreadyClaimed = str_contains($errorMessage, 'sudah pernah diklaim');
                    $isExpired = str_contains($errorMessage, 'kedaluwarsa');
                @endphp

                <div class="text-center space-y-5">
                    <div class="w-16 h-16 rounded-3xl bg-amber-50 text-amber-700 border border-amber-200/80 flex items-center justify-center mx-auto text-2xl shadow-xs">
                        @if($isAlreadyClaimed)
                            <i class="fas fa-clock-rotate-left text-2xl text-amber-600"></i>
                        @elseif($isExpired)
                            <i class="fas fa-hourglass-end text-2xl text-amber-600"></i>
                        @else
                            <i class="fas fa-receipt text-2xl text-amber-600"></i>
                        @endif
                    </div>

                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-stone-500 block mb-1">
                            Status Struk Belanja
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-heading">
                            @if($isAlreadyClaimed)
                                Struk Sudah <span class="font-editorial italic font-normal text-stone-600">Diklaim.</span>
                            @elseif($isExpired)
                                Waktu Klaim <span class="font-editorial italic font-normal text-stone-600">Berakhir.</span>
                            @else
                                Informasi <span class="font-editorial italic font-normal text-stone-600">Struk.</span>
                            @endif
                        </h2>
                    </div>

                    {{-- Structured Message Box --}}
                    <div class="bg-[#faf8f5] p-5 rounded-2xl border border-stone-200/90 text-center space-y-2.5">
                        <p class="text-xs sm:text-sm text-stone-600 leading-relaxed font-medium">
                            {{ $errorMessage }}
                        </p>
                        @if($transaction)
                            <div class="pt-2.5 border-t border-dashed border-stone-200 flex justify-between items-center text-xs">
                                <span class="text-stone-400 font-medium">Nomor Struk:</span>
                                <span class="font-mono font-bold text-slate-800 bg-white px-2 py-0.5 rounded-lg border border-stone-200/70">
                                    {{ $transaction->invoice_number }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Friendly Hint --}}
                    <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/60 text-xs text-amber-950 flex items-start gap-2.5 text-left">
                        <i class="fas fa-circle-info text-amber-600 text-sm mt-0.5 shrink-0"></i>
                        <span class="leading-relaxed">
                            Setiap struk belanja hanya dapat diklaim <strong>1 kali</strong>. Silakan cek saldo untuk melihat total poin member kamu.
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-2 flex flex-col gap-2">
                        <a href="{{ route('customer.member') }}" 
                           class="w-full py-3.5 px-4 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-[0.98] text-white font-black text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-md shadow-[#0e382c]/20">
                            <i class="fas fa-id-card text-emerald-300"></i>
                            <span>Buka Kartu Member Saya</span>
                        </a>
                        <a href="{{ route('customer.landing') }}" 
                           class="py-2.5 text-xs font-bold text-stone-500 hover:text-slate-800 transition">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>

            @elseif($transaction)
                {{-- 3. READY TO CLAIM (THE MAIN FLOW FROM RECEIPT QR SCAN) --}}
                
                {{-- Elegant Editorial Typography Header --}}
                <div class="mb-5">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#0e382c] block mb-1">
                        Klaim Poin Struk
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-heading">
                        Kumpulkan <span class="font-editorial italic font-normal text-[#0e382c]">Reward-mu.</span>
                    </h2>
                    <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                        Masukkan nomor WhatsApp untuk menyimpan poin dari struk belanja ini langsung ke akun member kamu.
                    </p>
                </div>

                {{-- Receipt Info Pass --}}
                <div class="bg-stone-50 rounded-2xl p-4 border border-stone-200/80 mb-5">
                    <div class="flex justify-between items-center text-xs pb-2 border-b border-stone-200/60">
                        <span class="text-stone-500">Nomor Struk:</span>
                        <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded-lg border border-stone-200/60">{{ $transaction->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs py-2 border-b border-stone-200/60">
                        <span class="text-stone-500">Total Pembelian:</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 text-xs">
                        <span class="font-semibold text-emerald-800 flex items-center gap-1.5">
                            <i class="fas fa-gift text-emerald-600"></i>
                            <span>Poin Diperoleh:</span>
                        </span>
                        <span class="text-base font-black text-emerald-700 font-heading">
                            +{{ $pointsToClaim }} Poin
                        </span>
                    </div>
                </div>

                {{-- Form Inputs --}}
                <form wire:submit.prevent="submitClaim" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5 flex items-center justify-between">
                            <span>Nomor WhatsApp Aktif</span>
                            <span class="text-emerald-700 text-[10px] font-semibold">Wajib Diisi</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600">
                                <i class="fab fa-whatsapp text-lg"></i>
                            </div>
                            <input type="tel" wire:model="phone" placeholder="Contoh: 081234567890" autofocus
                                   class="w-full pl-11 pr-4 py-3 text-base sm:text-sm font-semibold rounded-2xl border border-stone-200/90 bg-white text-slate-900 placeholder:text-stone-400 focus:outline-none focus:border-[#0e382c] focus:ring-4 focus:ring-[#0e382c]/10 transition-all shadow-2xs">
                        </div>
                        @error('phone') 
                            <span class="text-rose-500 text-[11px] font-medium block mt-1.5">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5 flex items-center justify-between">
                            <span>Nama Panggilan</span>
                            <span class="text-stone-400 text-[10px] font-normal">Opsional</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <input type="text" wire:model="name" placeholder="Nama Panggilan Kamu"
                                   class="w-full pl-10 pr-4 py-3 text-base sm:text-sm font-semibold rounded-2xl border border-stone-200/90 bg-white text-slate-900 placeholder:text-stone-400 focus:outline-none focus:border-[#0e382c] focus:ring-4 focus:ring-[#0e382c]/10 transition-all shadow-2xs">
                        </div>
                        @error('name') 
                            <span class="text-rose-500 text-[11px] font-medium block mt-1.5">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div class="flex items-start gap-2 text-[11px] text-stone-500 leading-relaxed pt-1">
                        <i class="fas fa-lock text-stone-400 text-xs mt-0.5 shrink-0"></i>
                        <span>Nomor WhatsApp ini otomatis menjadi ID akun member kamu tanpa perlu bikin password.</span>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-6 bg-[#0e382c] hover:bg-[#134e3f] active:scale-[0.98] text-white rounded-2xl font-black text-xs sm:text-sm shadow-md shadow-[#0e382c]/20 transition flex items-center justify-center gap-2 cursor-pointer mt-2">
                        <span wire:loading.remove>Klaim +{{ $pointsToClaim }} Poin Sekarang</span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Memproses...</span>
                        </span>
                    </button>
                </form>

            @else
                {{-- 4. NO TOKEN / DIRECT VISIT TO /claim --}}
                <div class="text-center space-y-5">
                    <div class="w-16 h-16 rounded-3xl bg-stone-100 text-stone-600 flex items-center justify-center mx-auto text-2xl">
                        <i class="fas fa-qrcode text-3xl text-[#0e382c]"></i>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-[#0e382c] block mb-1">Klaim Poin Struk</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight font-heading">
                            Scan QR <span class="font-editorial italic font-normal text-[#0e382c]">Struk Belanja.</span>
                        </h2>
                        <p class="text-xs text-stone-500 mt-2 leading-relaxed">
                            Arahkan kamera smartphone ke <strong>QR Code di bagian bawah struk belanja kasir</strong> untuk klaim poin kamu secara otomatis.
                        </p>
                    </div>

                    <div class="pt-2 border-t border-stone-100">
                        <a href="{{ route('customer.member') }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0e382c] hover:text-[#134e3f] transition">
                            <span>Sudah punya member? Cek saldo poin di sini</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endif

        </div>

    </div>

    {{-- Clean Subtle Footer (shrink-0 at bottom) --}}
    <footer class="w-full text-center text-xs text-stone-400 font-medium pt-6 pb-2 relative z-10 shrink-0">
        &copy; {{ date('Y') }} {{ $setting->shop_name ?? 'Noli Coffe & Space' }}. Crafted with care.
    </footer>

</div>
