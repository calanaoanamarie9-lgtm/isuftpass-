<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    /**
     * The two transactions a student can rate. The morph pair still points
     * at the exact record; transaction_type is the readable label reports
     * and the modal speak in.
     */
    public const TYPE_APPOINTMENT = 'appointment';

    public const TYPE_DOCUMENT_REQUEST = 'document_request';

    protected $fillable = [
        'user_id',
        'feedbackable_type',
        'feedbackable_id',
        'transaction_type',
        'service_area',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedbackable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Store one rating for a transaction. The unique index on
     * (user_id, feedbackable_type, feedbackable_id) makes a second call a
     * no-op, so a retried request can never double-count a rating.
     */
    public static function recordFor(
        User $user,
        Appointment|DocumentRequest $feedbackable,
        int $rating,
        ?string $comment,
    ): self {
        $isAppointment = $feedbackable instanceof Appointment;

        return static::firstOrCreate(
            [
                'user_id' => $user->id,
                'feedbackable_type' => $feedbackable->getMorphClass(),
                'feedbackable_id' => $feedbackable->id,
            ],
            [
                'transaction_type' => $isAppointment
                    ? self::TYPE_APPOINTMENT
                    : self::TYPE_DOCUMENT_REQUEST,
                'service_area' => $isAppointment
                    ? $feedbackable->office
                    : $feedbackable->documentsSummary(),
                'rating' => $rating,
                'comment' => $comment,
            ],
        );
    }
}
