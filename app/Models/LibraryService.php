<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * An auxiliary library service — the ones that are not a consultation.
 *
 * The rows are the catalogue: the library describes a service once and the
 * page, its search and its request panel all read the same words.
 */
class LibraryService extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'summary',
        'description',
        'how_to_avail',
        'requirements',
        'icon',
        'is_requestable',
        'is_active',
        'sort_order',
    ];

    /**
     * Page grouping keys => chip labels, in display order.
     */
    public const CATEGORIES = [
        'research' => 'Research',
        'access' => 'Access',
        'awareness' => 'Awareness',
        'reproduction' => 'Copying & delivery',
    ];

    protected function casts(): array
    {
        return [
            'is_requestable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    /**
     * "What to prepare", one item per line in the database.
     *
     * @return array<int, string>
     */
    public function requirementList(): array
    {
        return collect(preg_split('/\R/', (string) $this->requirements))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The shape the page needs — cards, search, filters and the request panel
     * all read from this, so the words never drift apart.
     *
     * @return array<string, mixed>
     */
    public function toCard(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'summary' => $this->summary,
            'category' => $this->category,
            'categoryLabel' => $this->categoryLabel(),
            'description' => $this->description,
            'howToAvail' => $this->how_to_avail,
            'requirements' => $this->requirementList(),
            'requestable' => $this->is_requestable,
            'icon' => $this->icon,
        ];
    }
}
