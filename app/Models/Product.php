<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'price',
        'stock',
        'description',
        'status',
        'category_id',
        'add_to_pos',
        'created_at',
        'created_by', // បន្ថែម
        'updated_by', // បន្ថែម
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'created_at' => 'datetime',
        'min_stock' => 'integer',
    ];

    /**
     * Relationship: Product belongs to Category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship: Product has many images.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Relationship: Creator of the product.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Updater of the product.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Stock status accessor: returns status text and CSS class.
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return [
                'status' => 'out-of-stock',
                'text' => 'Out of Stock',
                'class' => 'out-of-stock',
            ];
        } elseif ($this->stock <= $this->min_stock) {
            return [
                'status' => 'low-stock',
                'text' => 'Low Stock',
                'class' => 'low-stock',
            ];
        } else {
            return [
                'status' => 'in-stock',
                'text' => 'In Stock',
                'class' => 'in-stock',
            ];
        }
    }

    /**
     * Formatted price accessor, e.g. "$12.34"
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Active products scope
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
