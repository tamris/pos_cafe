<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'starting_cash',
        'cash_sales',
        'qris_sales',
        'transfer_sales',
        'total_cash_in',
        'total_cash_out',
        'total_sales',
        'total_transactions',
        'expected_cash',
        'actual_cash',
        'difference',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'starting_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'qris_sales' => 'decimal:2',
        'transfer_sales' => 'decimal:2',
        'total_cash_in' => 'decimal:2',
        'total_cash_out' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    protected $appends = [
        'non_cash_sales',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'shift_id');
    }

    public function cashMovements()
    {
        return $this->hasMany(CashMovement::class, 'shift_id');
    }

    /**
     * Accessor untuk total penjualan non-tunai (QRIS + Transfer).
     */
    public function getNonCashSalesAttribute(): float
    {
        return (float) ($this->qris_sales ?? 0) + (float) ($this->transfer_sales ?? 0);
    }

    /**
     * Recalculate sales totals and drawer cash flow based on transactions and cash movements.
     */
    public function recalculateTotals()
    {
        $transactions = $this->transactions()->where('status', 'completed')->get();

        $this->total_transactions = $transactions->count();
        $this->cash_sales = (float) $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'cash')->sum('total');
        $this->qris_sales = (float) $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'qris')->sum('total');
        $this->transfer_sales = (float) $transactions->filter(fn($t) => in_array(strtolower($t->payment_method ?? ''), ['transfer', 'debit']))->sum('total');
        $this->total_sales = (float) $transactions->sum('total');

        // Akumulasi kas masuk dan keluar dari laci kasir (drawer) pada shift ini
        $drawerMovements = $this->cashMovements()->where('source', 'drawer')->get();
        $this->total_cash_in = (float) $drawerMovements->where('type', 'in')->sum('amount');
        $this->total_cash_out = (float) $drawerMovements->where('type', 'out')->sum('amount');

        // Saldo laci seharusnya = Modal Awal + Penjualan Tunai + Kas Masuk Laci - Kas Keluar Laci
        $this->expected_cash = (float) $this->starting_cash + (float) $this->cash_sales + (float) $this->total_cash_in - (float) $this->total_cash_out;

        if ($this->status === 'closed' && !is_null($this->actual_cash)) {
            $this->difference = (float) $this->actual_cash - (float) $this->expected_cash;
        }

        $this->save();
    }
}
