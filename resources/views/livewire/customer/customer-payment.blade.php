<div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between p-3.5 sm:p-6 lg:p-8 pt-safe pb-8"
     wire:poll.3s="checkPaymentStatus">

    {{-- Top Header --}}
    <div class="max-w-sm sm:max-w-md mx-auto w-full flex items-center justify-between pb-2.5 border-b border-slate-200/80 shrink-0">
        <a href="{{ route('customer.order') }}" 
           class="w-9 h-9 rounded-2xl bg-white border border-slate-200 text-slate-700 hover:text-[#0e382c] hover:border-emerald-300 active:scale-95 flex items-center justify-center text-xs shadow-2xs transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span class="text-xs font-black text-slate-900 uppercase tracking-widest font-heading">
            Pembayaran QRIS
        </span>
        <div class="w-9"></div>
    </div>

    {{-- Main Payment Container (Compact & 100% Focused on Payment) --}}
    <div class="max-w-sm sm:max-w-md mx-auto w-full my-auto py-2 space-y-3">

        {{-- Main Payment Card --}}
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-md border border-slate-200/80 text-center relative overflow-hidden">
            
            {{-- Payment Expiry Countdown Timer --}}
            <div x-data="paymentTimer({{ $expiresAtTimestamp }})" x-init="start()" class="mb-3">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-bold shadow-2xs">
                    <i class="far fa-clock text-amber-600 animate-pulse text-[11px]"></i>
                    <span>Selesaikan dalam <strong class="font-mono text-amber-950 font-black tracking-wider" x-text="formattedTime">15:00</strong></span>
                </div>
            </div>

            {{-- Invoice & Table Info --}}
            <div class="text-center mb-3">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">No. Pesanan</span>
                <span class="text-sm font-black text-slate-900 font-heading tracking-wide">{{ $transaction->short_order_number }}</span>
                <div class="mt-1 flex items-center justify-center gap-1.5 text-xs text-slate-600">
                    @if($transaction->order_type === 'dine_in')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-[#0e382c] font-black text-[11px] border border-emerald-200/70">
                            <i class="{{ !empty($transaction->table_number) ? 'fas fa-chair' : 'fas fa-mug-hot' }} text-[10px]"></i> 
                            {{ !empty($transaction->table_number) ? 'Meja ' . $transaction->table_number : 'Makan di Tempat' }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 font-black text-[11px] border border-blue-200">
                            <i class="fas fa-bag-shopping text-[10px]"></i> Takeaway
                        </span>
                    @endif
                    <span class="text-slate-300">•</span>
                    <span class="font-bold text-slate-800 truncate max-w-[150px]">{{ $transaction->customer_name }}</span>
                </div>
            </div>

            {{-- Total Amount Display --}}
            <div class="bg-slate-50 py-2.5 px-4 rounded-2xl border border-slate-200/60 mb-2.5">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mb-0.5">Total yang Harus Dibayar</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 font-heading tracking-tight">
                    Rp {{ number_format($transaction->total, 0, ',', '.') }}
                </span>
            </div>

            {{-- Collapsible Order Breakdown with Add-ons --}}
            <div x-data="{ openDetail: false }" class="mb-3 text-left">
                <button type="button" @click="openDetail = !openDetail" 
                        class="w-full py-2 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 text-xs font-bold flex items-center justify-between transition cursor-pointer">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-receipt text-emerald-700 text-[11px]"></i>
                        <span>Lihat Rincian Pesanan ({{ $transaction->details->count() }} menu)</span>
                    </span>
                    <i class="fas fa-chevron-down text-[10px] text-slate-500 transition-transform duration-200" :class="openDetail ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openDetail" x-collapse class="mt-2 p-3 bg-slate-50/80 rounded-2xl border border-slate-200/70 space-y-2 text-xs">
                    @foreach($transaction->details as $d)
                        <div class="flex justify-between items-start pb-2 border-b border-slate-200/60 last:border-0 last:pb-0">
                            <div class="flex-1 pr-2">
                                <span class="font-bold text-slate-900">{{ $d->quantity }}x {{ $d->product?->name ?? 'Menu' }}</span>
                                @if(!empty($d->addons))
                                    <div class="flex flex-wrap gap-1 mt-0.5">
                                        @foreach($d->addons as $addon)
                                            @php
                                                $addonName = is_array($addon) ? ($addon['name'] ?? '') : ($addon->name ?? '');
                                                $addonPrice = is_array($addon) ? ($addon['price'] ?? 0) : ($addon->price ?? 0);
                                            @endphp
                                            <span class="inline-flex items-center text-[10px] font-extrabold px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-900 border border-emerald-300">
                                                + {{ $addonName }} @if($addonPrice > 0) (+Rp {{ number_format($addonPrice, 0, ',', '.') }}) @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($d->notes))
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $d->notes }}</div>
                                @endif
                            </div>
                            <span class="font-bold text-slate-800 shrink-0 font-mono">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- The Authentic QRIS Frame --}}
            <div class="bg-white rounded-2xl p-3.5 sm:p-4 border-2 border-slate-200 shadow-xs text-center relative overflow-hidden max-w-[260px] sm:max-w-[280px] mx-auto">
                
                {{-- Left Red Chevron Accent --}}
                <div class="absolute left-0 top-1/4 -translate-y-2 w-3 h-16 bg-[#e5252d] clip-chevron-left"></div>

                {{-- Bottom Right Red Chevron Accent --}}
                <div class="absolute right-0 bottom-0 w-8 h-8 bg-[#e5252d] clip-chevron-corner"></div>

                {{-- QRIS Official Header Logo --}}
                <div class="flex items-center justify-center pt-0.5 pb-1.5">
                    <img src="{{ asset('images/qris-logo.png') }}" 
                         alt="QRIS - QR Code Standar Pembayaran Nasional" 
                         class="h-6 sm:h-7 w-auto object-contain">
                </div>

                {{-- Direct Midtrans QR Code Image --}}
                <div class="relative inline-block my-0.5 bg-white p-1">
                    @if($qrisUrl)
                        <img id="qrisImageElement" 
                             src="{{ $qrisUrl }}" 
                             alt="Kode QRIS" 
                             class="w-44 h-44 sm:w-52 sm:h-52 mx-auto object-contain">
                    @else
                        <div class="w-44 h-44 sm:w-52 sm:h-52 flex flex-col items-center justify-center bg-slate-50 rounded-lg text-slate-400">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2 text-[#0e382c]"></i>
                            <span class="text-xs font-semibold">Memuat Kode QRIS...</span>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Unduh / Simpan Gambar QR Button --}}
            <div class="mt-2.5">
                <button type="button" 
                        onclick="downloadQrisImage()" 
                        class="px-3.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#0e382c] text-xs font-black border border-emerald-200/80 shadow-2xs inline-flex items-center gap-1.5 transition active:scale-95 cursor-pointer">
                    <i class="fas fa-download text-[10px]"></i>
                    <span>Unduh Gambar QR</span>
                </button>
            </div>

            {{-- Minimalist Payment Note --}}
            <div class="mt-2 text-[10px] text-slate-400 font-medium max-w-[260px] mx-auto leading-tight">
                Scan atau upload gambar QR dengan <span class="text-slate-600 font-bold">BCA, GoPay, OVO, DANA, ShopeePay</span> & semua M-Banking.
            </div>

        </div>

        {{-- Primary Action: Saya Sudah Membayar --}}
        <div>
            <button type="button" 
                    wire:click="manualCheckPayment"
                    wire:loading.attr="disabled"
                    wire:target="manualCheckPayment"
                    class="w-full py-3.5 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-black text-xs shadow-md shadow-[#0e382c]/20 flex items-center justify-center gap-2 transition cursor-pointer">
                <i class="fas fa-check-circle text-xs" wire:loading.remove wire:target="manualCheckPayment"></i>
                <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="manualCheckPayment"></i>
                <span wire:loading.remove wire:target="manualCheckPayment">Saya Sudah Membayar</span>
                <span wire:loading wire:target="manualCheckPayment">Memverifikasi Pembayaran...</span>
            </button>
        </div>

        {{-- Cancel Order Button (Clean Secondary Action) --}}
        <div>
            <button type="button" 
                    onclick="confirmCancelOrder()"
                    class="w-full py-2.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50/80 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                <i class="fas fa-times-circle text-xs"></i>
                <span>Batalkan Pesanan</span>
            </button>
        </div>

    </div>

    {{-- Footer Security Info --}}
    <div class="max-w-sm sm:max-w-md mx-auto w-full text-center text-[10.5px] text-slate-400 pb-safe shrink-0">
        Pembayaran terverifikasi otomatis. Pesanan langsung masuk ke antrean dapur.
    </div>

    {{-- CSS Helpers for Red Geometric Chevron Accents on QR Frame --}}
    <style>
        .clip-chevron-left {
            clip-path: polygon(0% 0%, 100% 15%, 100% 85%, 0% 100%, 0% 50%);
        }
        .clip-chevron-corner {
            clip-path: polygon(100% 0%, 100% 100%, 0% 100%, 30% 70%);
        }
    </style>

    <script>
        function downloadQrisImage() {
            const img = document.getElementById('qrisImageElement');
            if (!img) return;

            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const size = 600;
                canvas.width = size;
                canvas.height = 700;

                // Background
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Header Shop Name
                ctx.fillStyle = '#0e382c';
                ctx.font = 'bold 24px "Plus Jakarta Sans", sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('{{ $setting->shop_name ?? "POS Cafe" }}', size / 2, 45);

                // Subtitle
                ctx.fillStyle = '#64748b';
                ctx.font = '16px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('{{ $transaction->invoice_number }} • {{ $transaction->order_type === "dine_in" ? "Meja " . ($transaction->table_number ?? "-") : "Takeaway" }}', size / 2, 75);

                // Total
                ctx.fillStyle = '#0f172a';
                ctx.font = 'bold 30px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('Rp {{ number_format($transaction->total, 0, ",", ".") }}', size / 2, 115);

                // Draw QR Code
                const qrImg = new Image();
                qrImg.crossOrigin = 'anonymous';
                qrImg.onload = function() {
                    ctx.drawImage(qrImg, 75, 140, 450, 450);

                    // Footer note
                    ctx.fillStyle = '#94a3b8';
                    ctx.font = '14px "Plus Jakarta Sans", sans-serif';
                    ctx.fillText('Scan dengan BCA, GoPay, OVO, DANA, ShopeePay & M-Banking', size / 2, 630);

                    // Download
                    const link = document.createElement('a');
                    link.download = 'QRIS_{{ $transaction->invoice_number }}.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                };

                qrImg.onerror = function() {
                    // Fallback
                    const link = document.createElement('a');
                    link.download = 'QRIS_{{ $transaction->invoice_number }}.png';
                    link.href = img.src;
                    link.target = '_blank';
                    link.click();
                };

                qrImg.src = img.src;
            } catch(e) {
                const link = document.createElement('a');
                link.download = 'QRIS_{{ $transaction->invoice_number }}.png';
                link.href = img.src;
                link.target = '_blank';
                link.click();
            }
        }

        function paymentTimer(expiresTimestamp) {
            return {
                expiry: expiresTimestamp,
                formattedTime: '15:00',
                timer: null,
                start() {
                    this.calcTime();
                    this.timer = setInterval(() => {
                        this.calcTime();
                    }, 1000);
                },
                calcTime() {
                    let now = Math.floor(Date.now() / 1000);
                    let diff = this.expiry - now;
                    if (diff <= 0) {
                        this.formattedTime = '00:00 (Habis)';
                        clearInterval(this.timer);
                        if (window.Livewire && typeof @this !== 'undefined') {
                            @this.call('expireOrder');
                        }
                        return;
                    }
                    let m = Math.floor(diff / 60);
                    let s = diff % 60;
                    this.formattedTime = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                }
            };
        }

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

        window.addEventListener('remove-token-localstorage', event => {
            let removeToken = event.detail.token;
            let tokens = JSON.parse(localStorage.getItem('self_order_tokens') || '[]');
            tokens = tokens.filter(t => t !== removeToken);
            localStorage.setItem('self_order_tokens', JSON.stringify(tokens));
        });

        function confirmCancelOrder() {
            if (typeof Swal === 'undefined') {
                if (confirm('Yakin ingin membatalkan pesanan ini dan kembali ke menu?')) {
                    @this.call('cancelOrder');
                }
                return;
            }

            Swal.fire({
                title: 'Batalkan Pesanan Ini?',
                text: 'Pesanan Anda belum dibayar dan akan dihapus dari antrean.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#0e382c',
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Batalkan',
                cancelButtonText: '<i class="fas fa-arrow-left mr-1"></i> Lanjut Bayar',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100',
                    confirmButton: 'rounded-2xl px-5 py-3 font-bold text-xs shadow-xs',
                    cancelButton: 'rounded-2xl px-5 py-3 font-bold text-xs shadow-xs'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('cancelOrder');
                }
            });
        }
    </script>
</div>
