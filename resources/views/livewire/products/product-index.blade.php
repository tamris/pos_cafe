<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', ['title' => 'Produk', 'subtitle' => 'Kelola semua produk'])

        <main class="p-6">
            
            @if (session()->has('message'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 animate-fade-in-down">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-800 dark:text-green-300 font-medium text-sm">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Daftar Produk</h2>
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
                            
                            {{-- Filter Kategori --}}
                            <select wire:model.live="categoryFilter" class="px-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-slate-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white w-full sm:w-auto cursor-pointer">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>

                            {{-- Search --}}
                            <div class="relative w-full sm:w-auto">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Cari produk..."
                                    class="pl-10 pr-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-slate-500 focus:border-transparent w-full sm:w-64 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            {{-- Tombol Tambah --}}
                            <button wire:click="openModal"
                                class="bg-slate-900 dark:bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors flex items-center space-x-2 whitespace-nowrap font-medium w-full sm:w-auto justify-center shadow-lg shadow-slate-200 dark:shadow-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Tambah Produk</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($products as $product)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            @if ($product->image)
                                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200 dark:border-slate-600">
                                            @else
                                                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center border border-slate-200 dark:border-slate-600">
                                                    <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $product->name }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ Str::limit($product->description, 30) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-slate-600 dark:text-slate-300 font-mono">{{ $product->sku }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                            {{ $product->category->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <button wire:click="edit({{ $product->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-slate-300 dark:border-slate-600 text-xs font-medium rounded-lg text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit
                                        </button>
                                        
                                        {{-- TOMBOL HAPUS DENGAN SWEETALERT --}}
                                        <button onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')"
                                            class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-800 text-xs font-medium rounded-lg text-red-700 dark:text-red-400 bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada produk ditemukan</p>
                                        <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Mulai dengan menambahkan produk baru</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    {{ $products->links() }}
                </div>
            </div>
        </main>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-50" wire:click="closeModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white dark:bg-slate-800 px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                    {{ $isEdit ? 'Edit Produk' : 'Tambah Produk' }}
                                </h3>
                                <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 px-6 py-4 space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Produk <span class="text-red-500">*</span></label>
                                    <input type="text" id="name" wire:model="name" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="Masukkan nama produk">
                                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="sku" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">SKU <span class="text-red-500">*</span></label>
                                    <input type="text" id="sku" wire:model="sku" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="PRD001">
                                    @error('sku') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kategori <span class="text-red-500">*</span></label>
                                    <select id="category_id" wire:model="category_id" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="price" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Harga Jual <span class="text-red-500">*</span></label>
                                    <input type="number" id="price" wire:model.live="price" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="15000">
                                    @error('price') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="harga_beli" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Harga Beli (Modal / HPP) <span class="text-red-500">*</span></label>
                                    <input type="number" id="harga_beli" wire:model.live="harga_beli" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="12000">
                                    @error('harga_beli') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                {{-- KALKULATOR PROFIT LIVE PREVIEW --}}
                                @php
                                    $pPrice = is_numeric($price) ? (float)$price : 0;
                                    $pHpp = is_numeric($harga_beli) ? (float)$harga_beli : 0;
                                    $pProfit = max(0, $pPrice - $pHpp);
                                    $pMargin = $pPrice > 0 ? ($pProfit / $pPrice) * 100 : 0;
                                @endphp
                                <div class="md:col-span-2 bg-amber-50 dark:bg-slate-900 p-3.5 rounded-xl border border-amber-200 dark:border-slate-700 flex justify-between items-center text-xs">
                                    <div>
                                        <span class="text-slate-600 dark:text-slate-400 font-semibold block">Estimasi Profit per Cup:</span>
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">Rp {{ number_format($pProfit, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-slate-600 dark:text-slate-400 font-semibold block">Profit Margin (%):</span>
                                        <span class="font-extrabold text-sm {{ $pMargin >= 50 ? 'text-emerald-600' : ($pMargin >= 35 ? 'text-amber-600' : 'text-red-600') }}">
                                            {{ number_format($pMargin, 1) }}%
                                        </span>
                                    </div>
                                </div>

                               

                                <div class="md:col-span-2">
                                    <label for="barcode" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Barcode</label>
                                    <input type="text" id="barcode" wire:model="barcode" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="Scan barcode di sini...">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Deskripsi</label>
                                    <textarea id="description" wire:model="description" rows="3" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="Masukkan deskripsi produk (opsional)"></textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Gambar Produk</label>
                                    <div class="flex items-center space-x-4">
                                        @if ($image)
                                            <img src="{{ $image->temporaryUrl() }}" class="w-24 h-24 rounded-lg object-cover border border-slate-200 dark:border-slate-600">
                                        @elseif($oldImage)
                                            <img src="{{ Storage::url($oldImage) }}" class="w-24 h-24 rounded-lg object-cover border border-slate-200 dark:border-slate-600">
                                        @endif
                                        <div class="flex-1">
                                            <input type="file" wire:model="image" accept="image/*" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-slate-600 cursor-pointer">
                                            @error('image') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">PNG, JPG maksimal 2MB</p>
                                        </div>
                                    </div>
                                    <div wire:loading wire:target="image" class="mt-2 text-sm text-slate-500 dark:text-slate-400">Uploading...</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex justify-end space-x-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="closeModal" class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2.5 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors font-medium" wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Update' : 'Simpan' }}</span>
                                <span wire:loading>
                                    <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            title: 'Hapus Produk?',
            text: "Anda akan menghapus produk: " + name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#1e293b', // Di dark mode ini akan menyesuaikan background otomatis atau bisa dikustom
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            // Opsi tambahan buat SweetAlert Dark Mode
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', id);
            }
        });
    }
</script>
@endpush