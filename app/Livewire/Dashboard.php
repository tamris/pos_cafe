<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]
#[Title('Dashboard - Toko Kendali POS')]
class Dashboard extends Component
{
    public $totalCategories;
    public $todayTransactions;
    public $todayRevenue;
    public $recentTransactions;
    public $topProducts;
    public $todayProfit;
    public $todayItemsSold;
    public $paymentMethodStats;
    public $itemsSoldGrowth;
    public $transactionsGrowth;
    public $revenueGrowth;
    public $profitGrowth;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->totalCategories = Category::count();
        $this->todayTransactions = Transaction::whereDate('created_at', today())->count();
        $this->todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
        $this->todayItemsSold = Transaction::whereDate('transactions.created_at', today())
                                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                                ->sum('transaction_details.quantity');
        $this->todayProfit = Transaction::whereDate('transactions.created_at', today())
                                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                                ->sum('transaction_details.profit');

        // Data Kemarin untuk Komparasi (Growth %)
        $yesterday = now()->subDay();
        $yesterdayItemsSold = Transaction::whereDate('transactions.created_at', $yesterday)
                                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                                ->sum('transaction_details.quantity');
        $yesterdayTransactions = Transaction::whereDate('created_at', $yesterday)->count();
        $yesterdayRevenue = Transaction::whereDate('created_at', $yesterday)->sum('total');
        $yesterdayProfit = Transaction::whereDate('transactions.created_at', $yesterday)
                                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                                ->sum('transaction_details.profit');

        $this->itemsSoldGrowth = $this->calculateGrowth($this->todayItemsSold, $yesterdayItemsSold);
        $this->transactionsGrowth = $this->calculateGrowth($this->todayTransactions, $yesterdayTransactions);
        $this->revenueGrowth = $this->calculateGrowth($this->todayRevenue, $yesterdayRevenue);
        $this->profitGrowth = $this->calculateGrowth($this->todayProfit, $yesterdayProfit);

        $this->recentTransactions = Transaction::with('user')
            ->latest()
            ->take(5)
            ->get();

        $this->topProducts = Product::select('products.*', DB::raw('COALESCE(SUM(transaction_details.quantity), 0) as total_sold'))
            ->leftJoin('transaction_details', 'products.id', '=', 'transaction_details.product_id')
            ->groupBy('products.id')
            ->orderBy('total_sold', 'DESC')
            ->take(5)
            ->get();

        $this->paymentMethodStats = Transaction::whereDate('created_at', today())
            ->select('payment_method', DB::raw('count(*) as count'))
            ->groupBy('payment_method')
            ->get();
    }

    private function calculateGrowth($today, $yesterday)
    {
        if ($yesterday == 0) {
            return $today > 0 ? 100 : 0;
        }
        return round((($today - $yesterday) / $yesterday) * 100, 1);
    }

    public function render()
    {
        $chartLabels = [];
        $chartData = [];

        // Loop 7 hari ke belakang
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            // Label: "Senin, 20 Nov" (Format Indonesia)
            $chartLabels[] = $date->translatedFormat('D, d M');
            
            // Data: Total penjualan pada tanggal tersebut
            $total = Transaction::whereDate('created_at', $date->format('Y-m-d'))->sum('total');
            $chartData[] = $total;
        }
        // ------------------------------------------------

        return view('livewire.dashboard', [
            // ... (variable lama: totalProducts, todayTransactions, dll) ...
            
            // Kirim data grafik ke view
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }
}