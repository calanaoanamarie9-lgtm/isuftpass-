<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A row in the `roles` catalog, kept in sync with App\Enums\Role by
 * RolesAndPermissionsSeeder. Runtime authorisation resolves from the enum so
 * no query is paid per check; the table exists so the catalog is visible,
 * auditable and queryable (and so user-type roles are clearly separated from
 * staff roles via is_staff).
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'label',
        'is_staff',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_staff' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')->withTimestamps();
    }
}
