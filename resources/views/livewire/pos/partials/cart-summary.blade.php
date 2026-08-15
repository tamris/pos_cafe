<div class="space-y-3">
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Diskon (%)</label>
            <input type="number" wire:model.live="discount"
                class="w-full px-3 py-2 text-xs font-bold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent transition-all"
                placeholder="0">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Pajak (%)</label>
            <input type="number" wire:model.live="tax"
                class="w-full px-3 py-2 text-xs font-bold border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent transition-all"
                placeholder="0">
        </div>
    </div>

    <div class="space-y-2 pt-3 border-t border-dashed border-slate-200 dark:border-slate-700">
        <div class="flex justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-medium">Subtotal</span>
            <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
        @if ($discount > 0)
            <div class="flex justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Diskon ({{ $discount }}%)</span>
                <span class="font-bold text-red-600 dark:text-red-400">
                    - Rp {{ number_format(($subtotal * $discount) / 100, 0, ',', '.') }}
                </span>
            </div>
        @endif
        @if ($tax > 0)
            <div class="flex justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Pajak ({{ $tax }}%)</span>
                <span class="font-bold text-slate-900 dark:text-white">
                    + Rp {{ number_format(($subtotal * $tax) / 100, 0, ',', '.') }}
                </span>
            </div>
        @endif
        <div class="flex justify-between items-end pt-2 border-t border-slate-200 dark:border-slate-700">
            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Total Tagihan</span>
            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3 pt-2">
        {{-- Tombol Reset --}}
        <button type="button" onclick="confirmResetCart()"
            class="col-span-1 flex items-center justify-center border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 py-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-red-600 transition-colors"
            title="Reset Transaksi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </button>

        {{-- Tombol Bayar --}}
        <button wire:click="openPaymentModal"
            class="col-span-2 bg-slate-900 dark:bg-blue-600 text-white py-2.5 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 active:scale-95 transition-all font-bold text-xs shadow-sm flex items-center justify-center gap-2">
            <span>Bayar Sekarang</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
    </div>
</div>