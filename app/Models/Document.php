<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Document extends Model
{
    /** Audience sentinels — who may request this document. */
    public const AUDIENCE_STUDENT = 'student';

    public const AUDIENCE_ALUMNI = 'alumni';

    public const AUDIENCE_BOTH = 'both';

    protected $fillable = [
        'name',
        'description',
        'fee',
        'audience',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function requests(): BelongsToMany
    {
        return $this->belongsToMany(DocumentRequest::class, 'document_request_document');
    }

    /**
     * Who the document may be requested by, as value => label.
     *
     * @return array<string, string>
     */
    public static function audiences(): array
    {
        return [
            self::AUDIENCE_STUDENT => 'Student',
            self::AUDIENCE_ALUMNI => 'Alumni',
            self::AUDIENCE_BOTH => 'Student & Alumni',
        ];
    }

    /**
     * True when an account of the given classification (User::classification())
     * may request this document.
     */
    public function allows(string $classification): bool
    {
        return $this->audience === self::AUDIENCE_BOTH
            || $this->audience === $classification;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Restrict to documents an account with the given audiences may request.
     * An empty audience list yields no documents.
     *
     * @param  list<string>  $audiences
     */
    public function scopeVisibleTo(Builder $query, array $audiences): Builder
    {
        return $query->whereIn('audience', $audiences);
    }
}
