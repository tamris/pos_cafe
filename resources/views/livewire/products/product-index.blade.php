<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="if (window.innerWidth >= 1280) { sidebarOpen = true } else { sidebarOpen = false }">
    
    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Menu Produk', 
            'subtitle' => 'Kelola seluruh daftar menu makanan & minuman cafe'
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- Flash Message Success --}}
            @if (session()->has('message'))
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-emerald-800 dark:text-emerald-300 font-medium text-xs sm:text-sm">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            {{-- Flash Message Error --}}
            @if (session()->has('error'))
                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-4 animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-rose-800 dark:text-rose-300 font-medium text-xs sm:text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Main Table Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Header Filter & Action Box --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Daftar Menu Produk</h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola data menu, ketersediaan di POS, HPP, serta kode barcode</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                            {{-- Filter Kategori --}}
                            <select wire:model.live="categoryFilter" 
                                class="px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white w-full sm:w-auto cursor-pointer">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>

                            {{-- Search Input --}}
                            <div class="relative w-full sm:w-60">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Cari produk / SKU / barcode..."
                                    class="w-full pl-10 pr-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            {{-- Tombol Tambah Produk (Admin Only) --}}
                            @if(auth()->user()->role === 'admin')
                            <button wire:click="openModal"
                                class="bg-slate-900 dark:bg-blue-600 text-white px-4 py-2 sm:py-2.5 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2 font-semibold text-xs sm:text-sm w-full sm:w-auto shadow-sm active:scale-95 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Tambah Menu</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Status Filter Tabs (Semua, Aktif, Non-aktif, Arsip) --}}
                    <div class="flex items-center gap-2 mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/60 overflow-x-auto scrollbar-none text-xs">
                        <button wire:click="setStatusFilter('all')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                            <span>Semua Menu</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200' }}">{{ $countAll }}</span>
                        </button>

                        <button wire:click="setStatusFilter('active')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200/60 dark:border-emerald-800/40' }}">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Aktif di POS</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'active' ? 'bg-white/20 text-white' : 'bg-emerald-200/70 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200' }}">{{ $countActive }}</span>
                        </button>

                        <button wire:click="setStatusFilter('inactive')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'inactive' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/60 dark:border-amber-800/40' }}">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span>Non-Aktif</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'inactive' ? 'bg-white/20 text-white' : 'bg-amber-200/70 dark:bg-amber-800 text-amber-800 dark:text-amber-200' }}">{{ $countInactive }}</span>
                        </button>

                        <button wire:click="setStatusFilter('trashed')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'trashed' ? 'bg-rose-700 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            <span>Arsip</span>
                            @if($countTrashed > 0)
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'trashed' ? 'bg-white/20 text-white' : 'bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200' }}">{{ $countTrashed }}</span>
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Table Area --}}
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[760px]">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-5 sm:px-6 py-3.5">Produk</th>
                                <th class="px-5 sm:px-6 py-3.5">SKU / Barcode</th>
                                <th class="px-5 sm:px-6 py-3.5">Kategori</th>
                                <th class="px-5 sm:px-6 py-3.5">Harga Jual</th>
                                <th class="px-5 sm:px-6 py-3.5 text-center">Status POS</th>
                                <th class="px-5 sm:px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                            @forelse($products as $product)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors group {{ $product->trashed() ? 'opacity-70 bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                                    {{-- Produk Info --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="shrink-0 h-11 w-11">
                                                @if ($product->image)
                                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-11 h-11 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                                                @else
                                                    <div class="w-11 h-11 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center border border-slate-200 dark:border-slate-600 text-lg">
                                                        ☕
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-900 dark:text-white truncate max-w-[200px] flex items-center gap-1.5">
                                                    <span>{{ $product->name }}</span>
                                                    @if($product->trashed())
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 font-semibold">Terhapus</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-[220px]">
                                                    {{ Str::limit($product->description, 35) ?: 'HPP: Rp ' . number_format($product->harga_beli, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- SKU & Barcode --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                        <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $product->sku }}</span>
                                        @if($product->barcode)
                                            <span class="block text-[10px] font-mono text-slate-400 dark:text-slate-500">{{ $product->barcode }}</span>
                                        @endif
                                    </td>

                                    {{-- Kategori --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                            {{ $product->category->name ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- Harga Jual --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap font-bold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </td>

                                    {{-- Status POS (Toggle Switch) --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-center">
                                        @if($product->trashed())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                Diarsipkan
                                            </span>
                                        @else
                                            <button wire:click="toggleStatus({{ $product->id }})" 
                                                title="Klik untuk ubah status ketersediaan di Kasir POS"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer border {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700 hover:bg-slate-200' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                <span>{{ $product->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            </button>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-right space-x-1.5 shrink-0">
                                        @if($product->trashed())
                                            {{-- Pulihkan (Restore) --}}
                                            <button onclick="confirmRestore({{ $product->id }}, '{{ $product->name }}')"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-emerald-300 dark:border-emerald-800 text-xs font-medium rounded-lg text-emerald-700 dark:text-emerald-400 bg-white dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                Pulihkan
                                            </button>

                                            {{-- Hapus Permanen --}}
                                            <button onclick="confirmForceDelete({{ $product->id }}, '{{ $product->name }}')"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-rose-300 dark:border-rose-800 text-xs font-medium rounded-lg text-rose-700 dark:text-rose-400 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus Permanen
                                            </button>
                                        @else
                                            <button wire:click="edit({{ $product->id }})"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 text-xs font-medium rounded-lg text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit
                                            </button>
                                            
                                            <button onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-rose-300 dark:border-rose-800/60 text-xs font-medium rounded-lg text-rose-700 dark:text-rose-400 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors active:scale-95"
                                                title="Arsipkan / Hapus Menu">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium text-xs sm:text-sm">
                                                @if($statusFilter === 'trashed')
                                                    Arsip kosong. Tidak ada menu yang diarsipkan.
                                                @else
                                                    Tidak ada produk menu ditemukan.
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Area --}}
                <div class="px-5 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $products->links() }}
                </div>
            </div>
        </main>
    </div>

    {{-- MODAL FORM PRODUK --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeModal"></div>

                <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-2xl w-full my-8 border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="save">
                        
                        {{-- Modal Header --}}
                        <div class="bg-slate-900 dark:bg-slate-700 text-white px-5 sm:px-6 py-4 border-b border-slate-800 dark:border-slate-600 flex items-center justify-between">
                            <h3 class="text-base font-bold">
                                {{ $isEdit ? 'Edit Data Menu' : 'Tambah Menu Baru' }}
                            </h3>
                            <button type="button" wire:click="closeModal" class="text-white hover:opacity-80 transition-opacity p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="p-5 sm:p-6 space-y-4 max-h-[calc(100vh-180px)] overflow-y-auto scrollbar-thin">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                
                                {{-- Nama Produk --}}
                                <div class="sm:col-span-2">
                                    <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Nama Menu <span class="text-rose-500">*</span></label>
                                    <input type="text" id="name" wire:model="name" class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="Contoh: Caramel Macchiato">
                                    @error('name') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>

                                {{-- Kategori --}}
                                <div>
                                    <label for="category_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Kategori <span class="text-rose-500">*</span></label>
                                    <select id="category_id" wire:model.live="category_id" class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer">
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>

                                {{-- SKU & Auto Generate --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="sku" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">SKU / Kode <span class="text-rose-500">*</span></label>
                                        <button type="button" wire:click="regenerateSku" class="inline-flex items-center text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors gap-1 group" title="Generate SKU otomatis">
                                            <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <span>Auto</span>
                                        </button>
                                    </div>
                                    <input type="text" id="sku" wire:model="sku" class="block w-full px-3 py-2 font-mono text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="Contoh: COF006">
                                    @error('sku') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>

                                {{-- Harga Jual dengan Auto Format Rupiah --}}
                                <div x-data="{
                                    rawPrice: @entangle('price').live,
                                    displayPrice: '',
                                    formatPrice(val) {
                                        let clean = String(val).replace(/\D/g, '');
                                        this.displayPrice = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                        this.rawPrice = clean ? parseInt(clean, 10) : '';
                                    },
                                    init() {
                                        if (this.rawPrice && this.rawPrice > 0) {
                                            this.displayPrice = new Intl.NumberFormat('id-ID').format(this.rawPrice);
                                        }
                                        this.$watch('rawPrice', (newVal) => {
                                            if (newVal !== undefined && newVal !== null && newVal !== '') {
                                                let clean = String(newVal).replace(/\D/g, '');
                                                let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                if (this.displayPrice !== formatted) {
                                                    this.displayPrice = formatted;
                                                }
                                            } else if (newVal === '' || newVal === null || newVal === 0) {
                                                if (!newVal) this.displayPrice = '';
                                            }
                                        });
                                    }
                                }">
                                    <label for="price" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                        Harga Jual (Rp) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400">Rp</span>
                                        <input type="text" id="price" 
                                            x-model="displayPrice"
                                            @input="formatPrice($event.target.value)"
                                            class="block w-full pl-9 pr-3 py-2 text-xs sm:text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400"
                                            placeholder="Contoh: 25.000">
                                    </div>
                                    @error('price') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>

                                {{-- Harga Beli / HPP dengan Auto Format Rupiah --}}
                                <div x-data="{
                                    rawHpp: @entangle('harga_beli').live,
                                    displayHpp: '',
                                    formatHpp(val) {
                                        let clean = String(val).replace(/\D/g, '');
                                        this.displayHpp = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                        this.rawHpp = clean ? parseInt(clean, 10) : '';
                                    },
                                    init() {
                                        if (this.rawHpp && this.rawHpp > 0) {
                                            this.displayHpp = new Intl.NumberFormat('id-ID').format(this.rawHpp);
                                        }
                                        this.$watch('rawHpp', (newVal) => {
                                            if (newVal !== undefined && newVal !== null && newVal !== '') {
                                                let clean = String(newVal).replace(/\D/g, '');
                                                let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                if (this.displayHpp !== formatted) {
                                                    this.displayHpp = formatted;
                                                }
                                            } else if (newVal === '' || newVal === null || newVal === 0) {
                                                if (!newVal) this.displayHpp = '';
                                            }
                                        });
                                    }
                                }">
                                    <label for="harga_beli" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                        Harga Modal / HPP (Rp) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400">Rp</span>
                                        <input type="text" id="harga_beli" 
                                            x-model="displayHpp"
                                            @input="formatHpp($event.target.value)"
                                            class="block w-full pl-9 pr-3 py-2 text-xs sm:text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400"
                                            placeholder="Contoh: 12.000">
                                    </div>
                                    @error('harga_beli') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                </div>

                                {{-- Status Menu (is_active) --}}
                                <div class="sm:col-span-2 bg-slate-50 dark:bg-slate-900/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Status Ketersediaan Menu</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">Aktifkan agar menu dapat dipilih dan dijual oleh kasir di POS</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-600"></div>
                                    </label>
                                </div>

                                {{-- KALKULATOR ESTIMASI MARGIN PROFIT --}}
                                @php
                                    $pPrice = is_numeric($price) ? (float)$price : 0;
                                    $pHpp = is_numeric($harga_beli) ? (float)$harga_beli : 0;
                                    $pProfit = max(0, $pPrice - $pHpp);
                                    $pMargin = $pPrice > 0 ? ($pProfit / $pPrice) * 100 : 0;
                                @endphp
                                <div class="sm:col-span-2 bg-amber-50 dark:bg-slate-900/80 p-3.5 rounded-xl border border-amber-200 dark:border-slate-700 flex justify-between items-center text-xs">
                                    <div>
                                        <span class="text-slate-600 dark:text-slate-400 font-semibold block">Estimasi Profit per Cup:</span>
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">Rp {{ number_format($pProfit, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-slate-600 dark:text-slate-400 font-semibold block">Profit Margin (%):</span>
                                        <span class="font-extrabold text-sm {{ $pMargin >= 50 ? 'text-emerald-600' : ($pMargin >= 35 ? 'text-amber-600' : 'text-rose-600') }}">
                                            {{ number_format($pMargin, 1) }}%
                                        </span>
                                    </div>
                                </div>

                                {{-- Barcode --}}
                                <div class="sm:col-span-2">
                                    <label for="barcode" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Kode Barcode (Opsional)</label>
                                    <input type="text" id="barcode" wire:model="barcode" class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="Scan barcode produk di sini...">
                                </div>

                                {{-- Deskripsi --}}
                                <div class="sm:col-span-2">
                                    <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Deskripsi / Komposisi</label>
                                    <textarea id="description" wire:model="description" rows="2" class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400" placeholder="Deskripsi singkat menu cafe..."></textarea>
                                </div>

                                {{-- Gambar Produk --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Foto Produk</label>
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                        @if ($image)
                                            <img src="{{ $image->temporaryUrl() }}" class="w-20 h-20 rounded-lg object-cover border border-slate-200 dark:border-slate-600 shrink-0">
                                        @elseif($oldImage)
                                            <img src="{{ Storage::url($oldImage) }}" class="w-20 h-20 rounded-lg object-cover border border-slate-200 dark:border-slate-600 shrink-0">
                                        @endif
                                        <div class="flex-1 w-full">
                                            <input type="file" wire:model="image" accept="image/*" class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 cursor-pointer">
                                            @error('image') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                            <p class="mt-1 text-[11px] text-slate-400">Format PNG, JPG, WebP maksimal 2MB</p>
                                        </div>
                                    </div>
                                    <div wire:loading wire:target="image" class="mt-1.5 text-xs text-amber-600 dark:text-amber-400 font-medium">Mengunggah gambar...</div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-slate-50 dark:bg-slate-700/50 px-5 sm:px-6 py-3.5 flex justify-end space-x-2.5 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="closeModal" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 transition-colors font-medium text-xs">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors font-semibold text-xs shadow-sm active:scale-95 flex items-center justify-center min-w-[100px]" wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Menu' }}</span>
                                <span wire:loading>
                                    <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Arsipkan Menu?',
            html: `<div class="text-left text-xs space-y-2">
                    <p class="font-semibold text-sm text-slate-800 dark:text-slate-200">Menu: <span class="text-rose-600">${name}</span></p>
                    <p class="text-slate-600 dark:text-slate-400">Menu akan disembunyikan dari POS Kasir dan diarsipkan (Soft Delete). <b>Semua riwayat transaksi & omset masa lalu tetap 100% aman tersimpan.</b></p>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Arsipkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', id);
            }
        });
    }

    function confirmRestore(id, name) {
        Swal.fire({
            title: 'Pulihkan Menu?',
            text: "Menu '" + name + "' akan dikembalikan ke daftar menu aktif.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Pulihkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('restore', id);
            }
        });
    }

    function confirmForceDelete(id, name) {
        Swal.fire({
            title: 'Hapus Permanen?',
            text: "Menu '" + name + "' akan dihapus selamanya dari database. Pastikan menu ini belum pernah memiliki riwayat transaksi.",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Hapus Permanen',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('forceDelete', id);
            }
        });
    }
</script>
@endpush