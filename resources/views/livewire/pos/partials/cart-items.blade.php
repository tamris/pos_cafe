@forelse($cart as $index => $item)
    <div class="flex flex-col p-3 bg-slate-50 dark:bg-slate-700/40 rounded-lg border border-slate-200 dark:border-slate-700 gap-2">
        <div class="flex items-center justify-between">
            <div class="flex-1 pr-2">
                <p class="font-bold text-slate-900 dark:text-white text-sm line-clamp-1">{{ $item['name'] }}</p>
                <p class="text-xs text-amber-600 dark:text-amber-400 font-bold">
                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                </p>
            </div>
            
            <div class="flex items-center space-x-2">
                {{-- Tombol Kurang --}}
                <button wire:click="updateQuantity({{ $index }}, 'decrease')"
                    class="w-7 h-7 flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg shadow-sm transition-colors text-slate-600 dark:text-slate-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </button>
                
                {{-- Angka Qty --}}
                <span class="w-6 text-center font-bold text-sm text-slate-900 dark:text-white">{{ $item['quantity'] }}</span>
                
                {{-- Tombol Tambah --}}
                <button wire:click="updateQuantity({{ $index }}, 'increase')"
                    class="w-7 h-7 flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg shadow-sm transition-colors text-slate-600 dark:text-slate-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>

            {{-- Tombol Hapus --}}
            <button wire:click="removeFromCart({{ $index }})"
                class="text-slate-400 hover:text-red-600 p-1 ml-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>

        {{-- Notes Section / Add Customization --}}
        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200 dark:border-slate-600">
            @if(!empty($item['notes']))
                <span class="text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded font-mono text-[11px] truncate max-w-[200px]">
                    📝 {{ $item['notes'] }}
                </span>
            @else
                <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">Tanpa catatan khusus</span>
            @endif
            <button wire:click="openItemNotesModal({{ $index }})" class="text-amber-600 hover:text-amber-700 dark:text-amber-400 font-semibold hover:underline flex items-center gap-1 text-[11px]">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Catatan / Options
            </button>
        </div>
    </div>
@empty
    <div class="text-center py-10 flex flex-col items-center justify-center h-full">
        <div class="p-3 bg-slate-100 dark:bg-slate-700/50 rounded-full mb-3 text-slate-400 dark:text-slate-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <p class="text-slate-600 dark:text-slate-300 text-sm font-semibold">Keranjang Pesanan Kosong</p>
        <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Pilih menu di sebelah kiri untuk menambahkan</p>
    </div>
@endforelse