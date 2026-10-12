<?php

namespace App\Models;

use App\Support\CampusTime;
use App\Support\QrUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A Visitor Gate Pass: the record behind a walk-in guest's QR.
 *
 * A visitor never has an account, so this row is the only place their
 * name, ID and destination live — and it is what the guard desk scans at
 * the entrance and again at the exit.
 */
class Visitor extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'reference_code',
        'token',
        'name',
        'email',
        'contact_number',
        'id_number',
        'purpose',
        'destination_office',
        'host_name',
        'photo',
        'status',
        'valid_until',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Visitor $visitor) {
            if (! $visitor->reference_code) {
                $visitor->reference_code = 'VIS-'.now()->format('Y').'-'.Str::upper(Str::random(4));
            }

            if (! $visitor->token) {
                $visitor->token = (string) Str::uuid();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Expiry is derived, not stored: a pass is good through the end of its
     * valid_until campus day, so a pass issued for today survives every scan
     * made today whatever the hour. The comparison is done on dates in the
     * campus timezone, not UTC, so "valid until today" means today on the
     * wall, not the UTC day that can run eight hours ahead of it.
     */
    public function isExpired(): bool
    {
        return $this->valid_until !== null
            && $this->valid_until->toDateString() < CampusTime::today();
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function isValid(): bool
    {
        return ! $this->isRevoked() && ! $this->isExpired();
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isRevoked() => 'Revoked',
            $this->isExpired() => 'Expired',
            default => 'Valid',
        };
    }

    /**
     * The payload burned into the pass QR — the guard scanner resolves it
     * back to this one row and nothing else.
     */
    public function qrPayload(): string
    {
        return QrUrl::to('/verify/visitor/'.$this->token);
    }

    /**
     * Public URL of the visitor photo, or null when none was taken.
     *
     * Built exactly like HasAvatarUrl::avatar_url — see that trait for why
     * it is request-relative rather than asset().
     */
    public function photoUrl(): ?string
    {
        $path = $this->photo;

        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $disk = (string) config('filesystems.avatar', 'public');

        if ($disk !== 'public') {
            return Storage::disk($disk)->url($path);
        }

        return request()->getBaseUrl().'/storage/'.ltrim($path, '/');
    }
}
