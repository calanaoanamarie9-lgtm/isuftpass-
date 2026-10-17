<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'name',
        'location',
        'contact',
        'hours',
        'description',
        'is_active',
        'default_slot_capacity',
        'open_time',
        'close_time',
        'slot_definitions',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'slot_definitions' => 'array',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
