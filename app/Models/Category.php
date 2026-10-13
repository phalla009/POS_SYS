<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // ស្វ័យប្រវត្តិដាក់ ID ពេលបង្កើតថ្មី
        static::creating(function ($category) {
            if (Auth::check()) {
                $category->created_by = Auth::id();
                $category->updated_by = Auth::id();
            }
        });

        // ពេល Update ទុកឱ្យ Laravel ចាប់ updated_at និង updated_by ធម្មតា
        static::updating(function ($category) {
            if (Auth::check()) {
                $category->updated_by = Auth::id();
            }
        });
    }

    /**
     * អ្នកបង្កើត Category នេះ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * អ្នកកែប្រែ Category ចុងក្រោយ
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
