<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A row in the `permissions` catalog, kept in sync with App\Enums\Permission
 * by RolesAndPermissionsSeeder.
 */
class Permission extends Model
{
    protected $fillable = [
        'name',
        'label',
        'group',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')->withTimestamps();
    }
}
