<div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between p-4 sm:p-6 lg:p-8 pt-8 sm:pt-6 pb-10"
     wire:poll.3s="checkPaymentStatus">

    {{-- Top Header --}}
    <div class="max-w-md mx-auto w-full flex items-center justify-between pb-3 border-b border-slate-200/80">
        <a href="{{ route('customer.order') }}" 
           class="w-9 h-9 rounded-2xl bg-white border border-slate-200 text-slate-700 hover:text-[#0e382c] hover:border-emerald-300 active:scale-95 flex items-center justify-center text-xs shadow-2xs transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span class="text-xs font-black text-slate-900 uppercase tracking-widest font-heading">
            Pembayaran QRIS
        </span>
        <div class="w-9"></div>
    </div>

    {{-- Main Payment Container --}}
    <div class="max-w-md mx-auto w-full my-auto py-3 space-y-3.5">

        {{-- Main Payment Card --}}
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-md border border-slate-200/80 text-center relative overflow-hidden">
            
            {{-- Payment Expiry Countdown Timer --}}
            <div x-data="paymentTimer({{ $expiresAtTimestamp }})" x-init="start()" class="mb-4">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-bold shadow-2xs">
                    <i class="far fa-clock text-amber-600 animate-pulse"></i>
                    <span>Selesaikan dalam <strong class="font-mono text-amber-950 font-black tracking-wider" x-text="formattedTime">15:00</strong></span>
                </div>
            </div>

            {{-- Invoice & Table Info --}}
            <div class="text-center mb-4">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Nomor Tagihan</span>
                <span class="text-xs font-black text-slate-900 font-mono tracking-wider">{{ $transaction->invoice_number }}</span>
                <div class="mt-1.5 flex items-center justify-center gap-1.5 text-xs text-slate-600">
                    @if($transaction->order_type === 'dine_in')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-[#0e382c] font-black text-[11px] border border-emerald-200/70">
                            <i class="fas fa-chair text-[10px]"></i> Meja {{ $transaction->table_number ?? '-' }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 font-black text-[11px] border border-blue-200">
                            <i class="fas fa-bag-shopping text-[10px]"></i> Takeaway
                        </span>
                    @endif
                    <span class="text-slate-300">•</span>
                    <span class="font-bold text-slate-800">{{ $transaction->customer_name }}</span>
                </div>
            </div>

            {{-- Total Amount Display --}}
            <div class="bg-slate-50 py-3.5 px-4 rounded-2xl border border-slate-200/60 mb-4">
                <span class="text-[11px] text-slate-500 font-medium block mb-0.5">Total yang Harus Dibayar</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 font-heading">
                    Rp {{ number_format($transaction->total, 0, ',', '.') }}
                </span>
            </div>

            {{-- The Authentic QRIS Frame (Kopken Style Frame inside Original Theme) --}}
            <div class="bg-white rounded-2xl p-4 sm:p-5 border-2 border-slate-200 shadow-xs text-center relative overflow-hidden max-w-[270px] sm:max-w-[290px] mx-auto">
                
                {{-- Left Red Chevron Accent --}}
                <div class="absolute left-0 top-1/4 -translate-y-2 w-3.5 h-20 bg-[#e5252d] clip-chevron-left"></div>

                {{-- Bottom Right Red Chevron Accent --}}
                <div class="absolute right-0 bottom-0 w-10 h-10 bg-[#e5252d] clip-chevron-corner"></div>

                {{-- QRIS Official Header Logo (Official National Standard) --}}
                <div class="flex items-center justify-center pt-1 pb-2">
                    <img src="{{ asset('images/qris-logo.png') }}" 
                         alt="QRIS - QR Code Standar Pembayaran Nasional" 
                         class="h-7 sm:h-8 w-auto object-contain">
                </div>

                {{-- Direct Midtrans QR Code Image --}}
                <div class="relative inline-block my-1 bg-white p-1">
                    @if($qrisUrl)
                        <img id="qrisImageElement" 
                             src="{{ $qrisUrl }}" 
                             alt="Kode QRIS" 
                             class="w-52 h-52 sm:w-56 sm:h-56 mx-auto object-contain">
                    @else
                        <div class="w-52 h-52 sm:w-56 sm:h-56 flex flex-col items-center justify-center bg-slate-50 rounded-lg text-slate-400">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2 text-[#0e382c]"></i>
                            <span class="text-xs font-semibold">Memuat Kode QRIS...</span>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Minimalist & Clean Payment Note --}}
            <div class="mt-3 text-[10.5px] text-slate-400 font-medium max-w-[260px] mx-auto leading-snug">
                Scan dengan <span class="text-slate-600 font-bold">BCA, GoPay, OVO, DANA, ShopeePay</span> & semua Mobile Banking.
            </div>

        </div>

        {{-- Accordion Order Items Breakdown (Original Style) --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-xs font-black text-slate-800 cursor-pointer">
                <span class="flex items-center gap-2">
                    <i class="fas fa-receipt text-slate-400 text-xs"></i>
                    <span>Rincian Pesanan ({{ $transaction->details->count() }} menu)</span>
                </span>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-slate-100 space-y-2.5 text-xs">
                @foreach($transaction->details as $d)
                    <div class="flex justify-between items-start">
                        <div class="pr-2">
                            <span class="font-bold text-slate-900">{{ $d->quantity }}x {{ $d->product?->name ?? 'Menu' }}</span>
                            @if(!empty($d->notes))
                                <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                    {{ $d->notes }}
                                </div>
                            @endif
                        </div>
                        <span class="font-black text-slate-900 shrink-0">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                
                @if($transaction->tax > 0)
                    <div class="flex justify-between items-center pt-2 border-t border-slate-50 text-slate-500">
                        <span>Pajak PB1</span>
                        <span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Primary Action: Saya Sudah Membayar (Original Emerald Theme) --}}
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

        {{-- Cancel Order Button with SweetAlert2 (Original Style) --}}
        <div>
            <button type="button" 
                    onclick="confirmCancelOrder()"
                    class="w-full py-3 rounded-2xl bg-white border border-slate-200/90 text-rose-600 hover:bg-rose-50 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs cursor-pointer">
                <i class="fas fa-times-circle text-xs"></i>
                <span>Batalkan Pesanan & Kembali ke Menu</span>
            </button>
        </div>

    </div>

    {{-- Footer Info --}}
    <div class="max-w-md mx-auto w-full text-center text-[11px] text-slate-400 pb-safe">
        Pembayaran diamankan otomatis. Pesanan langsung masuk antrean dapur setelah pembayaran lunas.
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
