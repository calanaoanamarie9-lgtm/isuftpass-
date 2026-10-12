<?php

namespace App\Models;

use App\Enums\DocumentRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class DocumentRequest extends Model
{
    protected $fillable = [
        'request_number',
        'user_id',
        'student_name',
        'student_address',
        'student_contact',
        'student_course_year',
        'status',
        'purpose_type',
        'transfer_to',
        'educational_status',
        'educational_level',
        'claim_mode',
        'representative_name',
        'others_specification',
        'attachments',
        'submitted_at',
        'processing_at',
        'for_signature_at',
        'ready_at',
        'paid_at',
        'or_number',
        'op_number',
        'submitted_or_number',
        'submitted_or_date',
        'submitted_or_amount',
        'or_submitted_at',
        'or_validated_at',
        'or_validation_note',
        'completed_at',
        'cancelled_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'submitted_at' => 'datetime',
            'processing_at' => 'datetime',
            'for_signature_at' => 'datetime',
            'ready_at' => 'datetime',
            'paid_at' => 'datetime',
            'submitted_or_date' => 'date',
            'submitted_or_amount' => 'decimal:2',
            'or_submitted_at' => 'datetime',
            'or_validated_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DocumentRequest $request) {
            $request->request_number = 'REQ-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
            $request->claim_token = (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_request_document');
    }

    public function documentsSummary(): string
    {
        $names = $this->documents->pluck('name');

        if ($this->others_specification) {
            $names->push('Others: '.$this->others_specification);
        }

        return $names->join(', ');
    }

    public function feedback(): MorphMany
    {
        return $this->morphMany(Feedback::class, 'feedbackable');
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, [
            DocumentRequestStatus::COMPLETED->value,
            DocumentRequestStatus::CANCELLED->value,
        ], true);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * True once the student has presented their Official Receipt details
     * and the request is waiting on the registrar to validate them.
     */
    public function hasSubmittedOr(): bool
    {
        return $this->or_submitted_at !== null;
    }

    /**
     * Issue the Order of Payment that accompanies the approval — the
     * official billing statement the student settles at the Cashier's
     * Office. Generated once; re-approvals never renumber it.
     */
    public function issueOrderOfPayment(): void
    {
        if ($this->op_number !== null) {
            return;
        }

        $this->forceFill([
            'op_number' => 'OP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
        ])->save();
    }

    public function totalFee(): float
    {
        return (float) $this->documents->sum('fee');
    }

    /**
     * Status pipeline index (0-based) used by the tracking timeline.
     */
    public function pipelineIndex(): int
    {
        foreach (DocumentRequestStatus::pipeline() as $i => $step) {
            if ($step->value === $this->status) {
                return $i;
            }
        }

        return DocumentRequestStatus::CANCELLED->value === $this->status
            ? -1
            : 0;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            DocumentRequestStatus::COMPLETED->value,
            DocumentRequestStatus::CANCELLED->value,
        ]);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereIn('status', [
            DocumentRequestStatus::COMPLETED->value,
            DocumentRequestStatus::CANCELLED->value,
        ]);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->whereNotNull('paid_at');
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereNull('paid_at');
    }

    /**
     * Advance the request to the next pipeline state.
     */
    public function advanceTo(string $status): self
    {
        $stamp = [
            DocumentRequestStatus::PROCESSING->value => 'processing_at',
            DocumentRequestStatus::FOR_SIGNATURE->value => 'for_signature_at',
            DocumentRequestStatus::READY_FOR_PICKUP->value => 'ready_at',
            DocumentRequestStatus::COMPLETED->value => 'completed_at',
            DocumentRequestStatus::CANCELLED->value => 'cancelled_at',
        ][$status] ?? null;

        $this->status = $status;

        if ($stamp) {
            $this->{$stamp} = now();
        }

        return $this;
    }
}
