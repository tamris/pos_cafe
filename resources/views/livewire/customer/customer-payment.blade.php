<div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between p-4 sm:p-6 lg:p-8 pt-10 sm:pt-6">

    {{-- Midtrans Snap JS --}}
    @if(!empty($clientKey))
        <script src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" 
                data-client-key="{{ $clientKey }}"></script>
    @endif

    {{-- Top Header (With Back/Cancel Button) --}}
    <div class="max-w-md mx-auto w-full flex items-center justify-between pb-3.5 border-b border-slate-200/80">
        <button type="button" 
                onclick="confirmCancelOrder()"
                class="w-9 h-9 rounded-2xl bg-white border border-slate-200 text-slate-600 hover:text-rose-600 flex items-center justify-center text-xs shadow-2xs cursor-pointer">
            <i class="fas fa-arrow-left"></i>
        </button>
        <span class="text-xs font-black text-slate-900 uppercase tracking-widest font-heading">
            Pembayaran Online
        </span>
        <div class="w-9"></div> {{-- Spacer for perfect centering --}}
    </div>

    {{-- Main Payment Container (Centered for Mobile & Tablet) --}}
    <div class="max-w-md mx-auto w-full my-auto py-4 space-y-4">

        {{-- Midtrans & QRIS Payment Card --}}
        <div class="bg-white rounded-3xl p-6 shadow-md border border-slate-200/80 text-center relative overflow-hidden">
            
            {{-- Sandbox Mode Badge --}}
            @if(!$isProduction)
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 text-amber-800 border border-amber-300/60 text-[10px] font-black uppercase tracking-wider mb-3">
                    <i class="fas fa-flask text-[10px] text-amber-600"></i> Midtrans Sandbox Mode
                </div>
            @endif

            {{-- Clean Centered Payment Gateways Header --}}
            <div class="flex items-center justify-center gap-2 pb-3.5 border-b border-slate-100 mb-4">
                <span class="text-2xl font-black text-[#0e382c] tracking-tight font-heading">Midtrans</span>
                <span class="text-xs px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold">Snap Gateway</span>
            </div>

            {{-- Supported Payment Methods Logos / Badges --}}
            <div class="flex flex-wrap items-center justify-center gap-1.5 mb-4 text-[11px] font-bold text-slate-600">
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 border border-slate-200 text-slate-700">QRIS</span>
                <span class="px-2.5 py-1 rounded-xl bg-blue-50 border border-blue-200 text-blue-700">GoPay</span>
                <span class="px-2.5 py-1 rounded-xl bg-orange-50 border border-orange-200 text-orange-700">ShopeePay</span>
                <span class="px-2.5 py-1 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700">BCA / VA</span>
                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">DANA</span>
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
            <div class="bg-gradient-to-br from-slate-50 to-emerald-50/40 py-3.5 px-4 rounded-2xl border border-emerald-200/60 mb-5">
                <span class="text-[11px] text-slate-500 font-medium block mb-0.5">Total Tagihan Pembayaran</span>
                <span class="text-2xl sm:text-3xl font-black text-[#0e382c] font-heading">
                    Rp {{ number_format($transaction->total, 0, ',', '.') }}
                </span>
            </div>

            {{-- Snap Error Alert if any --}}
            @if($snapError)
                <div class="p-3.5 mb-4 rounded-2xl bg-rose-50 border border-rose-200 text-left text-xs text-rose-950 flex items-start gap-2.5">
                    <i class="fas fa-exclamation-triangle text-rose-600 mt-0.5"></i>
                    <div>
                        <strong class="block font-bold">Peringatan:</strong>
                        <span>{{ $snapError }}</span>
                    </div>
                </div>
            @endif

            {{-- Primary Action: Open Midtrans Snap Popup --}}
            @if($snapToken)
                <button type="button" 
                        onclick="payWithMidtrans()"
                        class="w-full py-4 px-4 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-black text-sm shadow-lg shadow-[#0e382c]/25 flex items-center justify-center gap-2.5 transition cursor-pointer">
                    <i class="fas fa-shield-alt text-emerald-400 text-base"></i>
                    <span>Bayar Sekarang (Midtrans Snap)</span>
                    <i class="fas fa-arrow-right text-xs ml-1"></i>
                </button>
            @else
                <button type="button" 
                        wire:click="initSnap"
                        class="w-full py-3.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="fas fa-redo"></i>
                    <span>Muat Ulang Sesi Pembayaran</span>
                </button>
            @endif

            {{-- Manual Status Sync Button --}}
            <div class="mt-3">
                <button type="button"
                        wire:click="checkPaymentStatus"
                        wire:loading.attr="disabled"
                        class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="fas fa-sync-alt text-[10px]" wire:loading.class="fa-spin"></i>
                    <span wire:loading.remove>Saya Sudah Bayar (Cek Status)</span>
                    <span wire:loading>Memverifikasi Pembayaran...</span>
                </button>
            </div>

            {{-- Sandbox Quick Simulator Notice & Button --}}
            @if(!$isProduction)
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-left flex items-start gap-2.5 mb-2.5">
                        <div class="w-5 h-5 rounded-md bg-amber-600 text-white flex items-center justify-center text-[10px] shrink-0 mt-0.5">
                            <i class="fas fa-vial"></i>
                        </div>
                        <div class="text-[11px] text-amber-950">
                            <strong class="font-bold block">Testing Sandbox Mode:</strong>
                            Gunakan popup Midtrans di atas, atau klik tombol simulasi di bawah untuk langsung menguji alur dapur tanpa pembayaran asli.
                        </div>
                    </div>

                    <button type="button" 
                            wire:click="simulatePaymentSuccess"
                            wire:loading.attr="disabled"
                            class="w-full py-2.5 rounded-xl bg-emerald-600/10 hover:bg-emerald-600/20 text-emerald-800 border border-emerald-300 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                        <i class="fas fa-check-circle text-emerald-600"></i>
                        <span>Simulasi Bayar Instan (Bypass Test)</span>
                    </button>
                </div>
            @endif

        </div>

        {{-- Cancel Order Button with SweetAlert2 --}}
        <div>
            <button type="button" 
                    onclick="confirmCancelOrder()"
                    class="w-full py-3 rounded-2xl bg-white border border-slate-200/90 text-rose-600 hover:bg-rose-50 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs cursor-pointer">
                <i class="fas fa-times-circle text-xs"></i>
                <span>Batalkan Pesanan & Kembali ke Menu</span>
            </button>
        </div>

        {{-- Accordion Order Items Breakdown --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-xs font-black text-slate-800">
                <span>Rincian Item ({{ $transaction->details->count() }} menu)</span>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-slate-100 space-y-2 text-xs">
                @foreach($transaction->details as $d)
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-slate-900">{{ $d->quantity }}x {{ $d->product?->name ?? 'Menu' }}</span>
                            @if(!empty($d->notes))
                                <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                    {{ $d->notes }}
                                </div>
                            @endif
                        </div>
                        <span class="font-black text-slate-900">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
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

    </div>

    {{-- Footer Info --}}
    <div class="max-w-md mx-auto w-full text-center text-[11px] text-slate-400 pb-safe">
        Pembayaran diamankan otomatis oleh Midtrans. Pesanan langsung masuk antrean kasir setelah pembayaran lunas.
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

        window.addEventListener('remove-token-localstorage', event => {
            let removeToken = event.detail.token;
            let tokens = JSON.parse(localStorage.getItem('self_order_tokens') || '[]');
            tokens = tokens.filter(t => t !== removeToken);
            localStorage.setItem('self_order_tokens', JSON.stringify(tokens));
        });

        function payWithMidtrans() {
            let snapToken = '{{ $snapToken }}';
            if (!snapToken) {
                alert('Sesi pembayaran Midtrans belum siap. Silakan muat ulang halaman.');
                return;
            }

            if (typeof window.snap === 'undefined') {
                alert('Modul pembayaran Midtrans sedang dimuat. Mohon tunggu beberapa detik lalu coba lagi.');
                return;
            }

            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    console.log('Midtrans Payment Success:', result);
                    window.location.href = "{{ route('customer.status', ['token' => $transaction->order_token]) }}";
                },
                onPending: function(result) {
                    console.log('Midtrans Payment Pending:', result);
                    window.location.href = "{{ route('customer.status', ['token' => $transaction->order_token]) }}";
                },
                onError: function(result) {
                    console.error('Midtrans Payment Error:', result);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pembayaran Gagal / Ditolak',
                            text: result.status_message || 'Terjadi kendala dalam proses pembayaran.',
                            confirmButtonColor: '#e11d48'
                        });
                    } else {
                        alert('Pembayaran Gagal: ' + (result.status_message || ''));
                    }
                },
                onClose: function() {
                    console.log('Customer closed Midtrans Snap without completing payment.');
                }
            });
        }

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
