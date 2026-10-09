<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Support\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Appointment extends Model
{
    /**
     * The default day, used only when an office has no hours saved yet.
     * Live slot lists come from App\Support\TimeSlots::forOffice(), which
     * builds hourly slots from the office's own opening and closing time.
     */
    public const TIME_SLOTS = [
        '08:00 AM - 09:00 AM',
        '09:00 AM - 10:00 AM',
        '10:00 AM - 11:00 AM',
        '11:00 AM - 12:00 PM',
        '12:00 PM - 01:00 PM',
        '01:00 PM - 02:00 PM',
        '02:00 PM - 03:00 PM',
        '03:00 PM - 04:00 PM',
        '04:00 PM - 05:00 PM',
    ];

    /**
     * How many of a day's appointments are the day's slots.
     *
     * A day holds this many people and stops there; anyone past it still
     * books (booking is never turned away) but is marked as coming after
     * the day's first fill rather than taking one of it. The registrar's
     * approval, not the clock, is what lets a day run past this number.
     */
    public const SLOTS_PER_DAY = 10;

    /**
     * Maximum concurrent bookings per time slot, configurable per office.
     */
    public const SLOT_LIMITS = [
        'OSAS' => 8,
        'Registrar' => 6,
        'Guidance' => 8,
        'Cashier' => 8,
        'Accounting' => 6,
        'Admin' => 4,
    ];

    protected $fillable = [
        'reference_code',
        'qr_token',
        'user_id',
        'office',
        'purpose',
        'date',
        'original_date',
        'original_time_slot',
        'time_slot',
        'confirmed_time',
        'reschedule_reason',
        'status',
        'notes',
        'confirmed_at',
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
                $appointment->reference_code = 'APT-' . now()->format('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            }

            if (! $appointment->qr_token) {
                $appointment->qr_token = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * A booking starts as a request — the slot they hoped for. Once the
     * office approves it, it answers with the time it wants them there, and
     * that answer is what the student follows. Everything that tells a
     * student when to arrive reads this rather than the raw request.
     */
    public function timeToCome(): string
    {
        return $this->confirmed_time ?: (string) $this->time_slot;
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

    /**
     * Remaining available seats for a given office / date / time slot,
     * driven by the slot availability & capacity rules.
     * Optionally ignore one appointment (e.g. the one being rescheduled).
     */
    public static function remainingSlots(string $office, Carbon|string $date, string $timeSlot, ?int $ignoreId = null): int
    {
        return app(SlotAvailabilityService::class)
            ->checkForOffice($office, $date, $timeSlot, $ignoreId)['remaining'];
    }

    /**
     * Full availability view (slots with remaining seats and status)
     * driven by the slot availability & capacity rules.
     */
    public static function availableSlots(string $office, Carbon|string $date, ?int $ignoreId = null): array
    {
        return app(SlotAvailabilityService::class)
            ->availableSlots($office, $date, $ignoreId);
    }
}