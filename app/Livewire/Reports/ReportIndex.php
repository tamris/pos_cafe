<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Product; 
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\SalesReportExport; 
use Carbon\Carbon;

#[Layout('components.layouts.app')]
#[Title('Laporan Ringkasan - POS Cafe')]
class ReportIndex extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $activeTab = 'penjualan';

    // Properti summary
    public $totalRevenue = 0;
    public $totalProfit = 0;
    public $totalTransactions = 0;
    public $profitMargin = 0;

    // Growth summary
    public $revenueGrowth = 0;
    public $profitGrowth = 0;
    public $transactionsGrowth = 0;
    public $marginGrowth = 0;

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedActiveTab()
    {
        $this->resetPage('penjualanPage');
    }

    public function updatedDateFrom()
    {
        $this->resetPage('penjualanPage');
    }

    public function updatedDateTo()
    {
        $this->resetPage('penjualanPage');
    }

    public function exportExcel()
    {
        $fileName = 'Laporan_Penjualan_Cafe_' . $this->dateFrom . '_sd_' . $this->dateTo . '.xlsx';
        return Excel::download(new SalesReportExport($this->dateFrom, $this->dateTo), $fileName);
    }

    public function render()
    {
        $dateFrom = $this->dateFrom . ' 00:00:00';
        $dateTo = $this->dateTo . ' 23:59:59';

        $summaryQuery = Transaction::whereBetween('created_at', [$dateFrom, $dateTo]);

        $this->totalTransactions = $summaryQuery->count();
        $this->totalRevenue = (float) $summaryQuery->sum('total');

        $this->totalProfit = (float) Transaction::whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->sum('transaction_details.profit');

        $this->profitMargin = $this->totalRevenue > 0 ? round(($this->totalProfit / $this->totalRevenue) * 100, 1) : 0;

        // Komparasi Bulan Kemarin (Bulan Lalu)
        $lastMonthStart = now()->subMonth()->startOfMonth()->format('Y-m-d 00:00:00');
        $lastMonthEnd = now()->subMonth()->endOfMonth()->format('Y-m-d 23:59:59');

        $lastMonthSummary = Transaction::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd]);
        $lastMonthRevenue = (float) (clone $lastMonthSummary)->sum('total');
        $lastMonthTransactions = (clone $lastMonthSummary)->count();
        $lastMonthProfit = (float) Transaction::whereBetween('transactions.created_at', [$lastMonthStart, $lastMonthEnd])
            ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->sum('transaction_details.profit');
        $lastMonthMargin = $lastMonthRevenue > 0 ? round(($lastMonthProfit / $lastMonthRevenue) * 100, 1) : 0;

        $this->revenueGrowth = $this->calculateGrowth($this->totalRevenue, $lastMonthRevenue);
        $this->profitGrowth = $this->calculateGrowth($this->totalProfit, $lastMonthProfit);
        $this->transactionsGrowth = $this->calculateGrowth($this->totalTransactions, $lastMonthTransactions);
        $this->marginGrowth = $this->calculateGrowth($this->profitMargin, $lastMonthMargin);

        $transactions = Transaction::with(['user', 'details'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->latest()
            ->paginate(12, ['*'], 'penjualanPage'); 

        return view('livewire.reports.report-index', [
            'transactions' => $transactions
        ]);
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100);
    }
}