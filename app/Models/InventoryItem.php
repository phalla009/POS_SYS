<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'created_by', // បន្ថែម
        'updated_by', // បន្ថែម
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty'   => 'integer',
    ];

    /**
     * អ្នកបង្កើត Inventory Item នេះ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * អ្នកកែប្រែ Inventory Item ចុងក្រោយ
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

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
