<?php

namespace App\Livewire\StockManagement;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\TransactionDetail;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Penjualan Menu Hari Ini - Cafe Noli')]
class StockIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $showHistoryModal = false;
    public $selectedProduct = null;
    public $itemHistory = [];

    public function openHistoryModal($productId)
    {
        $this->selectedProduct = Product::find($productId);
        if (!$this->selectedProduct) return;

        // Ambil riwayat detail transaksi produk hari ini
        $this->itemHistory = TransactionDetail::with('transaction')
            ->where('product_id', $productId)
            ->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', today());
            })
            ->latest()
            ->get();

        $this->showHistoryModal = true;
    }

    public function closeModal()
    {
        $this->showHistoryModal = false;
        $this->selectedProduct = null;
        $this->itemHistory = [];
    }

    public function render()
    {
        // 1. Hitung Ringkasan KPI Penjualan Hari Ini
        $todayDetails = TransactionDetail::whereHas('transaction', function ($q) {
            $q->whereDate('created_at', today());
        });

        $totalCupsToday = (int) $todayDetails->sum('quantity');
        $totalRevenueToday = (float) $todayDetails->sum('subtotal');

        // Top seller hari ini
        $topSellerItem = TransactionDetail::whereHas('transaction', function ($q) {
            $q->whereDate('created_at', today());
        })
        ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
        ->groupBy('product_id')
        ->orderByDesc('total_qty')
        ->with('product')
        ->first();

        $topSellerName = $topSellerItem->product->name ?? '-';
        $topSellerQty = $topSellerItem->total_qty ?? 0;

        $uniqueMenuSoldToday = TransactionDetail::whereHas('transaction', function ($q) {
            $q->whereDate('created_at', today());
        })->distinct('product_id')->count('product_id');

        // 2. Query Daftar Produk dengan Agregat Penjualan Hari Ini & All Time
        $query = Product::query()
            ->with('category')
            ->withSum(['transactionDetails as sold_today' => function ($q) {
                $q->whereHas('transaction', function ($tr) {
                    $tr->whereDate('created_at', today());
                });
            }], 'quantity')
            ->withSum(['transactionDetails as revenue_today' => function ($q) {
                $q->whereHas('transaction', function ($tr) {
                    $tr->whereDate('created_at', today());
                });
            }], 'subtotal')
            ->withSum('transactionDetails as sold_all_time', 'quantity');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', $this->search)
                    ->orWhere('sku', $this->search);
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $products = $query->orderByDesc('sold_today')
            ->orderBy('name', 'asc')
            ->paginate(12);

        $categories = Category::all();

        return view('livewire.stock-management.stock-index', [
            'products' => $products,
            'categories' => $categories,
            'totalCupsToday' => $totalCupsToday,
            'totalRevenueToday' => $totalRevenueToday,
            'topSellerName' => $topSellerName,
            'topSellerQty' => $topSellerQty,
            'uniqueMenuSoldToday' => $uniqueMenuSoldToday
        ]);
    }
}
