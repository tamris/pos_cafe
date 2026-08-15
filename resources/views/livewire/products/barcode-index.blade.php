<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', ['title' => 'Cetak Label Barcode', 'subtitle' => 'Pilih produk untuk dicetak labelnya'])

        <main class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 h-fit transition-colors">
                        
                        <div class="mb-6">
                            <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Cari Produk
                            </h3>
                            
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    class="w-full pl-10 pr-10 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent text-sm transition-all bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                                    placeholder="Scan barcode atau ketik nama...">
                                
                                <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                
                                <div wire:loading wire:target="search" class="absolute right-3 top-3">
                                    <svg class="animate-spin h-5 w-5 text-slate-400 dark:text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                @if(!empty($searchResults) && strlen($search) > 1)
                                    <div class="absolute z-20 w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-80 overflow-y-auto ring-1 ring-black ring-opacity-5">
                                        @foreach($searchResults as $result)
                                            <button wire:click="addToQueue({{ $result->id }})" 
                                                class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-100 dark:border-slate-700 last:border-0 transition-colors group">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <p class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $result->name }}</p>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">Code: {{ $result->barcode ?? $result->sku ?? '-' }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-1 rounded">
                                                            Rp {{ number_format($result->price, 0, ',', '.') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif(strlen($search) > 1 && empty($searchResults))
                                    <div class="absolute z-20 w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl p-4 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Produk tidak ditemukan
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex gap-3">
                                <div class="text-blue-500 dark:text-blue-400 mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-blue-900 dark:text-blue-100 text-sm mb-1">Informasi Cetak</h4>
                                    <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 list-disc list-inside">
                                        <li>Gunakan kertas label ukuran <strong>108mm</strong> (A6) atau A4.</li>
                                        <li>Pastikan produk memiliki kode Barcode/SKU.</li>
                                        <li>Atur margin printer ke "None" saat mencetak.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col h-full min-h-[500px] transition-colors">
                        
                        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white text-lg">Antrian Cetak</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar produk yang akan dicetak labelnya</p>
                            </div>
                            
                            @if(count($printQueue) > 0)
                                <button wire:click="$set('printQueue', [])" 
                                    class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Kosongkan
                                </button>
                            @endif
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50/50 dark:bg-slate-700/50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[40%]">Produk</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Harga</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Jumlah</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @forelse($printQueue as $index => $item)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                                            {{-- Info Produk --}}
                                            <td class="px-6 py-4 align-middle">
                                                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm mb-1">{{ $item['name'] }}</p>
                                                <span class="inline-block bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-[10px] font-mono px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600">
                                                    {{ $item['barcode'] }}
                                                </span>
                                            </td>

                                            {{-- Harga --}}
                                            <td class="px-6 py-4 align-middle text-sm text-slate-600 dark:text-slate-400 font-medium">
                                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                                            </td>

                                            {{-- Input Jumlah (Model Kapsul) --}}
                                            <td class="px-6 py-4 align-middle">
                                                <div class="flex items-center justify-center">
                                                    <div class="flex items-center border border-slate-200 dark:border-slate-600 rounded-lg overflow-hidden shadow-sm">
                                                        <button wire:click="updateQuantity({{ $index }}, {{ max(1, $item['quantity'] - 1) }})" 
                                                            class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors border-r border-slate-200 dark:border-slate-600 active:bg-slate-100 dark:active:bg-slate-600">
                                                            -
                                                        </button>
                                                        <input type="number" min="1" 
                                                            wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                            value="{{ $item['quantity'] }}"
                                                            class="w-12 text-center border-none text-sm font-bold text-slate-700 dark:text-slate-300 focus:ring-0 p-0 appearance-none bg-white dark:bg-slate-800">
                                                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" 
                                                            class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors border-l border-slate-200 dark:border-slate-600 active:bg-slate-100 dark:active:bg-slate-600">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Tombol Hapus --}}
                                            <td class="px-6 py-4 align-middle text-right">
                                                <button wire:click="removeFromQueue({{ $index }})" 
                                                    class="p-2 text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                                                    title="Hapus item">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-24 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="w-20 h-20 bg-slate-50 dark:bg-slate-700/50 rounded-full flex items-center justify-center mb-4 border border-slate-100 dark:border-slate-600">
                                                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                        </svg>
                                                    </div>
                                                    <h4 class="text-slate-900 dark:text-white font-medium mb-1">Antrian Cetak Kosong</h4>
                                                    <p class="text-slate-500 dark:text-slate-400 text-sm">Pilih produk dari kolom kiri untuk mulai mencetak.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(count($printQueue) > 0)
                            <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-xl flex justify-between items-center">
                                <div class="text-sm text-slate-600 dark:text-slate-300">
                                    Total Label: <strong class="dark:text-white">{{ array_sum(array_column($printQueue, 'quantity')) }}</strong> pcs
                                </div>
                                
                                <form action="{{ route('barcodes.print') }}" method="POST" target="_blank" class="flex items-center gap-4">
                                    @csrf
                                    <input type="hidden" name="products" value="{{ json_encode($printQueue) }}">
                                    
                                    {{-- CHECKBOX TAMPILKAN HARGA --}}
                                    <div class="flex items-center">
                                        <input id="show_price" name="show_price" type="checkbox" value="1" 
                                            class="w-4 h-4 text-slate-900 bg-gray-100 border-gray-300 rounded focus:ring-slate-900 dark:focus:ring-slate-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="show_price" class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                                            Tampilkan Harga
                                        </label>
                                    </div>

                                    <button type="submit" class="bg-slate-900 dark:bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-slate-800 dark:hover:bg-blue-700 transition-all font-bold flex items-center gap-3 shadow-lg shadow-slate-200 dark:shadow-none hover:-translate-y-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                        Cetak Sekarang
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-xl flex justify-end">
                                <button disabled class="bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 px-8 py-3 rounded-xl font-bold flex items-center gap-3 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Cetak Sekarang
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>