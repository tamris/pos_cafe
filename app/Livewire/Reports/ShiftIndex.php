<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\CashierShift;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Laporan Shift Kasir - POS Cafe')]
class ShiftIndex extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $activeQuickDate = 'this_month'; // 'today', 'yesterday', 'this_week', 'this_month', 'last_month', 'custom'
    public $selectedUserId = '';
    public $selectedStatus = '';
    public $search = '';

    // Summary Cards
    public $totalShifts = 0;
    public $totalCashSales = 0;
    public $totalNonCashSales = 0;
    public $totalDifference = 0;

    // Modal Detail
    public $showDetailModal = false;
    public $selectedShift = null;

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->activeQuickDate = 'this_month';
    }

    public function updatedDateFrom() 
    { 
        $this->checkQuickDateMatch();
        $this->resetPage(); 
    }

    public function updatedDateTo() 
    { 
        $this->checkQuickDateMatch();
        $this->resetPage(); 
    }

    public function updatedSelectedUserId() { $this->resetPage(); }
    public function updatedSelectedStatus() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function setQuickDate($range)
    {
        $this->activeQuickDate = $range;

        switch ($range) {
            case 'today':
                $this->dateFrom = Carbon::today()->format('Y-m-d');
                $this->dateTo = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->dateFrom = Carbon::yesterday()->format('Y-m-d');
                $this->dateTo = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'this_week':
                $this->dateFrom = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->dateFrom = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->dateTo = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
        }

        $this->resetPage();
    }

    private function checkQuickDateMatch()
    {
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        $startLastMonth = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $endLastMonth = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        if ($this->dateFrom === $today && $this->dateTo === $today) {
            $this->activeQuickDate = 'today';
        } elseif ($this->dateFrom === $yesterday && $this->dateTo === $yesterday) {
            $this->activeQuickDate = 'yesterday';
        } elseif ($this->dateFrom === $startOfWeek && $this->dateTo === $endOfWeek) {
            $this->activeQuickDate = 'this_week';
        } elseif ($this->dateFrom === $startOfMonth && $this->dateTo === $endOfMonth) {
            $this->activeQuickDate = 'this_month';
        } elseif ($this->dateFrom === $startLastMonth && $this->dateTo === $endLastMonth) {
            $this->activeQuickDate = 'last_month';
        } else {
            $this->activeQuickDate = 'custom';
        }
    }

    public function openDetailModal($shiftId)
    {
        $this->selectedShift = CashierShift::with(['user', 'transactions.details.product'])->find($shiftId);
        if ($this->selectedShift) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedShift = null;
    }

    public function render()
    {
        $start = $this->dateFrom . ' 00:00:00';
        $end = $this->dateTo . ' 23:59:59';

        $baseQuery = CashierShift::whereBetween('start_time', [$start, $end]);

        if (!empty($this->selectedUserId)) {
            $baseQuery->where('user_id', $this->selectedUserId);
        }

        if (!empty($this->selectedStatus)) {
            $baseQuery->where('status', $this->selectedStatus);
        }

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $baseQuery->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm);
            });
        }

        // Hitung Summary Cards dari data terfilter
        $summaryClone = clone $baseQuery;
        $this->totalShifts = $summaryClone->count();
        $this->totalCashSales = (float) $summaryClone->sum('cash_sales');
        $this->totalNonCashSales = (float) ($summaryClone->sum('qris_sales') + $summaryClone->sum('transfer_sales'));
        $this->totalDifference = (float) $summaryClone->sum('difference');

        $shifts = $baseQuery->with(['user', 'transactions'])
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        $cashiers = User::orderByRaw("CASE WHEN role = 'admin' THEN 1 ELSE 2 END")
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.reports.shift-index', [
            'shifts' => $shifts,
            'cashiers' => $cashiers,
        ]);
    }
}
