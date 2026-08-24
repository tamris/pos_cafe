<div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between p-4 sm:p-6 lg:p-8 pt-10 sm:pt-6">

    {{-- Top Header (With Back/Cancel Button) --}}
    <div class="max-w-md mx-auto w-full flex items-center justify-between pb-3.5 border-b border-slate-200/80">
        <button type="button" 
                onclick="confirmCancelOrder()"
                class="w-9 h-9 rounded-2xl bg-white border border-slate-200 text-slate-600 hover:text-rose-600 flex items-center justify-center text-xs shadow-2xs cursor-pointer">
            <i class="fas fa-arrow-left"></i>
        </button>
        <span class="text-xs font-black text-slate-900 uppercase tracking-widest font-heading">
            Pembayaran QRIS
        </span>
        <div class="w-9"></div> {{-- Spacer for perfect centering --}}
    </div>

    {{-- Main Payment Container (Centered for Mobile & Tablet) --}}
    <div class="max-w-md mx-auto w-full my-auto py-4 space-y-4">

        {{-- Official Style Clean QRIS Card --}}
        <div class="bg-white rounded-3xl p-6 shadow-md border border-slate-200/80 text-center relative overflow-hidden">
            
            {{-- Clean Centered QRIS Header --}}
            <div class="flex items-center justify-center pb-3.5 border-b border-slate-100 mb-4">
                <span class="text-2xl font-black text-rose-600 tracking-tighter font-heading">QRIS</span>
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
            <div class="bg-slate-50 py-3.5 px-4 rounded-2xl border border-slate-200/60 mb-5">
                <span class="text-[11px] text-slate-500 font-medium block mb-0.5">Total yang Harus Dibayar</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 font-heading">
                    Rp {{ number_format($transaction->total, 0, ',', '.') }}
                </span>
            </div>

            {{-- QR Code Scan Frame --}}
            <div class="inline-block p-4 bg-white rounded-2xl border-2 border-dashed border-slate-300 shadow-inner relative">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('customer.status', ['token' => $transaction->order_token])) }}" 
                     alt="QRIS Payment Code" 
                     class="w-48 h-48 mx-auto rounded-lg">
                <div class="mt-2.5 text-[10px] text-slate-400 font-medium max-w-[220px] mx-auto leading-tight">
                    Mendukung BCA, GoPay, OVO, ShopeePay, DANA, LinkAja & Mobile Banking
                </div>
            </div>

            {{-- Simulation Alert Notice --}}
            <div class="mt-5 p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-left flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-lg bg-amber-600 text-white flex items-center justify-center text-xs shrink-0 mt-0.5 shadow-2xs">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="text-xs text-amber-950">
                    <strong class="font-black block">Mode Simulator Pengujian:</strong>
                    Klik tombol hijau di bawah untuk mensimulasikan pembayaran QRIS berhasil secara instan.
                </div>
            </div>

        </div>

        {{-- Action Button: Simulator Trigger --}}
        <div>
            <button type="button" 
                    wire:click="simulatePaymentSuccess"
                    wire:loading.attr="disabled"
                    class="w-full py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-sm shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2 transition cursor-pointer">
                <i class="fas fa-check-circle text-base"></i>
                <span wire:loading.remove>Simulasi Bayar Berhasil (Klik Disini)</span>
                <span wire:loading><i class="fas fa-spinner fa-spin mr-1"></i> Mengonfirmasi Pembayaran...</span>
            </button>
        </div>

        {{-- Cancel Order Button with SweetAlert2 --}}
        <div>
            <button type="button" 
                    onclick="confirmCancelOrder()"
                    class="w-full py-3.5 rounded-2xl bg-white border border-slate-200/90 text-rose-600 hover:bg-rose-50 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs cursor-pointer">
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
            </div>
        </div>

    </div>

    {{-- Footer Info --}}
    <div class="max-w-md mx-auto w-full text-center text-[11px] text-slate-400 pb-safe">
        Pembayaran diamankan otomatis. Pesanan langsung masuk antrean kasir setelah pembayaran lunas.
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
