<?php

namespace App\Livewire\OnlineOrders;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\CashierShift;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Pesanan Online (Self-Order) - POS Cafe')]
class OnlineOrderIndex extends Component
{
    use WithPagination;

    public $statusFilter = 'active'; // 'active', 'processing', 'ready', 'completed', 'all'
    public $search = '';
    public $activeShift = null;
    public $lastNotifiedOnlineOrderId = null;
    public $newOnlineOrderAlert = null;
    public $isOnlineOrderActive = true;

    // Detail & Void Modal State
    public $selectedTransaction = null;
    public $showDetailModal = false;
    public $showCancelModal = false;
    public $cancelTransactionId = null;
    public $cancelReasonNotes = '';

    public function mount()
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect()->route('dashboard');
        }

        $this->checkActiveShift();
        $latestPaid = Transaction::where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->max('id');
        $this->lastNotifiedOnlineOrderId = $latestPaid ?? 0;

        $setting = \App\Models\Setting::first();
        $this->isOnlineOrderActive = (bool) ($setting->is_online_order_active ?? true);
    }

    public function toggleOnlineOrderStatus()
    {
        $setting = \App\Models\Setting::first();
        if (!$setting) {
            $setting = \App\Models\Setting::create(['is_online_order_active' => true]);
        }

        $this->isOnlineOrderActive = !$this->isOnlineOrderActive;
        $setting->update(['is_online_order_active' => $this->isOnlineOrderActive]);

        if ($this->isOnlineOrderActive) {
            $this->dispatch('alert', type: 'success', message: 'Pesanan Online DIBUKA (Menerima pesanan baru).');
        } else {
            $this->dispatch('alert', type: 'warning', message: 'Pesanan Online DIJEDA SEMENTARA (Toko sedang sibuk).');
        }
    }

    public function checkActiveShift()
    {
        $this->activeShift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();
    }

    public function checkNewOnlineOrders()
    {
        $newOrder = Transaction::with(['details.product'])
            ->where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->where('id', '>', (int) $this->lastNotifiedOnlineOrderId)
            ->oldest()
            ->first();

        if ($newOrder) {
            $this->lastNotifiedOnlineOrderId = $newOrder->id;
            $this->newOnlineOrderAlert = [
                'id' => $newOrder->id,
                'invoice' => $newOrder->invoice_number,
                'customer_name' => $newOrder->customer_name,
                'table_number' => $newOrder->table_number,
                'order_type' => $newOrder->order_type,
                'total' => $newOrder->total,
                'items_count' => $newOrder->details->count(),
            ];

            $this->dispatch('play-online-order-sound', [
                'invoice' => $newOrder->invoice_number,
                'name' => $newOrder->customer_name,
                'total' => number_format($newOrder->total, 0, ',', '.'),
            ]);
        }
    }

    public function dismissOnlineOrderAlert()
    {
        $this->newOnlineOrderAlert = null;
    }

    public function setStatusFilter($filter)
    {
        $this->statusFilter = $filter;
        $this->resetPage();
    }

    public function updateStatus($transactionId, $newStatus)
    {
        $transaction = Transaction::where('order_source', 'self_order')->find($transactionId);
        if (!$transaction) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Pesanan tidak ditemukan.']);
            return;
        }

        $validStatuses = ['pending', 'processing', 'ready', 'completed', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Status tidak valid.']);
            return;
        }

        $this->checkActiveShift();
        $updateData = ['status' => $newStatus];

        if (auth()->check()) {
            $updateData['user_id'] = auth()->id();
        }

        if ($this->activeShift) {
            $updateData['shift_id'] = $this->activeShift->id;
        }

        if ($newStatus === 'cancelled') {
            $updateData['cancelled_reason'] = 'Dibatalkan oleh Kasir';
            $updateData['cancelled_by'] = auth()->id();
            $updateData['cancelled_at'] = now();
        }

        $transaction->update($updateData);

        if ($this->activeShift) {
            $this->activeShift->recalculateTotals();
        }

        $statusLabels = [
            'processing' => 'Sedang Disiapkan di Dapur',
            'ready' => 'Siap Diambil / Diantar',
            'completed' => 'Pesanan Selesai',
            'cancelled' => 'Pesanan Dibatalkan',
        ];

        $label = $statusLabels[$newStatus] ?? $newStatus;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => "Pesanan {$transaction->invoice_number} diubah: {$label}."]);
    }

    public function viewDetail($transactionId)
    {
        $this->selectedTransaction = Transaction::with(['details.product', 'user', 'shift'])->find($transactionId);
        if ($this->selectedTransaction) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function openCancelModal($transactionId)
    {
        $this->cancelTransactionId = $transactionId;
        $this->cancelReasonNotes = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->cancelTransactionId = null;
        $this->cancelReasonNotes = '';
    }

    public function confirmCancelOrder()
    {
        if (!$this->cancelTransactionId) return;

        $transaction = Transaction::find($this->cancelTransactionId);
        if ($transaction) {
            $this->checkActiveShift();
            $transaction->update([
                'status' => 'cancelled',
                'cancelled_reason' => !empty(trim($this->cancelReasonNotes)) ? trim($this->cancelReasonNotes) : 'Dibatalkan oleh Kasir',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
            ]);

            if ($this->activeShift) {
                $this->activeShift->recalculateTotals();
            }

            $this->dispatch('show-toast', ['type' => 'success', 'message' => "Pesanan {$transaction->invoice_number} berhasil dibatalkan."]);
        }

        $this->closeCancelModal();
    }

    public function render()
    {
        $this->checkActiveShift();

        // 1. Stats Counter (Online Orders Today)
        $todayOnlineQuery = Transaction::where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', today());

        $statsProcessing = (clone $todayOnlineQuery)->whereIn('status', ['pending', 'processing'])->count();
        $statsReady = (clone $todayOnlineQuery)->where('status', 'ready')->count();
        $statsCompleted = (clone $todayOnlineQuery)->where('status', 'completed')->count();
        $statsRevenueToday = (clone $todayOnlineQuery)->where('status', 'completed')->sum('total');

        // 2. Query Orders List
        $query = Transaction::with(['details.product', 'user', 'shift'])
            ->where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->latest();

        if ($this->statusFilter === 'active') {
            $query->whereIn('status', ['pending', 'processing', 'ready']);
        } elseif ($this->statusFilter === 'processing') {
            $query->whereIn('status', ['pending', 'processing']);
        } elseif ($this->statusFilter === 'ready') {
            $query->where('status', 'ready');
        } elseif ($this->statusFilter === 'completed') {
            $query->where('status', 'completed')->whereDate('created_at', today());
        }

        if (!empty(trim($this->search))) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('table_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('invoice_number', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->paginate(12);

        return view('livewire.online-orders.online-order-index', [
            'orders' => $orders,
            'statsProcessing' => $statsProcessing,
            'statsReady' => $statsReady,
            'statsCompleted' => $statsCompleted,
            'statsRevenueToday' => $statsRevenueToday,
        ]);
    }
}
