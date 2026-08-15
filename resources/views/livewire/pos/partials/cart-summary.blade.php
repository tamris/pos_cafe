<div class="space-y-3" x-data="{ showDiscountTax: {{ ($discount > 0 || $tax > 0) ? 'true' : 'false' }} }">
    {{-- Collapsible Discount & Tax Button --}}
    <div>
        <div class="flex items-center justify-between">
            <button type="button" @click="showDiscountTax = !showDiscountTax" 
                class="text-xs font-medium text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center gap-1 transition-colors">
                <span x-text="showDiscountTax ? '▼ Sembunyikan Diskon & Pajak' : '▶ Tambah Diskon / Pajak'"></span>
                @if($discount > 0 || $tax > 0)
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-600">
                        {{ $discount > 0 ? 'Disc '.$discount.'%' : '' }} {{ $tax > 0 ? 'Tax '.$tax.'%' : '' }}
                    </span>
                @endif
            </button>
        </div>

        {{-- Expanded Discount/Tax inputs --}}
        <div x-show="showDiscountTax" x-collapse x-cloak class="pt-2">
            <div class="grid grid-cols-2 gap-2.5 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Diskon (%)</label>
                    <input type="number" min="0" max="100" wire:model.live="discount"
                        class="w-full px-3 py-2 text-xs font-semibold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="0">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Pajak (%)</label>
                    <input type="number" min="0" max="100" wire:model.live="tax"
                        class="w-full px-3 py-2 text-xs font-semibold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="0">
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items Summary --}}
    <div class="space-y-1.5 pt-2 border-t border-dashed border-slate-200 dark:border-slate-700">
        <div class="flex justify-between text-xs text-slate-600 dark:text-slate-400">
            <span>Subtotal ({{ count($cart) }} menu)</span>
            <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
        
        @if ($discount > 0)
            <div class="flex justify-between text-xs text-red-600 dark:text-red-400 font-medium">
                <span>Diskon ({{ $discount }}%)</span>
                <span>- Rp {{ number_format(($subtotal * $discount) / 100, 0, ',', '.') }}</span>
            </div>
        @endif

        @if ($tax > 0)
            <div class="flex justify-between text-xs text-slate-600 dark:text-slate-400 font-medium">
                <span>Pajak ({{ $tax }}%)</span>
                <span>+ Rp {{ number_format(($subtotal * $tax) / 100, 0, ',', '.') }}</span>
            </div>
        @endif

        <div class="flex justify-between items-end pt-2 border-t border-slate-200 dark:border-slate-700">
            <span class="text-sm font-bold text-slate-800 dark:text-slate-200">Total Tagihan</span>
            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="grid grid-cols-4 gap-2.5 pt-2">
        {{-- Tombol Batal/Reset --}}
        <button type="button" onclick="confirmResetCart()"
            class="col-span-1 flex items-center justify-center border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-red-600 dark:hover:text-red-400 rounded-lg py-2.5 transition-colors"
            title="Reset Transaksi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </button>

        {{-- Tombol Bayar Sekarang --}}
        <button type="button" wire:click="openPaymentModal"
            class="col-span-3 bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white py-2.5 rounded-lg font-semibold text-xs sm:text-sm shadow-md transition-all active:scale-98 flex items-center justify-center gap-2">
            <span>Bayar Sekarang</span>
            <span class="opacity-90">(Rp {{ number_format($total, 0, ',', '.') }})</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
    </div>
</div>