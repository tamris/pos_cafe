<?php

namespace App\Livewire\Products;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.app')]
#[Title('Produk Menu - POS Cafe')]
class ProductIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = 'all'; // 'all', 'active', 'inactive', 'trashed'
    public $productId;
    public $name = '';
    public $sku = '';
    public $category_id = '';
    public $description = '';
    public $price = '';
    public $image;
    public $oldImage;
    public $barcode = '';
    public $harga_beli = '';
    public $is_active = true;
    public $isEdit = false;
    public $showModal = false;

    protected $messages = [
        'name.required' => 'Nama menu wajib diisi',
        'sku.required' => 'SKU wajib diisi',
        'sku.unique' => 'SKU sudah digunakan pada menu aktif',
        'category_id.required' => 'Kategori wajib dipilih',
        'barcode.unique' => 'Barcode ini sudah terdaftar di menu lain.',
        'price.required' => 'Harga jual wajib diisi',
        'price.numeric' => 'Harga jual harus berupa angka',
        'harga_beli.required' => 'Harga beli (modal) wajib diisi',
        'harga_beli.numeric' => 'Harga beli harus berupa angka',
        'image.image' => 'File harus berupa gambar',
        'image.max' => 'Ukuran gambar maksimal 2MB',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function setStatusFilter($filter)
    {
        $this->statusFilter = $filter;
        $this->resetPage();
    }

    public function updatedCategoryId($value)
    {
        if (!$this->isEdit) {
            $this->sku = $this->generateSku($value);
        }
    }

    public function generateSku($categoryId = null)
    {
        $prefix = 'PRD';

        $catId = $categoryId ?: $this->category_id;
        if ($catId) {
            $category = Category::find($catId);
            if ($category) {
                $catName = strtolower($category->name);
                if (str_contains($catName, 'coffee') && !str_contains($catName, 'non-coffee')) {
                    $prefix = 'COF';
                } elseif (str_contains($catName, 'non-coffee') || str_contains($catName, 'tea')) {
                    $prefix = 'NCF';
                } elseif (str_contains($catName, 'pastry') || str_contains($catName, 'bakery')) {
                    $prefix = 'PST';
                } elseif (str_contains($catName, 'food') || str_contains($catName, 'main course')) {
                    $prefix = 'FOD';
                } elseif (str_contains($catName, 'snack')) {
                    $prefix = 'SNK';
                } else {
                    $clean = preg_replace('/[^A-Za-z0-9]/', '', $category->name);
                    $prefix = strtoupper(substr($clean, 0, 3));
                    if (strlen($prefix) < 3) {
                        $prefix = 'PRD';
                    }
                }
            }
        }

        // Cari nomor urut terbesar dari SKU dengan prefix tersebut (termasuk yang soft deleted untuk menghindari duplikasi)
        $existingSkus = Product::withTrashed()->where('sku', 'LIKE', $prefix . '%')->pluck('sku');
        $maxNum = 0;
        foreach ($existingSkus as $existingSku) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $existingSku, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        $newSku = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        while (Product::withTrashed()->where('sku', $newSku)->exists()) {
            $nextNum++;
            $newSku = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        return $newSku;
    }

    public function regenerateSku()
    {
        $this->sku = $this->generateSku($this->category_id);
    }

    public function openModal()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat menambah menu baru.');
            return;
        }
        $this->resetForm();
        $this->sku = $this->generateSku();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'sku', 'category_id', 'description', 'price', 'harga_beli', 'image', 'oldImage', 'productId', 'isEdit', 'barcode']);
        $this->price = '';
        $this->harga_beli = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function toggleStatus($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->is_active = !$product->is_active;
            $product->save();
            $statusLabel = $product->is_active ? 'Diaktifkan (Tersedia di POS)' : 'Dinonaktifkan (Disembunyikan dari POS)';
            session()->flash('message', "Menu '{$product->name}' berhasil {$statusLabel}.");
        }
    }

    public function save()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak. Hanya Administrator yang dapat menyimpan perubahan menu.');
            return;
        }

        if (empty(trim($this->sku))) {
            $this->sku = $this->generateSku($this->category_id);
        }

        // Parse format numerik
        if ($this->price !== '' && !is_numeric($this->price)) {
            $this->price = (float) preg_replace('/[^\d.]/', '', (string)$this->price);
        }
        if ($this->harga_beli !== '' && !is_numeric($this->harga_beli)) {
            $this->harga_beli = (float) preg_replace('/[^\d.]/', '', (string)$this->harga_beli);
        }

        $skuRule = Rule::unique('products', 'sku')->whereNull('deleted_at');
        $barcodeRule = Rule::unique('products', 'barcode')->whereNull('deleted_at');

        if ($this->isEdit && $this->productId) {
            $skuRule = $skuRule->ignore($this->productId);
            $barcodeRule = $barcodeRule->ignore($this->productId);
        }

        $this->validate([
            'name' => 'required|min:3',
            'sku' => ['required', $skuRule],
            'barcode' => ['nullable', 'string', $barcodeRule],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ], $this->messages);

        $barcodeValue = empty(trim($this->barcode)) ? null : $this->barcode;

        $data = [
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $barcodeValue,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'price' => $this->price,
            'harga_beli' => $this->harga_beli,
            'is_active' => (bool) $this->is_active,
        ];

        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
            $data['image'] = $imagePath;
        }

        if ($this->isEdit) {
            if ($this->image && $this->oldImage) {
                Storage::disk('public')->delete($this->oldImage);
            }

            $product = Product::find($this->productId);
            $product->update($data);
            session()->flash('message', 'Menu cafe berhasil diupdate');
        } else {
            $product = Product::create($data);
            session()->flash('message', 'Menu cafe berhasil ditambahkan');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat mengedit data menu.');
            return;
        }

        $product = Product::withTrashed()->find($id);
        if (!$product) return;

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->price = (int) $product->price;
        $this->harga_beli = (int) $product->harga_beli;
        $this->is_active = (bool) $product->is_active;
        $this->oldImage = $product->image;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat menghapus menu.');
            return;
        }

        $product = Product::find($id);
        if (!$product) return;

        $hasTransactions = $product->transactionDetails()->exists();
        
        // Soft delete menu
        $product->delete();

        if ($hasTransactions) {
            session()->flash('message', "Menu '{$product->name}' berhasil diarsipkan (Soft Delete). Riwayat transaksi dan omset masa lalu tetap 100% aman.");
        } else {
            session()->flash('message', "Menu '{$product->name}' berhasil dipindahkan ke Tong Sampah / Arsip.");
        }
    }

    public function restore($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat memulihkan menu.');
            return;
        }

        $product = Product::onlyTrashed()->find($id);
        if ($product) {
            $product->restore();
            session()->flash('message', "Menu '{$product->name}' berhasil dipulihkan dari arsip.");
        }
    }

    public function forceDelete($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat menghapus menu permanen.');
            return;
        }

        $product = Product::onlyTrashed()->find($id);
        if (!$product) return;

        if ($product->transactionDetails()->exists()) {
            session()->flash('error', "Menu '{$product->name}' tidak dapat dihapus permanen karena memiliki riwayat transaksi.");
            return;
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->forceDelete();
        session()->flash('message', "Menu '{$product->name}' berhasil dihapus permanen.");
    }

    public function render()
    {
        // Counts for tab filters
        $countAll = Product::count();
        $countActive = Product::where('is_active', true)->count();
        $countInactive = Product::where('is_active', false)->count();
        $countTrashed = Product::onlyTrashed()->count();

        // Build query based on status filter
        if ($this->statusFilter === 'trashed') {
            $query = Product::onlyTrashed()->with('category');
        } elseif ($this->statusFilter === 'active') {
            $query = Product::with('category')->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query = Product::with('category')->where('is_active', false);
        } else {
            $query = Product::with('category');
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('livewire.products.product-index', [
            'products' => $products,
            'categories' => $categories,
            'countAll' => $countAll,
            'countActive' => $countActive,
            'countInactive' => $countInactive,
            'countTrashed' => $countTrashed,
        ]);
    }
}
