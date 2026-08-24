<div class="space-y-2" x-data="{ showDiscountTax: {{ ($discount > 0 || $tax > 0) ? 'true' : 'false' }} }">
    @if($currentOpenBillId)
        <div class="p-2 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 rounded-xl flex items-center justify-between text-xs">
            <div class="flex items-center gap-1.5 text-amber-800 dark:text-amber-300 font-bold truncate">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"></path></svg>
                <span class="truncate">Edit Bill: {{ $tableNumber ? 'Meja '.$tableNumber : ($customerName ?: $currentOpenBillInvoice) }}</span>
            </div>
            <button type="button" wire:click="resetTransaction" class="text-[10px] text-amber-700 hover:text-amber-900 dark:text-amber-400 font-extrabold underline shrink-0 ml-1 cursor-pointer">
                Batal Edit
            </button>
        </div>
    @endif

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
    @if ($orderType === 'dine_in')
        {{-- DINE IN: Tombol Simpan Bill & Bayar Sekarang --}}
        <div class="grid grid-cols-2 gap-2 pt-1">
            {{-- Tombol Simpan / Open Bill --}}
            <button type="button" wire:click="saveOpenBill"
                class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 py-2.5 px-3 rounded-xl font-bold text-xs sm:text-sm shadow-2xs transition-all active:scale-[0.99] flex items-center justify-center gap-1.5 cursor-pointer"
                title="Simpan pesanan meja (Bayar nanti saat selesai nongkrong)">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H7.5m9 0h1.5A2.25 2.25 0 0120.25 6v12a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5"></path></svg>
                <span class="truncate">{{ $currentOpenBillId ? 'Update Bill' : 'Simpan Bill' }}</span>
            </button>

            {{-- Tombol Bayar Sekarang --}}
            <button type="button" wire:click="openPaymentModal"
                class="bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 px-3 rounded-xl font-bold text-xs sm:text-sm shadow-xs transition-all active:scale-[0.99] flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="truncate">Bayar</span>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg>
            </button>
        </div>
    @else
        {{-- TAKE AWAY / DELIVERY: Hanya tombol Bayar Sekarang Full Width --}}
        <div class="pt-1">
            <button type="button" wire:click="openPaymentModal"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm shadow-xs transition-all active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                <span>Proses Bayar</span>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg>
            </button>
        </div>
    @endif
</div>