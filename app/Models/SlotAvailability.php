<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotAvailability extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_FULLY_BOOKED = 'fully_booked';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'office_id',
        'date',
        'time_slot',
        'max_capacity',
        'booked_slots',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'max_capacity' => 'integer',
            'booked_slots' => 'integer',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Default (all-dates) rules for an office.
     */
    public function scopeDefaults(Builder $query): Builder
    {
        return $query->whereNull('date');
    }

    /**
     * Date-specific overrides for an office.
     */
    public function scopeForDate(Builder $query, mixed $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function isFullyBooked(): bool
    {
        return $this->status === self::STATUS_FULLY_BOOKED;
    }
}