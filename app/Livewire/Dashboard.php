<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]
#[Title('Dashboard - POS Cafe')]
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

    #[On('transaction-created')]
    public function refreshDashboard()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->totalCategories = Category::count();
        $this->todayTransactions = Transaction::whereDate('created_at', today())->where('status', 'completed')->count();
        $this->todayRevenue = (float) Transaction::whereDate('created_at', today())->where('status', 'completed')->sum('total');
        
        $this->todayItemsSold = (int) Transaction::whereDate('transactions.created_at', today())
                                ->where('transactions.status', 'completed')
                                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                                ->sum('transaction_details.quantity');

        $this->todayProfit = (float) Transaction::whereDate('transactions.created_at', today())
                                ->where('transactions.status', 'completed')
                                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                                ->sum('transaction_details.profit');

        // Data Kemarin untuk Komparasi (Growth %)
        $yesterday = now()->subDay();
        $yesterdayItemsSold = Transaction::whereDate('transactions.created_at', $yesterday)
                                ->where('transactions.status', 'completed')
                                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                                ->sum('transaction_details.quantity');
        $yesterdayTransactions = Transaction::whereDate('created_at', $yesterday)->where('status', 'completed')->count();
        $yesterdayRevenue = Transaction::whereDate('created_at', $yesterday)->where('status', 'completed')->sum('total');
        $yesterdayProfit = Transaction::whereDate('transactions.created_at', $yesterday)
                                ->where('transactions.status', 'completed')
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
            ->leftJoin('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where(function($q) {
                $q->whereNull('transactions.status')->orWhere('transactions.status', 'completed');
            })
            ->groupBy('products.id')
            ->orderBy('total_sold', 'DESC')
            ->take(5)
            ->get();

        $this->paymentMethodStats = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->select('payment_method', DB::raw('count(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->toArray();
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
        // Selalu load data terbaru setiap kali render/navigasi
        $this->loadDashboardData();

        $chartLabels = [];
        $chartData = [];

        // Loop 7 hari ke belakang
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->translatedFormat('D, d M');
            $total = (float) Transaction::whereDate('created_at', $date->format('Y-m-d'))->where('status', 'completed')->sum('total');
            $chartData[] = $total;
        }

        return view('livewire.dashboard', [
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }
}