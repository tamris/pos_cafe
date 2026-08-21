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
#[Title('Penjualan Menu Hari Ini - POS Cafe')]
class StockIndex extends Component
{
    use WithPagination;

    public $categoryFilter = '';
    public $filterPeriod = 'this_month'; // Default to this month for best representation
    public $showHistoryModal = false;
    public $selectedProduct = null;
    public $itemHistory = [];

    public function updatedFilterPeriod()
    {
        $this->resetPage();
    }

    public function openHistoryModal($productId)
    {
        $this->selectedProduct = Product::find($productId);
        if (!$this->selectedProduct) return;

        list($dateFrom, $dateTo) = $this->getDateRange();

        $this->itemHistory = TransactionDetail::with('transaction')
            ->where('product_id', $productId)
            ->whereHas('transaction', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo])
                  ->where('status', 'completed');
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

    private function getDateRange()
    {
        $now = now();
        switch ($this->filterPeriod) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'yesterday':
                return [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()];
            case 'this_week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'this_month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'last_month':
                return [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()];
            case 'all_time':
            default:
                return [now()->subYears(10), now()->addYears(10)];
        }
    }

    private function getPreviousDateRange($currentStart, $currentEnd)
    {
        $diffInDays = max(1, $currentStart->diffInDays($currentEnd) + 1);
        $prevEnd = $currentStart->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($diffInDays - 1)->startOfDay();
        return [$prevStart, $prevEnd];
    }

    public function render()
    {
        list($dateFrom, $dateTo) = $this->getDateRange();
        list($prevFrom, $prevTo) = $this->getPreviousDateRange($dateFrom, $dateTo);

        // 1. Hitung Ringkasan KPI Penjualan
        $periodDetails = TransactionDetail::whereHas('transaction', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom, $dateTo])
              ->where('status', 'completed');
        });

        $totalCups = (int) (clone $periodDetails)->sum('quantity');
        $totalRevenue = (float) (clone $periodDetails)->sum('subtotal');

        $topSellerItem = (clone $periodDetails)
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->first();

        $topSellerName = $topSellerItem->product->name ?? '-';
        $topSellerQty = $topSellerItem->total_qty ?? 0;

        $uniqueMenuSold = (clone $periodDetails)->distinct('product_id')->count('product_id');

        // Kategori Terlaris
        $topCategoryItem = Category::withSum(['transactionDetails as category_qty' => function ($q) use ($dateFrom, $dateTo) {
            $q->whereHas('transaction', function ($tr) use ($dateFrom, $dateTo) {
                $tr->whereBetween('created_at', [$dateFrom, $dateTo])
                   ->where('status', 'completed');
            });
        }], 'quantity')
        ->orderByDesc('category_qty')
        ->first();

        $topCategoryName = ($topCategoryItem && $topCategoryItem->category_qty > 0) ? $topCategoryItem->name : '-';
        $topCategoryQty = $topCategoryItem->category_qty ?? 0;

        // Komparasi Data Sebelumnya
        $prevDetails = TransactionDetail::whereHas('transaction', function ($q) use ($prevFrom, $prevTo) {
            $q->whereBetween('created_at', [$prevFrom, $prevTo])
              ->where('status', 'completed');
        });

        $totalCupsPrev = (int) (clone $prevDetails)->sum('quantity');
        $totalRevenuePrev = (float) (clone $prevDetails)->sum('subtotal');
        $uniqueMenuSoldPrev = (clone $prevDetails)->distinct('product_id')->count('product_id');

        $topCategoryPrev = $topCategoryItem ? Category::withSum(['transactionDetails as category_qty' => function ($q) use ($prevFrom, $prevTo) {
            $q->whereHas('transaction', function ($tr) use ($prevFrom, $prevTo) {
                $tr->whereBetween('created_at', [$prevFrom, $prevTo])
                   ->where('status', 'completed');
            });
        }], 'quantity')->where('id', $topCategoryItem->id)->first() : null;
        $topCategoryQtyPrev = $topCategoryPrev->category_qty ?? 0;

        $cupsGrowth = $this->calculateGrowth($totalCups, $totalCupsPrev);
        $revenueGrowth = $this->calculateGrowth($totalRevenue, $totalRevenuePrev);
        $uniqueMenuGrowth = $this->calculateGrowth($uniqueMenuSold, $uniqueMenuSoldPrev);
        $categoryGrowth = $this->calculateGrowth($topCategoryQty, $topCategoryQtyPrev);

        // 2. Query Daftar Produk dengan Agregat Penjualan
        $query = Product::query()
            ->with('category')
            ->withSum(['transactionDetails as sold_period' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereHas('transaction', function ($tr) use ($dateFrom, $dateTo) {
                    $tr->whereBetween('created_at', [$dateFrom, $dateTo])
                       ->where('status', 'completed');
                });
            }], 'quantity')
            ->withSum(['transactionDetails as revenue_period' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereHas('transaction', function ($tr) use ($dateFrom, $dateTo) {
                    $tr->whereBetween('created_at', [$dateFrom, $dateTo])
                       ->where('status', 'completed');
                });
            }], 'subtotal')
            ->withSum('transactionDetails as sold_all_time', 'quantity');

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $products = $query->orderByDesc('sold_period')
            ->orderBy('name', 'asc')
            ->paginate(12);

        $categories = Category::all();

        return view('livewire.stock-management.stock-index', [
            'products' => $products,
            'categories' => $categories,
            'totalCupsToday' => $totalCups, // using same variable names to avoid big blade changes
            'totalRevenueToday' => $totalRevenue,
            'topSellerName' => $topSellerName,
            'topSellerQty' => $topSellerQty,
            'uniqueMenuSoldToday' => $uniqueMenuSold,
            'cupsGrowth' => $cupsGrowth,
            'revenueGrowth' => $revenueGrowth,
            'uniqueMenuGrowth' => $uniqueMenuGrowth,
            'topCategoryName' => $topCategoryName,
            'topCategoryQty' => $topCategoryQty,
            'categoryGrowth' => $categoryGrowth,
            'periodLabel' => $this->getPeriodLabel(),
        ]);
    }

    private function getPeriodLabel()
    {
        return match($this->filterPeriod) {
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin',
            'this_week' => 'Minggu Ini',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'all_time' => 'Semua Waktu',
            default => 'Bulan Ini'
        };
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100);
    }
}
