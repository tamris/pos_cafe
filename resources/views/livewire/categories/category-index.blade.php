<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="if (window.innerWidth >= 1280) { sidebarOpen = true } else { sidebarOpen = false }">
    
    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Kategori',
            'subtitle' => 'Kelola kategori menu & produk cafe',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- Flash Messages --}}
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

            @if (session()->has('error'))
                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-4 animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-rose-800 dark:text-rose-300 font-medium text-xs sm:text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Main Table Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Header Filter & Action Box --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Daftar Kategori</h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola kategori pengelompokan menu kasir cafe</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                            {{-- Search Input --}}
                            <div class="relative w-full sm:w-60">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Cari kategori..."
                                    class="w-full pl-10 pr-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            {{-- Tombol Tambah Kategori (Admin Only) --}}
                            @if(auth()->user()->role === 'admin')
                            <button wire:click="openModal"
                                class="bg-slate-900 dark:bg-blue-600 text-white px-4 py-2 sm:py-2.5 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2 font-semibold text-xs sm:text-sm w-full sm:w-auto shadow-sm active:scale-95 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Tambah Kategori</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Status Filter Tabs (Semua, Aktif, Non-aktif, Arsip) --}}
                    <div class="flex items-center gap-2 mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/60 overflow-x-auto scrollbar-none text-xs">
                        <button wire:click="setStatusFilter('all')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                            <span>Semua Kategori</span>
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
                            <span>Arsip / Tong Sampah</span>
                            @if($countTrashed > 0)
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'trashed' ? 'bg-white/20 text-white' : 'bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200' }}">{{ $countTrashed }}</span>
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Table Area --}}
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[620px]">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-5 sm:px-6 py-3.5 w-16">No</th>
                                <th class="px-5 sm:px-6 py-3.5">Nama Kategori</th>
                                <th class="px-5 sm:px-6 py-3.5">Deskripsi</th>
                                <th class="px-5 sm:px-6 py-3.5">Jumlah Produk</th>
                                <th class="px-5 sm:px-6 py-3.5 text-center">Status POS</th>
                                <th class="px-5 sm:px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                            @forelse($categories as $index => $category)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors {{ $category->trashed() ? 'opacity-70 bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                                    <td class="px-5 sm:px-6 py-3.5 text-slate-500 dark:text-slate-400 font-mono">
                                        {{ $categories->firstItem() + $index }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $category->name }}</div>
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 text-slate-500 dark:text-slate-400">
                                        <div class="truncate max-w-[260px]">{{ $category->description ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $category->products_count > 0 ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                                            {{ $category->products_count }} Produk
                                        </span>
                                    </td>
                                    
                                    {{-- Status POS (Interactive Toggle Button) --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-center">
                                        @if($category->trashed())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">
                                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span>Diarsipkan</span>
                                            </span>
                                        @else
                                            <button wire:click="toggleStatus({{ $category->id }})" title="Klik untuk mengubah ketersediaan kategori di kasir POS"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer select-none active:scale-95 {{ $category->is_active ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/40' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 hover:bg-amber-100 dark:hover:bg-amber-900/40' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $category->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
                                                <span>{{ $category->is_active ? 'Aktif di POS' : 'Non-Aktif' }}</span>
                                            </button>
                                        @endif
                                    </td>

                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-right space-x-1.5 shrink-0">
                                        @if($category->trashed())
                                            {{-- Pulihkan (Restore) --}}
                                            <button onclick="confirmRestoreCategory({{ $category->id }}, '{{ $category->name }}')"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-emerald-300 dark:border-emerald-800 text-xs font-medium rounded-lg text-emerald-700 dark:text-emerald-400 bg-white dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                Pulihkan
                                            </button>

                                            {{-- Hapus Permanen --}}
                                            <button onclick="confirmForceDeleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-rose-300 dark:border-rose-800 text-xs font-medium rounded-lg text-rose-700 dark:text-rose-400 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus Permanen
                                            </button>
                                        @else
                                            <button wire:click="edit({{ $category->id }})"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 text-xs font-medium rounded-lg text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            
                                            <button onclick="confirmDeleteCategory({{ $category->id }}, '{{ $category->name }}', {{ $category->products_count }})"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-rose-300 dark:border-rose-800/60 text-xs font-medium rounded-lg text-rose-700 dark:text-rose-400 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors active:scale-95"
                                                title="Arsipkan Kategori">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Hapus
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium text-xs sm:text-sm">
                                                @if($statusFilter === 'trashed')
                                                    Arsip kosong. Tidak ada kategori yang diarsipkan.
                                                @elseif($statusFilter === 'inactive')
                                                    Tidak ada kategori yang berstatus non-aktif.
                                                @else
                                                    Tidak ada kategori ditemukan
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
                    {{ $categories->links() }}
                </div>
            </div>
        </main>
    </div>

    {{-- MODAL FORM KATEGORI --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeModal"></div>

                <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full my-8 border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="save">
                        
                        {{-- Modal Header --}}
                        <div class="bg-slate-900 dark:bg-slate-700 text-white px-5 sm:px-6 py-4 border-b border-slate-800 dark:border-slate-600 flex items-center justify-between">
                            <h3 class="text-base font-bold">
                                {{ $isEdit ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                            </h3>
                            <button type="button" wire:click="closeModal" class="text-white hover:opacity-80 transition-opacity p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="p-5 sm:p-6 space-y-4">
                            <div>
                                <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                    Nama Kategori <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="name" wire:model="name"
                                    class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white @error('name') border-rose-500 @enderror"
                                    placeholder="Contoh: Coffee, Pastry, Non-Coffee">
                                @error('name')
                                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">
                                    Deskripsi (Opsional)
                                </label>
                                <textarea id="description" wire:model="description" rows="3"
                                    class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400"
                                    placeholder="Penjelasan singkat mengenai kategori menu..."></textarea>
                            </div>

                            {{-- Status Aktif Checkbox --}}
                            <div class="pt-2">
                                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                    <input type="checkbox" wire:model="is_active"
                                        class="w-4 h-4 text-emerald-600 border-slate-300 dark:border-slate-600 rounded-sm focus:ring-emerald-500 bg-white dark:bg-slate-900">
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Aktifkan Kategori (Tersedia & Muncul di Kasir POS)
                                    </span>
                                </label>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-slate-50 dark:bg-slate-700/50 px-5 sm:px-6 py-3.5 flex justify-end space-x-2.5 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 transition-colors font-medium text-xs">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors font-semibold text-xs shadow-sm active:scale-95 flex items-center justify-center min-w-[100px]"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Kategori' }}</span>
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
    function confirmDeleteCategory(id, name, productsCount) {
        if (productsCount > 0) {
            Swal.fire({
                title: 'Tidak Dapat Mengarsipkan',
                text: "Kategori '" + name + "' masih memiliki " + productsCount + " produk aktif. Pindahkan atau nonaktifkan produk terlebih dahulu.",
                icon: 'warning',
                confirmButtonColor: '#0f172a',
                confirmButtonText: 'Mengerti',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
            });
            return;
        }

        Swal.fire({
            title: 'Arsipkan Kategori?',
            text: "Kategori '" + name + "' akan dipindahkan ke Arsip / Tong Sampah.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569', 
            confirmButtonText: 'Ya, Arsipkan!',
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

    function confirmRestoreCategory(id, name) {
        Swal.fire({
            title: 'Pulihkan Kategori?',
            text: "Kategori '" + name + "' akan dipulihkan dan aktif kembali di sistem.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Pulihkan!',
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

    function confirmForceDeleteCategory(id, name) {
        Swal.fire({
            title: 'Hapus Permanen?',
            text: "Kategori '" + name + "' akan dihapus selamanya dari database dan tidak dapat dipulihkan!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Hapus Permanen!',
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