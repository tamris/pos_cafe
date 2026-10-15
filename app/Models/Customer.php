<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'points_balance',
        'notes',
    ];

    protected $casts = [
        'points_balance' => 'integer',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function pointHistories(): HasMany
    {
        return $this->hasMany(CustomerPointHistory::class)->latest();
    }
}
