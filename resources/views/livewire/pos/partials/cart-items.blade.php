@forelse($cart as $index => $item)
    <div class="p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 hover:border-slate-300 dark:hover:border-slate-600 transition-colors space-y-2">
        {{-- Item Header & Stepper --}}
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0 pr-2">
                <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm line-clamp-1 leading-snug">{{ $item['name'] }}</p>
                <p class="text-[11px] sm:text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                </p>
            </div>
            
            {{-- Stepper & Delete --}}
            <div class="flex items-center space-x-1 sm:space-x-1.5 shrink-0">
                {{-- Tombol Kurang --}}
                <button type="button" wire:click="updateQuantity({{ $index }}, 'decrease')"
                    class="w-7 h-7 flex items-center justify-center bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-700 dark:text-slate-200 transition-colors text-xs font-bold active:scale-95">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"></path></svg>
                </button>
                
                {{-- Angka Qty --}}
                <span class="w-6 text-center font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ $item['quantity'] }}</span>
                
                {{-- Tombol Tambah --}}
                <button type="button" wire:click="updateQuantity({{ $index }}, 'increase')"
                    class="w-7 h-7 flex items-center justify-center bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-700 dark:text-slate-200 transition-colors text-xs font-bold active:scale-95">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                </button>

                {{-- Tombol Hapus --}}
                <button type="button" wire:click="removeFromCart({{ $index }})"
                    class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors ml-0.5"
                    title="Hapus item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>

        {{-- Item Subtotal & Notes --}}
        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <div class="min-w-0 pr-2">
                @if(!empty($item['notes']))
                    <button type="button" wire:click="openItemNotesModal({{ $index }})"
                        class="text-[11px] font-medium text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded-md hover:border-emerald-400 max-w-[150px] sm:max-w-[180px] truncate text-left flex items-center gap-1.5"
                        title="Ubah catatan">
                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path></svg>
                        <span class="truncate">{{ $item['notes'] }}</span>
                    </button>
                @else
                    <button type="button" wire:click="openItemNotesModal({{ $index }})"
                        class="text-[11px] text-slate-400 hover:text-emerald-600 dark:text-slate-500 dark:hover:text-emerald-400 font-medium hover:underline flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path></svg>
                        <span>Catatan / Opsi</span>
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
        <div class="p-3 bg-slate-100 dark:bg-slate-700/50 rounded-2xl mb-3 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
        </div>
        <p class="text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-bold">Keranjang Pesanan Kosong</p>
        <p class="text-slate-400 dark:text-slate-500 text-[11px] mt-0.5">Pilih menu di sebelah kiri untuk menambahkan</p>
    </div>
@endforelse