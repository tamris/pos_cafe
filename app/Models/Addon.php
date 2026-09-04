<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'harga_beli',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'addon_category')->withTimestamps();
    }
}
