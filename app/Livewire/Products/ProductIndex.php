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

#[Layout('components.layouts.app')]
#[Title('Produk Menu - Cafe Noli')]
class ProductIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $categoryFilter = '';
    public $productId;
    public $name = '';
    public $sku = '';
    public $category_id = '';
    public $description = '';
    public $price = '';
    public $stock = 999; // Default 999 for Cafe Unlimited Mode
    public $image;
    public $oldImage;
    public $barcode = '';
    public $harga_beli = 0;
    public $isEdit = false;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|min:3',
        'sku' => 'required|unique:products,sku',
        'barcode' => 'nullable|string|unique:products,barcode',
        'category_id' => 'required|exists:categories,id',
        'price' => 'required|numeric|min:0',
        'harga_beli' => 'required|numeric|min:0',
        'description' => 'nullable',
        'image' => 'nullable|image|max:2048'
    ];

    protected $messages = [
        'name.required' => 'Nama menu wajib diisi',
        'sku.required' => 'SKU wajib diisi',
        'sku.unique' => 'SKU sudah digunakan',
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

        // Cari nomor urut terbesar dari SKU dengan prefix tersebut
        $existingSkus = Product::where('sku', 'LIKE', $prefix . '%')->pluck('sku');
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

        while (Product::where('sku', $newSku)->exists()) {
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
        $this->stock = 999;
        $this->resetValidation();
    }

    public function save()
    {
        if (empty(trim($this->sku))) {
            $this->sku = $this->generateSku($this->category_id);
        }

        if ($this->isEdit) {
            $this->rules['sku'] = 'required|unique:products,sku,' . $this->productId;
            $this->rules['barcode'] = 'nullable|string|unique:products,barcode,' . $this->productId;
        }

        $this->validate();
        $barcodeValue = empty(trim($this->barcode)) ? null : $this->barcode;

        $data = [
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $barcodeValue,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'price' => $this->price,
            'harga_beli' => $this->harga_beli,
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
            $data['stock'] = 999;

            $product = Product::create($data);
            session()->flash('message', 'Menu cafe berhasil ditambahkan');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        $product = Product::find($id);
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->harga_beli = $product->harga_beli;
        $this->stock = $product->stock;
        $this->oldImage = $product->image;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        $product = Product::find($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        session()->flash('message', 'Menu cafe berhasil dihapus');
    }

    public function render()
    {
        $query = Product::with('category')
            ->where('name', 'like', '%' . $this->search . '%');

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('livewire.products.product-index', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
}
