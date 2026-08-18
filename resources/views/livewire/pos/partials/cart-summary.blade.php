<div class="space-y-2" x-data="{ showDiscountTax: {{ ($discount > 0 || $tax > 0) ? 'true' : 'false' }} }">
    {{-- Collapsible Discount & Tax Button --}}
    <div class="flex items-center justify-between">
        <button type="button" @click="showDiscountTax = !showDiscountTax" 
            class="text-[11px] font-semibold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 flex items-center gap-1.5 transition-colors">
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="showDiscountTax ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path></svg>
            <span x-text="showDiscountTax ? 'Tutup Diskon & Pajak' : 'Diskon & Pajak (Opsional)'"></span>
            @if($discount > 0 || $tax > 0)
                <span class="text-[10px] bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-bold px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                    {{ $discount > 0 ? 'Disc '.$discount.'%' : '' }} {{ $tax > 0 ? 'Tax '.$tax.'%' : '' }}
                </span>
            @endif
        </button>
    </div>

    {{-- Expanded Discount/Tax inputs --}}
    <div x-show="showDiscountTax" x-cloak class="pt-1" style="display: none;">
        <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700">
            <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Diskon (%)</label>
                <input type="number" min="0" max="100" wire:model.live="discount"
                    class="w-full px-2.5 py-1.5 text-xs font-bold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500"
                    placeholder="0">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Pajak (%)</label>
                <input type="number" min="0" max="100" wire:model.live="tax"
                    class="w-full px-2.5 py-1.5 text-xs font-bold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500"
                    placeholder="0">
            </div>
        </div>
    </div>

    {{-- Line Items Summary --}}
    <div class="space-y-1 pt-1.5 border-t border-dashed border-slate-200 dark:border-slate-700">
        <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Subtotal ({{ count($cart) }} item)</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
        
        @if ($discount > 0)
            <div class="flex justify-between text-[11px] text-rose-600 dark:text-rose-400 font-semibold">
                <span>Diskon ({{ $discount }}%)</span>
                <span>- Rp {{ number_format(($subtotal * $discount) / 100, 0, ',', '.') }}</span>
            </div>
        @endif

        @if ($tax > 0)
            <div class="flex justify-between text-[11px] text-slate-600 dark:text-slate-400 font-semibold">
                <span>Pajak ({{ $tax }}%)</span>
                <span>+ Rp {{ number_format(($subtotal * $tax) / 100, 0, ',', '.') }}</span>
            </div>
        @endif

        <div class="flex justify-between items-center pt-1.5 border-t border-slate-200 dark:border-slate-700">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Total Bayar</span>
            <span class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Action CTA buttons --}}
    <div class="grid grid-cols-4 gap-2 pt-1">
        {{-- Tombol Batal/Reset --}}
        <button type="button" onclick="confirmResetCart()"
            class="col-span-1 flex items-center justify-center border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl py-2.5 transition-colors shadow-2xs"
            title="Kosongkan Keranjang">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path></svg>
        </button>

        {{-- Tombol Bayar Sekarang (Emerald CTA) --}}
        <button type="button" wire:click="openPaymentModal"
            class="col-span-3 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 px-3 rounded-xl font-bold text-xs sm:text-sm shadow-xs transition-all active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
            <span>Proses Bayar</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg>
        </button>
    </div>
</div>