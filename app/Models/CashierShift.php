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
        'total_sales' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'shift_id');
    }

    /**
     * Recalculate sales totals based on associated transactions.
     */
    public function recalculateTotals()
    {
        $transactions = $this->transactions()->where('status', 'completed')->get();

        $this->total_transactions = $transactions->count();
        $this->cash_sales = $transactions->where('payment_method', 'cash')->sum('total');
        $this->qris_sales = $transactions->where('payment_method', 'qris')->sum('total');
        $this->transfer_sales = $transactions->where('payment_method', 'transfer')->sum('total');
        $this->total_sales = $transactions->sum('total');
        $this->expected_cash = (float) $this->starting_cash + (float) $this->cash_sales;

        if ($this->status === 'closed' && !is_null($this->actual_cash)) {
            $this->difference = (float) $this->actual_cash - (float) $this->expected_cash;
        }

        $this->save();
    }
}
