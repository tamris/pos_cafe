<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'sku',
        'stock',
        'unit',
        'min_stock',
        'cost_per_unit',
        'buy_price',
        'buy_amount',
        'buy_unit',
        'is_active',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'buy_price' => 'decimal:2',
        'buy_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function mutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    public function productIngredients()
    {
        return $this->hasMany(ProductIngredient::class);
    }

    public function getStatusStockAttribute()
    {
        if ($this->stock <= 0) {
            return 'empty';
        }

        if ($this->stock <= $this->min_stock) {
            return 'warning';
        }

        return 'safe';
    }
}
