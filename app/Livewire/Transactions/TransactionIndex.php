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
    public $statusFilter = '';
    
    public $selectedTransaction;
    public $showDetailModal = false;

    // Properti Modal Pembatalan (Void)
    public $showCancelModal = false;
    public $cancelTransactionId = null;
    public $cancelTransactionInvoice = '';
    public $cancelTransactionTotal = 0;
    public $cancelReasonPreset = 'Pelanggan Membatalkan Pesanan';
    public $cancelReasonNotes = '';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }
    public function updatedPaymentMethodFilter() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }

    public function setFilterToday()
    {
        $this->dateFrom = today()->format('Y-m-d');
        $this->dateTo = today()->format('Y-m-d');
        $this->resetPage();
    }

    public function setFilterThisMonth()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function mount()
    {
        // Kasir fokus pada transaksi Hari Ini secara default, Admin memantau Bulan Ini
        if (auth()->check() && auth()->user()->role === 'admin') {
            $this->setFilterThisMonth();
        } else {
            $this->setFilterToday();
        }
    }

    public function viewDetail($id)
    {
        $query = Transaction::with(['details.product', 'user', 'cancelledBy', 'shift']);
        
        $query->where(function($q) {
            $q->where('order_source', '!=', 'self_order')
              ->orWhere('payment_status', 'paid');
        });

        if (auth()->check() && auth()->user()->role !== 'admin') {
            $query->where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('order_source', 'self_order');
            });
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

    public function openCancelModal($id)
    {
        $query = Transaction::with(['shift', 'user']);
        
        $query->where(function($q) {
            $q->where('order_source', '!=', 'self_order')
              ->orWhere('payment_status', 'paid');
        });

        if (auth()->check() && auth()->user()->role !== 'admin') {
            $query->where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('order_source', 'self_order');
            });
        }

        $transaction = $query->find($id);

        if (!$transaction) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Transaksi tidak ditemukan atau Anda tidak memiliki akses.']);
            return;
        }

        if ($transaction->status === 'cancelled') {
            $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'Transaksi ini sudah berstatus DIBATALKAN.']);
            return;
        }

        $isAdmin = auth()->check() && auth()->user()->role === 'admin';

        // Validasi khusus kasir: hanya boleh membatalkan jika shift transaksi masih AKTIF (Open)
        if (!$isAdmin) {
            if (!$transaction->shift || $transaction->shift->status !== 'open') {
                $this->dispatch('show-toast', [
                    'type' => 'error', 
                    'message' => 'Shift kasir untuk transaksi ini telah ditutup. Pembatalan transaksi historis hanya dapat dilakukan oleh Admin/Owner.'
                ]);
                return;
            }
        }

        // Tutup modal detail jika sedang terbuka agar tidak terjadi modal menumpuk
        $this->showDetailModal = false;

        $this->cancelTransactionId = $transaction->id;
        $this->cancelTransactionInvoice = $transaction->invoice_number;
        $this->cancelTransactionTotal = $transaction->total;
        $this->cancelReasonPreset = 'Pelanggan Membatalkan Pesanan';
        $this->cancelReasonNotes = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->cancelTransactionId = null;
        $this->cancelReasonNotes = '';
    }

    public function confirmCancelTransaction()
    {
        if (!$this->cancelTransactionId) return;

        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        $transaction = Transaction::with('shift')->find($this->cancelTransactionId);

        if (!$transaction || $transaction->status === 'cancelled') {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Transaksi tidak valid atau sudah dibatalkan.']);
            $this->closeCancelModal();
            return;
        }

        // Cek kembali proteksi shift kasir
        if (!$isAdmin) {
            if ($transaction->user_id !== auth()->id() && $transaction->order_source !== 'self_order') {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Anda tidak memiliki hak akses membatalkan transaksi ini.']);
                $this->closeCancelModal();
                return;
            }
            if (!$transaction->shift || $transaction->shift->status !== 'open') {
                $this->dispatch('show-toast', [
                    'type' => 'error', 
                    'message' => 'Shift transaksi ini sudah ditutup. Pembatalan hanya dapat dilakukan oleh Admin/Owner.'
                ]);
                $this->closeCancelModal();
                return;
            }
        }

        \DB::beginTransaction();
        try {
            $transaction->status = 'cancelled';
            $transaction->cancelled_at = now();
            $transaction->cancelled_by = auth()->id();
            
            $fullReason = $this->cancelReasonPreset;
            if (!empty(trim($this->cancelReasonNotes))) {
                $fullReason .= ': ' . trim($this->cancelReasonNotes);
            }
            $transaction->cancelled_reason = $fullReason;
            $transaction->save();

            // Recalculate shift totals if shift is assigned
            if ($transaction->shift) {
                $transaction->shift->recalculateTotals();
            }

            \DB::commit();

            $this->dispatch('show-toast', ['type' => 'success', 'message' => "Transaksi {$transaction->invoice_number} berhasil dibatalkan."]);
            $this->closeCancelModal();

        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        $userId = auth()->id();

        $query = Transaction::with(['user', 'cancelledBy', 'shift'])->latest();

        // Hanya tampilkan transaksi POS kasir langsung ATAU Self-Order yang sudah lunas (paid)
        // Self-order yang belum bayar / batal sebelum bayar tidak masuk ke jurnal transaksi
        $query->where(function($q) {
            $q->where('order_source', '!=', 'self_order')
              ->orWhere('payment_status', 'paid');
        });

        // Jika bukan admin (kasir), tampilkan transaksi kasir yang bersangkutan + transaksi self-order
        if (!$isAdmin) {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('order_source', 'self_order');
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%');
            });
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
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Statistik omset hanya menjumlahkan transaksi yang berhasil (status = completed)
        $completedQuery = (clone $query)->where('status', 'completed');
        $filteredTotal = (clone $completedQuery)->sum('total');
        $filteredCount = (clone $completedQuery)->count();
        $averageTransaction = $filteredCount > 0 ? round($filteredTotal / $filteredCount) : 0;

        // Statistik transaksi yang dibatalkan (status = cancelled)
        $cancelledQuery = (clone $query)->where('status', 'cancelled');
        $cancelledTotal = (clone $cancelledQuery)->sum('total');
        $cancelledCount = (clone $cancelledQuery)->count();

        // Omset Hari ini & Kemarin (disesuaikan berdasarkan kasir jika non-admin)
        $todayQuery = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->where(function($q) {
                $q->where('order_source', '!=', 'self_order')
                  ->orWhere('payment_status', 'paid');
            });

        if (!$isAdmin) {
            $todayQuery->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('order_source', 'self_order');
            });
        }
        if ($this->paymentMethodFilter) {
            $todayQuery->where('payment_method', $this->paymentMethodFilter);
        }
        $todayOmset = $todayQuery->sum('total');

        $yesterdayQuery = Transaction::whereDate('created_at', now()->subDay())
            ->where('status', 'completed')
            ->where(function($q) {
                $q->where('order_source', '!=', 'self_order')
                  ->orWhere('payment_status', 'paid');
            });

        if (!$isAdmin) {
            $yesterdayQuery->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('order_source', 'self_order');
            });
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

        $prevQuery = Transaction::whereBetween('created_at', [$prevFrom, $prevTo])
            ->where('status', 'completed')
            ->where(function($q) {
                $q->where('order_source', '!=', 'self_order')
                  ->orWhere('payment_status', 'paid');
            });

        if (!$isAdmin) {
            $prevQuery->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('order_source', 'self_order');
            });
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
            'cancelledTotal' => $cancelledTotal,
            'cancelledCount' => $cancelledCount,
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