<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    // INI PENTING BANGET BIAR ::create() BISA JALAN
    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'notes',
    ];
}