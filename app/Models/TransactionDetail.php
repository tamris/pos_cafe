<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
        'harga_beli',
        'profit',
        'notes',
        'addons'
    ];

    protected $casts = [
        'addons' => 'array',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
