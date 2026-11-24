<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price',
        'calories',
        'is_healthy',
        'stock',
        'image',
        'merchant_name',
        'canteen_id'
    ];
}
