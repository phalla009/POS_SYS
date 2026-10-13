<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'description',
        'created_by', // បន្ថែម
        'updated_by', // បន្ថែម
    ];

    /**
     * Permissions relationship (many-to-many)
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'roles_permissions', // pivot table
            'roles_id',          // foreign key on pivot for Role
            'permissions_id'     // foreign key on pivot for Permission
        )->withTimestamps();
    }

    /**
     * អ្នកបង្កើត Role នេះ
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * អ្នកកែប្រែ Role ចុងក្រោយ
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Assign multiple permissions
     */
    public function assignPermissions(array $permissionIds)
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Remove all permissions
     */
    public function removeAllPermissions()
    {
        $this->permissions()->detach();
    }

    /**
     * Check if the role has a permission
     */
    public function hasPermission($slug)
    {
        return $this->permissions()->where('slug', $slug)->exists();
    }
}
