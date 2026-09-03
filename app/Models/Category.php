<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function transactionDetails()
    {
        return $this->hasManyThrough(TransactionDetail::class, Product::class);
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'addon_category')->withTimestamps();
    }
}