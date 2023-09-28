<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        "cart",
        "total_price",
        "user_id",
        "status",
    ];
}

/*
    cart = [
        {product_name, product_size, product_price, product_quantity},
        {product_name, product_size, product_price, product_quantity}
    ]
*/
