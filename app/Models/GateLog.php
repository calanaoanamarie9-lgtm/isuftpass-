<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One scan at a campus gate.
 *
 * The original rows — written when someone opens a pass or appointment QR
 * on their phone — carry only the user. The guard desk adds the rest: the
 * pass that was scanned, the name on it, and the entry/exit timestamps, so
 * a single row can be read as "this person came in at 9:02 and left at
 * 9:40". A second scan of the same pass closes the open row instead of
 * opening a new one.
 */
class GateLog extends Model
{
    protected $fillable = [
        'user_id',
        'pass_type',
        'pass_id',
        'person_name',
        'reference_code',
        'destination',
        'purpose',
        'guard_id',
        'direction',
        'gate',
        'time_in',
        'time_out',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The guard who wrote this row. Named guardUser() because Model::guard()
     * is Eloquent's own mass-assignment method and cannot be overridden.
     */
    public function guardUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_id');
    }

    /**
     * True while this person is still inside: the session was opened and
     * has not been closed by a second scan.
     */
    public function isOpen(): bool
    {
        return $this->time_in !== null && $this->time_out === null;
    }
}
