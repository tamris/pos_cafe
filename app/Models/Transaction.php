<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'shift_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid',
        'change',
        'payment_method',
        'order_type',
        'table_number',
        'customer_name',
        'status',
        'order_source',
        'order_token',
        'customer_phone',
        'payment_status',
        'cancelled_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'change' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by')->withTrashed();
    }

    public function shift()
    {
        return $this->belongsTo(CashierShift::class, 'shift_id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOpenBill($query)
    {
        return $query->where('status', 'pending');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isOpenBill(): bool
    {
        return $this->status === 'pending';
    }

    public function scopeSelfOrder($query)
    {
        return $query->where('order_source', 'self_order');
    }

    public function scopeOnlinePaid($query)
    {
        return $query->where('order_source', 'self_order')
            ->where('payment_status', 'paid');
    }

    public function isSelfOrder(): bool
    {
        return $this->order_source === 'self_order';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->invoice_number) {
                $prefix = 'INV-' . date('Ymd') . '-';
                $latestTransaction = static::where('invoice_number', 'like', $prefix . '%')
                    ->orderBy('invoice_number', 'desc')
                    ->first();

                if ($latestTransaction) {
                    $lastNumber = (int) substr($latestTransaction->invoice_number, -4);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                $transaction->invoice_number = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            if (!$transaction->order_token) {
                $transaction->order_token = \Illuminate\Support\Str::random(32);
            }
        });
    }
}