<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_items';

    // Threshold below which an item is considered "low stock".
    const LOW_STOCK_THRESHOLD = 10;

    protected $fillable = [
        'name',
        'price',
        'qty',
        'unit',
        'type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty'   => 'integer',
    ];

    public function getStatusAttribute(): string
    {
        if ($this->qty <= 0) {
            return 'out';
        }

        if ($this->qty <= self::LOW_STOCK_THRESHOLD) {
            return 'low';
        }

        return 'in';
    }
}
