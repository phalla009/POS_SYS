<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'reference', 'type', 'method', 'amount', 'status', 'note', 'order_id', 'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
