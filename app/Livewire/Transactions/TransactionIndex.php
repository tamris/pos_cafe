<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Riwayat Transaksi - Toko Kendali')]
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
        $this->selectedTransaction = Transaction::with(['details.product', 'user'])->find($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function render()
    {
        $query = Transaction::with('user')->latest();

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

        $todayQuery = Transaction::whereDate('created_at', today());
        if ($this->paymentMethodFilter) {
            $todayQuery->where('payment_method', $this->paymentMethodFilter);
        }
        $todayOmset = $todayQuery->sum('total');

        // --------------------------------

        $transactions = $query->paginate(10);

        return view('livewire.transactions.transaction-index', [
            'transactions' => $transactions,
            'filteredTotal' => $filteredTotal,
            'filteredCount' => $filteredCount,
            'todayOmset' => $todayOmset,
        ]);
    }
}