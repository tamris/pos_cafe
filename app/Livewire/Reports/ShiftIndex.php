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

    // Filters (Admin)
    public $dateFrom;
    public $dateTo;
    public $selectedUserId = '';
    public $selectedStatus = '';
    public $search = '';
    public $activeQuickDate = 'this_month';

    // Summary Cards (Admin)
    public $totalShifts = 0;
    public $totalCashSales = 0;
    public $totalNonCashSales = 0;
    public $totalDifference = 0;

    // Active Shift Session (Kasir)
    public $activeShift = null;

    // Modal Buka Shift
    public $showStartShiftModal = false;
    public $startingCash = 0;
    public $formattedStartingCash = '0';

    // Modal Tutup Shift & Rekonsiliasi Kas
    public $showEndShiftModal = false;
    public $actualCash = 0;
    public $formattedActualCash = '0';
    public $shiftDifference = 0;
    public $shiftNotes = '';

    // Modal Detail
    public $showDetailModal = false;
    public $selectedShift = null;

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->activeQuickDate = 'this_month';

        $this->checkActiveShift();
    }

    public function checkActiveShift()
    {
        $this->activeShift = CashierShift::with(['user', 'transactions'])
            ->where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($this->activeShift) {
            $this->activeShift->recalculateTotals();
        }
    }

    public function openStartShiftModal()
    {
        $this->startingCash = 0;
        $this->formattedStartingCash = '0';
        $this->showStartShiftModal = true;
    }

    public function closeStartShiftModal()
    {
        $this->showStartShiftModal = false;
    }

    public function setStartingCashPreset($amount)
    {
        $this->startingCash = (float) $amount;
        $this->formattedStartingCash = number_format($this->startingCash, 0, ',', '.');
    }

    public function updatedFormattedStartingCash()
    {
        $clean = preg_replace('/[^0-9]/', '', $this->formattedStartingCash);
        $this->startingCash = (float) ($clean ?: 0);
        $this->formattedStartingCash = number_format($this->startingCash, 0, ',', '.');
    }

    public function startShift()
    {
        if ($this->startingCash < 0) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Modal awal tidak boleh negatif.']);
            return;
        }

        $shift = CashierShift::create([
            'user_id' => auth()->id(),
            'start_time' => now(),
            'starting_cash' => (float) $this->startingCash,
            'expected_cash' => (float) $this->startingCash,
            'status' => 'open',
        ]);

        $this->activeShift = $shift;
        $this->showStartShiftModal = false;
        $this->dispatch('show-toast', [
            'type' => 'success', 
            'message' => 'Shift kasir berhasil dibuka dengan modal Rp ' . number_format($this->startingCash, 0, ',', '.')
        ]);
    }

    public function openEndShiftModal()
    {
        $this->checkActiveShift();
        if (!$this->activeShift) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Tidak ada shift aktif yang perlu ditutup.']);
            return;
        }

        $this->actualCash = (float) $this->activeShift->expected_cash;
        $this->formattedActualCash = number_format($this->actualCash, 0, ',', '.');
        $this->calculateShiftDifference();
        $this->shiftNotes = '';
        $this->showEndShiftModal = true;
    }

    public function closeEndShiftModal()
    {
        $this->showEndShiftModal = false;
    }

    public function updatedFormattedActualCash()
    {
        $clean = preg_replace('/[^0-9]/', '', $this->formattedActualCash);
        $this->actualCash = (float) ($clean ?: 0);
        $this->formattedActualCash = number_format($this->actualCash, 0, ',', '.');
        $this->calculateShiftDifference();
    }

    public function calculateShiftDifference()
    {
        if ($this->activeShift) {
            $this->shiftDifference = (float) $this->actualCash - (float) $this->activeShift->expected_cash;
        }
    }

    public function endShift()
    {
        $this->checkActiveShift();
        if (!$this->activeShift) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Tidak ada shift aktif yang perlu ditutup.']);
            return;
        }

        $this->activeShift->end_time = now();
        $this->activeShift->actual_cash = (float) $this->actualCash;
        $this->activeShift->difference = (float) $this->actualCash - (float) $this->activeShift->expected_cash;
        $this->activeShift->notes = $this->shiftNotes;
        $this->activeShift->status = 'closed';
        $this->activeShift->save();

        $closedShiftId = $this->activeShift->id;
        $this->activeShift = null;
        $this->showEndShiftModal = false;

        $this->dispatch('show-toast', [
            'type' => 'success', 
            'message' => 'Shift kasir berhasil ditutup dan direkonsiliasi.'
        ]);

        return redirect()->route('print.shift', $closedShiftId);
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
        if ($this->dateFrom === Carbon::today()->format('Y-m-d') && $this->dateTo === Carbon::today()->format('Y-m-d')) {
            $this->activeQuickDate = 'today';
        } elseif ($this->dateFrom === Carbon::yesterday()->format('Y-m-d') && $this->dateTo === Carbon::yesterday()->format('Y-m-d')) {
            $this->activeQuickDate = 'yesterday';
        } elseif ($this->dateFrom === Carbon::now()->startOfWeek()->format('Y-m-d') && $this->dateTo === Carbon::now()->endOfWeek()->format('Y-m-d')) {
            $this->activeQuickDate = 'this_week';
        } elseif ($this->dateFrom === Carbon::now()->startOfMonth()->format('Y-m-d') && $this->dateTo === Carbon::now()->endOfMonth()->format('Y-m-d')) {
            $this->activeQuickDate = 'this_month';
        } elseif ($this->dateFrom === Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d') && $this->dateTo === Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d')) {
            $this->activeQuickDate = 'last_month';
        } else {
            $this->activeQuickDate = '';
        }
    }

    public function openDetailModal($shiftId)
    {
        $isAdmin = auth()->user()->role === 'admin';
        $shift = CashierShift::with(['user', 'transactions.details.product'])->find($shiftId);

        if ($shift && ($isAdmin || $shift->user_id === auth()->id())) {
            $this->selectedShift = $shift;
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
        $isAdmin = auth()->user()->role === 'admin';

        if (!$isAdmin) {
            $this->checkActiveShift();

            // Kasir: Hanya 5 data shift terakhir milik kasir ini
            $shifts = CashierShift::with(['transactions'])
                ->where('user_id', auth()->id())
                ->orderBy('start_time', 'desc')
                ->paginate(5);

            return view('livewire.reports.shift-index', [
                'shifts' => $shifts,
                'isAdmin' => false,
                'cashiers' => collect(),
            ]);
        }

        // Admin: Query analitik lengkap
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
            'isAdmin' => true,
        ]);
    }
}
