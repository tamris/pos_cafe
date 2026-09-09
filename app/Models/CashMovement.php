<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class CashMovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'movement_number',
        'user_id',
        'shift_id',
        'category_id',
        'category_name',
        'type',
        'source',
        'amount',
        'notes',
        'receipt_image',
        'movement_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    protected $appends = [
        'receipt_image_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->movement_number)) {
                $prefix = $model->type === 'in' ? 'CSH-IN' : 'CSH-OUT';
                $dateStr = now()->format('Ymd');
                $randomStr = strtoupper(substr(uniqid(), -4));
                $model->movement_number = "{$prefix}-{$dateStr}-{$randomStr}";
            }

            if (empty($model->movement_date)) {
                $model->movement_date = now();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function shift()
    {
        return $this->belongsTo(CashierShift::class, 'shift_id');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    /**
     * Accessor: Full URL untuk bukti struk/nota.
     */
    public function getReceiptImageUrlAttribute(): ?string
    {
        if (empty($this->receipt_image)) {
            return null;
        }

        if (str_starts_with($this->receipt_image, 'http://') || str_starts_with($this->receipt_image, 'https://')) {
            return $this->receipt_image;
        }

        return asset('storage/' . ltrim($this->receipt_image, '/'));
    }

    public function scopeCashIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeCashOut($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeFromDrawer($query)
    {
        return $query->where('source', 'drawer');
    }

    public function scopeFromBank($query)
    {
        return $query->where('source', 'bank');
    }

    public function scopeFromPettyCash($query)
    {
        return $query->where('source', 'petty_cash');
    }

    /**
     * Hitung total saldo kas riil toko secara real-time / all-time (Cash, Bank, dan Total Riil).
     */
    public static function getStoreRealBalances(): array
    {
        $allSalesQuery = Transaction::where('status', 'completed');
        $allSalesTotal = (float) $allSalesQuery->sum('total');
        $allCashSales = (float) (clone $allSalesQuery)->whereRaw('LOWER(payment_method) = ?', ['cash'])->sum('total');
        $allNonCashSales = (float) (clone $allSalesQuery)->whereRaw('LOWER(payment_method) != ?', ['cash'])->sum('total');

        $allMovements = static::all();
        $allCashInDrawer = (float) $allMovements->where('type', 'in')->where('source', 'drawer')->sum('amount');
        $allCashInBank = (float) $allMovements->where('type', 'in')->where('source', 'bank')->sum('amount');
        $allCashInTotal = (float) $allMovements->where('type', 'in')->sum('amount');

        $allCashOutDrawer = (float) $allMovements->where('type', 'out')->where('source', 'drawer')->sum('amount');
        $allCashOutPetty = (float) $allMovements->where('type', 'out')->where('source', 'petty_cash')->sum('amount');
        $allCashOutBank = (float) $allMovements->where('type', 'out')->where('source', 'bank')->sum('amount');
        $allCashOutTotal = (float) $allMovements->where('type', 'out')->sum('amount');

        $realCashBalance = ($allCashSales + $allCashInDrawer) - ($allCashOutDrawer + $allCashOutPetty);
        $realBankBalance = ($allNonCashSales + $allCashInBank) - $allCashOutBank;
        $totalRealBalance = ($allSalesTotal + $allCashInTotal) - $allCashOutTotal;

        return [
            'cash_balance' => $realCashBalance,
            'bank_balance' => $realBankBalance,
            'total_real_balance' => $totalRealBalance,
            'all_sales_total' => $allSalesTotal,
            'all_cash_sales' => $allCashSales,
            'all_non_cash_sales' => $allNonCashSales,
            'all_cash_in' => $allCashInTotal,
            'all_cash_out' => $allCashOutTotal,
        ];
    }
}
