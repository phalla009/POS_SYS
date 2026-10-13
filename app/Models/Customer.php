<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'name',
        'gender',
        'phone',
        'email',
        'password',
        'status',
        'created_by', // បន្ថែម
        'updated_by', // បន្ថែម
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * អ្នកបង្កើត Customer នេះ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * អ្នកកែប្រែ Customer ចុងក្រោយ
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getStatusAttribute()
    {
        return $this->orders()->count() > 0 ? 'Active' : 'Inactive';
    }
}
