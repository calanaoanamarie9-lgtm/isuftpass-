<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Support\QrUrl;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Appointment extends Model
{
    protected $fillable = [
        'reference_code',
        'qr_token',
        'user_id',
        'office',
        'purpose',
        'date',
        'original_date',
        'confirmed_time',
        'start_time',
        'end_time',
        'reschedule_reason',
        'status',
        'notes',
        'confirmed_at',
        'approved_by',
        'checked_in_at',
        'rescheduled_at',
        'completed_at',
        'cancelled_at',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'original_date' => 'date',
            'confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'rescheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            if (! $appointment->reference_code) {
                $appointment->reference_code = 'APT-'.now()->format('Y').'-'.strtoupper(Str::random(4));
            }

            if (! $appointment->qr_token) {
                $appointment->qr_token = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The staff account that approved this appointment, when one is set.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * The approved window spelled the way the office reads it — "8:00 AM – 9:00 AM".
     *
     * Falls back to a single confirmed time, so an approval that named only
     * one hour still prints something true.
     */
    public function scheduledRange(): string
    {
        if ($this->start_time && $this->end_time) {
            return self::clockLabel($this->start_time).' – '.self::clockLabel($this->end_time);
        }

        if ($this->start_time) {
            return self::clockLabel($this->start_time);
        }

        return $this->timeToCome();
    }

    /**
     * A stored time column ("08:00:00" or "08:00") as "8:00 AM".
     */
    private static function clockLabel(string $time): string
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return sprintf('%d:%02d %s', $hour % 12 ?: 12, $minute, $hour < 12 ? 'AM' : 'PM');
    }

    public function feedback(): MorphMany
    {
        return $this->morphMany(Feedback::class, 'feedbackable');
    }

    public function isUpcoming(): bool
    {
        return in_array($this->status, AppointmentStatus::schedulableValues(), true);
    }

    /**
     * The time this student is actually being asked to come.
     *
     * A booking starts as a request — a day, with no hour attached — and the
     * office answers it with the window it wants the student there. Until
     * that answer arrives there is nothing to follow, so the booking reads as
     * still waiting. Everything that tells a student when to arrive reads this
     * column.
     */
    public function timeToCome(): string
    {
        return $this->confirmed_time ?: 'To be set by the office';
    }

    /**
     * The status column spelled the way staff read it ("Checked In"), safe for
     * a value the enum does not know rather than one that throws mid-page.
     */
    public function statusLabel(): string
    {
        return AppointmentStatus::tryFrom((string) $this->status)?->label()
            ?? str_replace('_', ' ', ucfirst((string) $this->status));
    }

    /**
     * The scan target this appointment's QR carries — the URL that opens
     * this one booking's verification page.
     *
     * The student's pass, the page a scanner lands on and the registrar's
     * verification station all build their QR from this method, so every
     * surface prints a code pointing at the same single appointment.
     */
    public function passQrPayload(): string
    {
        return QrUrl::to('/verify/appointment/'.$this->qr_token);
    }

    /**
     * The same code as an inline SVG data URI, for rendering on screen.
     */
    public function passQrDataUri(): string
    {
        return (new SvgWriter)->write(new QrCode($this->passQrPayload()))->getDataUri();
    }

    public function isCancellable(): bool
    {
        return $this->isUpcoming() && $this->date >= Carbon::today();
    }

    public function isReschedulable(): bool
    {
        return $this->isUpcoming() && $this->date > Carbon::today();
    }

    /**
     * True while the office has not approved the appointment yet — the
     * student may still edit or cancel it on their own.
     */
    public function isModifiableByStudent(): bool
    {
        return in_array($this->status, [
            AppointmentStatus::PENDING->value,
            AppointmentStatus::FOR_RESCHEDULE->value,
        ], true);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', AppointmentStatus::schedulableValues());
    }

    /**
     * Queue order: whoever booked first is served first.
     *
     * Arrival is the time the booking was made, and where two bookings land
     * in the same second the insert order breaks the tie — so a day's list
     * reads back in the sequence the bookings actually arrived instead of
     * newest first.
     */
    public function scopeFirstComeFirstServed(Builder $query): Builder
    {
        return $query->orderBy('created_at')->orderBy('id');
    }
}
