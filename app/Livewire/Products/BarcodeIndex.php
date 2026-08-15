<?php

namespace App\Livewire\Products;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Cetak Barcode - Toko Kendali')]
class BarcodeIndex extends Component
{
    public $search = '';
    public $searchResults = [];

    // Daftar produk yang mau dicetak: [['id' => 1, 'name' => '...', 'qty' => 10]]
    public $printQueue = [];

    public function updatedSearch()
    {
        if (strlen($this->search) > 1) {

            // 1. Ambil daftar ID produk yang sudah ada di antrian (Sebelah Kanan)
            $idsInQueue = array_column($this->printQueue, 'id');

            // 2. Query Database dengan pengecualian (whereNotIn)
            $this->searchResults = Product::where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            })
                ->whereNotIn('id', $idsInQueue) // <--- INI KUNCINYA: Jangan tampilkan ID yang sudah dipilih
                ->take(5)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function addToQueue($productId)
    {
        $product = Product::find($productId);

        // Cek apakah sudah ada di antrian
        foreach ($this->printQueue as $item) {
            if ($item['id'] == $productId) {
                return; // Kalau udah ada, gak usah double
            }
        }

        // Masukkan ke antrian
        $this->printQueue[] = [
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode ?? $product->sku, // Pakai SKU kalau barcode kosong
            'price' => $product->price,
            'quantity' => 1 // Default cetak 1 label
        ];

        $this->search = '';
        $this->searchResults = [];
    }

    public function removeFromQueue($index)
    {
        unset($this->printQueue[$index]);
        $this->printQueue = array_values($this->printQueue);
    }

    public function updateQuantity($index, $qty)
    {
        if ($qty > 0) {
            $this->printQueue[$index]['quantity'] = $qty;
        }
    }

    public function render()
    {
        return view('livewire.products.barcode-index');
    }
}
