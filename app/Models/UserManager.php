<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserManager extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'login',
        'email',
        'password',
        'role_id',
        'description',
        'created_by', // បន្ថែម
        'updated_by', // បន្ថែម
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * UserManager belongs to a single role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * អ្នកបង្កើត User នេះ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserManager::class, 'created_by');
    }

    /**
     * អ្នកកែប្រែ User ចុងក្រោយ
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(UserManager::class, 'updated_by');
    }

    /**
     * Get all permissions from the user's role as a Collection
     */
    public function permissions()
    {
        return $this->role ? $this->role->permissions : collect();
    }

    /**
     * Check if the user has a specific permission
     */
    public function hasPermission($slug)
    {
        return $this->permissions()->contains('slug', $slug);
    }

    /**
     * Shortcut for checking if the user is admin
     */
    public function isAdmin()
    {
        return $this->role && strtolower($this->role->role_name) === 'admin';
    }
}
