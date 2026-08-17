<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Riwayat Transaksi - POS Cafe')]
class TransactionIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $paymentMethodFilter = '';
    
    public $selectedTransaction;
    public $showDetailModal = false;

    public function updatedSearch() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }
    public function updatedPaymentMethodFilter() { $this->resetPage(); }

    public function mount()
    {
        // Saat halaman pertama kali dibuka,
        // Otomatis isi filter tanggal dengan Awal Bulan s/d Akhir Bulan ini.
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function viewDetail($id)
    {
        $query = Transaction::with(['details.product', 'user']);
        if (auth()->check() && auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }
        $this->selectedTransaction = $query->find($id);
        if ($this->selectedTransaction) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function render()
    {
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        $userId = auth()->id();

        $query = Transaction::with('user')->latest();

        // Jika bukan admin (kasir), hanya tampilkan transaksi kasir yang bersangkutan
        if (!$isAdmin) {
            $query->where('user_id', $userId);
        }

        if ($this->search) {
            $query->where('invoice_number', 'like', '%' . $this->search . '%');
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        if ($this->paymentMethodFilter) {
            $query->where('payment_method', $this->paymentMethodFilter);
        }

        $filteredTotal = (clone $query)->sum('total');
        $filteredCount = (clone $query)->count();
        $averageTransaction = $filteredCount > 0 ? round($filteredTotal / $filteredCount) : 0;

        // Omset Hari ini & Kemarin (disesuaikan berdasarkan kasir jika non-admin)
        $todayQuery = Transaction::whereDate('created_at', today());
        if (!$isAdmin) {
            $todayQuery->where('user_id', $userId);
        }
        if ($this->paymentMethodFilter) {
            $todayQuery->where('payment_method', $this->paymentMethodFilter);
        }
        $todayOmset = $todayQuery->sum('total');

        $yesterdayQuery = Transaction::whereDate('created_at', now()->subDay());
        if (!$isAdmin) {
            $yesterdayQuery->where('user_id', $userId);
        }
        if ($this->paymentMethodFilter) {
            $yesterdayQuery->where('payment_method', $this->paymentMethodFilter);
        }
        $yesterdayOmset = $yesterdayQuery->sum('total');
        $todayOmsetGrowth = $this->calculateGrowth($todayOmset, $yesterdayOmset);

        // Komparasi Periode Sebelumnya yang Sama Panjang (Dynamic Previous Period)
        $from = $this->dateFrom ? \Carbon\Carbon::parse($this->dateFrom)->startOfDay() : now()->startOfMonth()->startOfDay();
        $to = $this->dateTo ? \Carbon\Carbon::parse($this->dateTo)->endOfDay() : now()->endOfMonth()->endOfDay();
        
        $diffInDays = max(1, $from->diffInDays($to) + 1);
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($diffInDays - 1)->startOfDay();

        $prevQuery = Transaction::whereBetween('created_at', [$prevFrom, $prevTo]);
        if (!$isAdmin) {
            $prevQuery->where('user_id', $userId);
        }
        if ($this->paymentMethodFilter) {
            $prevQuery->where('payment_method', $this->paymentMethodFilter);
        }
        $prevTotal = (clone $prevQuery)->sum('total');
        $prevCount = (clone $prevQuery)->count();
        $prevAverage = $prevCount > 0 ? round($prevTotal / $prevCount) : 0;

        $revenueGrowth = $this->calculateGrowth($filteredTotal, $prevTotal);
        $countGrowth = $this->calculateGrowth($filteredCount, $prevCount);
        $averageGrowth = $this->calculateGrowth($averageTransaction, $prevAverage);

        // --------------------------------

        $transactions = $query->paginate(10);

        return view('livewire.transactions.transaction-index', [
            'transactions' => $transactions,
            'filteredTotal' => $filteredTotal,
            'filteredCount' => $filteredCount,
            'averageTransaction' => $averageTransaction,
            'todayOmset' => $todayOmset,
            'todayOmsetGrowth' => $todayOmsetGrowth,
            'revenueGrowth' => $revenueGrowth,
            'countGrowth' => $countGrowth,
            'averageGrowth' => $averageGrowth,
            'isAdmin' => $isAdmin,
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