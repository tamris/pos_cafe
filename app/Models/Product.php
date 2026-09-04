<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'price',
        'barcode',
        'harga_beli',
        'operational_cost',
        'ai_pricing_data',
        'is_active',
        'image'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'operational_cost' => 'decimal:2',
        'ai_pricing_data' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function ingredients()
    {
        return $this->hasMany(ProductIngredient::class);
    }

    public function getAvailableAddonsAttribute()
    {
        if ($this->relationLoaded('category') && $this->category && $this->category->relationLoaded('addons')) {
            return $this->category->addons->where('is_active', true);
        }

        return $this->category ? $this->category->addons()->where('addons.is_active', true)->get() : collect();
    }
}
