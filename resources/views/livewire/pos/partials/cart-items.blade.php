@forelse($cart as $index => $item)
    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200/80 dark:border-slate-700/80 hover:border-slate-300 dark:hover:border-slate-600 transition-colors space-y-2">
        {{-- Item Header & Stepper --}}
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0 pr-2">
                <p class="font-semibold text-slate-900 dark:text-white text-xs sm:text-sm line-clamp-1 leading-snug">{{ $item['name'] }}</p>
                <p class="text-[11px] sm:text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">
                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                </p>
            </div>
            
            {{-- Stepper & Delete --}}
            <div class="flex items-center space-x-1 sm:space-x-1.5 shrink-0">
                {{-- Tombol Kurang --}}
                <button type="button" wire:click="updateQuantity({{ $index }}, 'decrease')"
                    class="w-7 h-7 flex items-center justify-center bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-700 dark:text-slate-200 transition-colors text-xs font-bold active:scale-95">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </button>
                
                {{-- Angka Qty --}}
                <span class="w-6 text-center font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ $item['quantity'] }}</span>
                
                {{-- Tombol Tambah --}}
                <button type="button" wire:click="updateQuantity({{ $index }}, 'increase')"
                    class="w-7 h-7 flex items-center justify-center bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-700 dark:text-slate-200 transition-colors text-xs font-bold active:scale-95">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>

                {{-- Tombol Hapus --}}
                <button type="button" wire:click="removeFromCart({{ $index }})"
                    class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors ml-0.5"
                    title="Hapus item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>

        {{-- Item Subtotal & Notes --}}
        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <div class="min-w-0 pr-2">
                @if(!empty($item['notes']))
                    <button type="button" wire:click="openItemNotesModal({{ $index }})"
                        class="text-[11px] font-mono text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded hover:underline max-w-[150px] sm:max-w-[180px] truncate text-left block"
                        title="Ubah catatan">
                        📝 {{ $item['notes'] }}
                    </button>
                @else
                    <button type="button" wire:click="openItemNotesModal({{ $index }})"
                        class="text-[11px] text-slate-400 hover:text-emerald-600 dark:text-slate-500 dark:hover:text-emerald-400 font-medium hover:underline flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>Opsi / Catatan</span>
                    </button>
                @endif
            </div>

            {{-- Line Subtotal --}}
            <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm shrink-0">
                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
            </span>
        </div>
    </div>
@empty
    <div class="text-center py-10 flex flex-col items-center justify-center">
        <div class="p-3 bg-slate-100 dark:bg-slate-700/50 rounded-full mb-3 text-slate-400 dark:text-slate-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <p class="text-slate-600 dark:text-slate-300 text-sm font-semibold">Keranjang Pesanan Kosong</p>
        <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Pilih menu di sebelah kiri untuk menambahkan</p>
    </div>
@endforelse