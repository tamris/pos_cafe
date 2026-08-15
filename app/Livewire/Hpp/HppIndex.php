<?php

namespace App\Livewire\Hpp;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Manajemen HPP & Margin - Cafe Noli')]
class HppIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    
    // Quick Edit State
    public $showEditModal = false;
    public $editingProduct = null;
    public $harga_beli = 0;
    public $price = 0;

    public function notify($type, $message)
    {
        $this->dispatch('show-toast', type: $type, message: $message);
    }

    public function openEditModal($productId)
    {
        $this->editingProduct = Product::find($productId);
        if (!$this->editingProduct) return;

        $this->harga_beli = $this->editingProduct->harga_beli;
        $this->price = $this->editingProduct->price;
        $this->showEditModal = true;
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->editingProduct = null;
        $this->harga_beli = 0;
        $this->price = 0;
    }

    public function updateHpp()
    {
        $this->validate([
            'harga_beli' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ], [
            'harga_beli.required' => 'Harga modal (HPP) wajib diisi.',
            'price.required' => 'Harga jual wajib diisi.'
        ]);

        if ($this->editingProduct) {
            $this->editingProduct->update([
                'harga_beli' => (float) $this->harga_beli,
                'price' => (float) $this->price
            ]);

            $this->notify('success', 'HPP & Harga Jual ' . $this->editingProduct->name . ' berhasil diperbarui!');
            $this->closeModal();
        }
    }

    public function render()
    {
        $query = Product::query()->with('category');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', $this->search)
                    ->orWhere('barcode', $this->search);
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $allProducts = Product::all();
        $totalMenuCount = $allProducts->count();
        
        $totalMarginPercent = 0;
        $lowMarginCount = 0;
        $totalProfitSum = 0;

        foreach ($allProducts as $p) {
            $hpp = (float) $p->harga_beli;
            $jual = (float) $p->price;
            $profit = max(0, $jual - $hpp);
            $margin = $jual > 0 ? ($profit / $jual) * 100 : 0;

            $totalMarginPercent += $margin;
            $totalProfitSum += $profit;

            if ($margin < 35 && $jual > 0) {
                $lowMarginCount++;
            }
        }

        $avgMargin = $totalMenuCount > 0 ? ($totalMarginPercent / $totalMenuCount) : 0;
        $avgProfitPerItem = $totalMenuCount > 0 ? ($totalProfitSum / $totalMenuCount) : 0;

        $products = $query->orderBy('name', 'asc')->paginate(12);
        $categories = Category::all();

        return view('livewire.hpp.hpp-index', [
            'products' => $products,
            'categories' => $categories,
            'totalMenuCount' => $totalMenuCount,
            'avgMargin' => $avgMargin,
            'lowMarginCount' => $lowMarginCount,
            'avgProfitPerItem' => $avgProfitPerItem
        ]);
    }
}
