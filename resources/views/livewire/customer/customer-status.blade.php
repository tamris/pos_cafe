@php
    $isFinished = in_array($transaction->status, ['completed', 'cancelled']);
    $isPaid = $transaction->payment_status === 'paid';
    $status = $transaction->status; // 'pending', 'processing', 'ready', 'completed', 'cancelled'
@endphp

<div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between p-4 sm:p-6 lg:p-8 pt-6 pb-12" 
     @if(!$isFinished) wire:poll.10s @endif>

    {{-- Clean Top Navigation Bar --}}
    <div class="max-w-lg mx-auto w-full flex items-center justify-between pb-3.5 border-b border-slate-200/80">
        <a href="{{ route('customer.order') }}" class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-[#0e382c] hover:border-slate-300 shadow-2xs flex items-center justify-center transition">
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
        <span class="font-extrabold text-xs text-slate-900 font-heading tracking-wide">{{ $setting->shop_name ?? 'POS Cafe' }}</span>
        <div class="w-8"></div>
    </div>

    {{-- Main Status Content (Centered for Mobile & Tablet) --}}
    <div class="max-w-lg mx-auto w-full my-auto py-4 space-y-4">

        @php
            $isPaid = $transaction->payment_status === 'paid';
            $status = $transaction->status; // 'pending', 'processing', 'ready', 'completed', 'cancelled'
        @endphp

        {{-- HERO STATUS BANNER --}}
        @if($status === 'cancelled')
            {{-- Status: Cancelled --}}
            <div class="bg-rose-50 border border-rose-200 rounded-3xl p-6 text-center text-rose-950 shadow-soft">
                <div class="w-16 h-16 rounded-2xl bg-rose-600 text-white text-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-rose-600/30">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="text-lg font-black font-heading text-rose-950">Pesanan Dibatalkan</h2>
                <p class="text-xs text-rose-800 mt-1 max-w-xs mx-auto leading-relaxed">
                    {{ $transaction->cancelled_reason ?: 'Pesanan ini telah dibatalkan.' }}
                </p>
            </div>

        @elseif(!$isPaid)
            {{-- Status: Unpaid --}}
            <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 text-center text-amber-950 shadow-soft">
                <div class="w-16 h-16 rounded-2xl bg-amber-500 text-white text-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-amber-500/30 animate-pulse">
                    <i class="fas fa-wallet"></i>
                </div>
                <h2 class="text-lg font-black font-heading text-amber-950">Menunggu Pembayaran</h2>
                <p class="text-xs text-amber-800 mt-1 max-w-xs mx-auto">
                    Pesanan Anda belum dibayar. Selesaikan pembayaran agar pesanan segera masuk ke dapur.
                </p>
                <div class="mt-4">
                    <a href="{{ route('customer.payment', ['token' => $transaction->order_token]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md">
                        <span>Lanjut ke Pembayaran QRIS</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        @elseif($status === 'ready')
            {{-- Status: Ready --}}
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-400 rounded-3xl p-6 text-center text-emerald-950 shadow-elevated relative overflow-hidden">
                <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white text-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-600/30 animate-bounce">
                    <i class="fas fa-bell"></i>
                </div>
                <h2 class="text-xl font-black font-heading text-emerald-950">Pesanan Siap! 🎉</h2>
                <p class="text-xs text-emerald-800 mt-1.5 max-w-xs mx-auto font-medium leading-relaxed">
                    @if($transaction->order_type === 'dine_in')
                        Pesanan Anda sedang diantarkan ke <strong>Meja {{ $transaction->table_number ?? '-' }}</strong> atau dapat diambil di pick-up counter.
                    @else
                        Pesanan Takeaway Anda sudah siap. Silakan ambil di pick-up counter kasir.
                    @endif
                </p>
            </div>

        @elseif($status === 'completed')
            {{-- Status: Completed --}}
            <div class="bg-stone-900 text-white rounded-3xl p-6 text-center shadow-card border border-stone-800">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500 text-white text-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-500/30">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="text-lg font-black font-heading">Pesanan Telah Selesai</h2>
                <p class="text-xs text-stone-400 mt-1 max-w-xs mx-auto">
                    Terima kasih telah berkunjung di {{ $setting->shop_name ?? 'POS Cafe' }}. Selamat menikmati hidangan Anda!
                </p>
            </div>

        @else
            {{-- Status: Processing / In Kitchen --}}
            <div class="bg-emerald-50/80 border border-emerald-200/90 rounded-3xl p-6 text-center text-emerald-950 shadow-soft">
                <div class="w-16 h-16 rounded-2xl bg-[#0e382c] text-white text-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-[#0e382c]/25 animate-pulse">
                    <i class="fas fa-utensils"></i>
                </div>
                <h2 class="text-lg font-black font-heading text-emerald-950">Pesanan Sedang Disiapkan</h2>
                <p class="text-xs text-emerald-800 mt-1 max-w-xs mx-auto leading-relaxed">
                    Pembayaran sukses! Barista / Dapur sedang meracik pesanan Anda dengan cermat. Mohon ditunggu ya!
                </p>
            </div>
        @endif

        {{-- Visual Stepper Progress --}}
        @if($status !== 'cancelled')
            <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-card border border-stone-200/80">
                <div class="grid grid-cols-4 gap-1 text-center relative">
                    
                    {{-- Step 1: Payment --}}
                    <div class="flex flex-col items-center">
                        <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-xs font-bold mb-1.5 {{ $isPaid ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-rose-600 text-white animate-pulse' }}">
                            <i class="fas {{ $isPaid ? 'fa-check' : 'fa-wallet' }}"></i>
                        </div>
                        <span class="text-[10px] font-bold text-stone-800">Lunas</span>
                    </div>

                    {{-- Step 2: In Kitchen --}}
                    <div class="flex flex-col items-center">
                        <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-xs font-bold mb-1.5 {{ in_array($status, ['processing', 'ready', 'completed']) ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-stone-200 text-stone-400' }}">
                            <i class="fas {{ in_array($status, ['processing', 'ready', 'completed']) ? 'fa-check' : 'fa-fire' }}"></i>
                        </div>
                        <span class="text-[10px] font-bold {{ in_array($status, ['processing', 'ready', 'completed']) ? 'text-stone-800' : 'text-stone-400' }}">Disiapkan</span>
                    </div>

                    {{-- Step 3: Ready --}}
                    <div class="flex flex-col items-center">
                        <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-xs font-bold mb-1.5 {{ in_array($status, ['ready', 'completed']) ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-stone-200 text-stone-400' }}">
                            <i class="fas {{ in_array($status, ['ready', 'completed']) ? 'fa-check' : 'fa-bell' }}"></i>
                        </div>
                        <span class="text-[10px] font-bold {{ in_array($status, ['ready', 'completed']) ? 'text-stone-800' : 'text-stone-400' }}">Siap</span>
                    </div>

                    {{-- Step 4: Completed --}}
                    <div class="flex flex-col items-center">
                        <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-xs font-bold mb-1.5 {{ $status === 'completed' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-stone-200 text-stone-400' }}">
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="text-[10px] font-bold {{ $status === 'completed' ? 'text-stone-800' : 'text-stone-400' }}">Selesai</span>
                    </div>

                </div>
            </div>
        @endif

        {{-- Free Wi-Fi Info Card --}}
        @if(!empty($setting?->wifi_ssid))
            <div class="bg-gradient-to-r from-stone-900 to-stone-800 text-white rounded-3xl p-4 sm:p-5 shadow-card flex items-center justify-between border border-stone-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-amber-300 text-lg">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <div>
                        <span class="text-[10px] text-amber-300 block uppercase font-black tracking-wider">Free Wi-Fi Cafe</span>
                        <div class="text-xs font-bold">
                            SSID: <span class="font-mono text-white">{{ $setting->wifi_ssid }}</span>
                        </div>
                        @if(!empty($setting->wifi_password))
                            <div class="text-[11px] text-stone-300 mt-0.5">
                                Password: <span class="font-mono bg-white/15 px-1.5 py-0.2 rounded font-bold">{{ $setting->wifi_password }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <i class="fas fa-signal text-xl text-stone-500"></i>
                </div>
            </div>
        @endif

        {{-- Order Breakdown Card --}}
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-card border border-stone-200/80 space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <div>
                    <span class="text-[10px] text-stone-400 font-semibold uppercase tracking-wider block">Faktur</span>
                    <span class="text-xs font-black text-stone-900 font-mono">{{ $transaction->invoice_number }}</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-stone-400 font-semibold uppercase tracking-wider block">Waktu Pesan</span>
                    <span class="text-xs font-bold text-stone-700">{{ $transaction->created_at->format('H:i, d M Y') }}</span>
                </div>
            </div>

            {{-- Identity Pill --}}
            <div class="flex items-center justify-between text-xs py-1 text-stone-600">
                <span>Pemesan: <strong class="text-stone-900">{{ $transaction->customer_name }}</strong></span>
                <span>
                    @if($transaction->order_type === 'dine_in')
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 font-bold text-[11px] border border-amber-200">
                            Meja {{ $transaction->table_number ?? '-' }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 font-bold text-[11px] border border-blue-200">
                            Takeaway
                        </span>
                    @endif
                </span>
            </div>

            {{-- Items --}}
            <div class="border-t border-stone-100 pt-3 space-y-2.5 text-xs">
                @foreach($transaction->details as $d)
                    <div class="flex justify-between items-start">
                        <div class="flex-1 pr-2">
                            <div class="font-bold text-stone-900">
                                {{ $d->quantity }}x {{ $d->product?->name ?? 'Menu' }}
                            </div>
                            @if(!empty($d->notes))
                                <div class="text-[11px] text-stone-500 font-medium mt-0.5">
                                    {{ $d->notes }}
                                </div>
                            @endif
                        </div>
                        <span class="font-extrabold text-stone-900 font-heading">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="border-t border-stone-100 pt-3 space-y-1 text-xs text-stone-600">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($transaction->tax > 0)
                    <div class="flex justify-between">
                        <span>Pajak</span>
                        <span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm font-extrabold text-stone-900 pt-2 border-t border-stone-100">
                    <span>Total Tagihan ({{ strtoupper($transaction->payment_method ?: 'Online') }})</span>
                    <span class="text-amber-700 font-heading">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Bottom Action Button --}}
        <div class="pt-1">
            <a href="{{ route('customer.order') }}" 
               class="w-full py-3.5 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-black text-xs shadow-md shadow-[#0e382c]/20 transition flex items-center justify-center gap-2">
                <i class="fas fa-mug-hot text-xs"></i>
                <span>Pesan Menu Lainnya</span>
            </a>
        </div>

    </div>

    <script>
        (function() {
            let token = '{{ $transaction->order_token }}';
            if (token) {
                let tokens = JSON.parse(localStorage.getItem('self_order_tokens') || '[]');
                if (!tokens.includes(token)) {
                    tokens.unshift(token);
                    localStorage.setItem('self_order_tokens', JSON.stringify(tokens.slice(0, 25)));
                }
            }
        })();
    </script>
</div>
