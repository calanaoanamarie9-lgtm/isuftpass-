<?php

namespace App\Models;

use App\Enums\Office;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A single consultation service offered by an office or department.
 *
 * Each office / department manages its own rows — see
 * App\Http\Controllers\Consultation\ConsultationServiceController.
 */
class ConsultationService extends Model
{
    protected $fillable = [
        'office',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            // 'office' stays a plain string: self-registered offices exist
            // as user-typed names that have no App\Enums\Office case yet.
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Limit to the services owned by one office / department.
     */
    public function scopeForOffice(Builder $query, Office|string $office): Builder
    {
        $value = $office instanceof Office ? $office->value : $office;

        return $query->where('office', $value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Short label shown when a description is missing.
     */
    public function summary(): string
    {
        return $this->description ?: 'Consultation with the ' . $this->office . ' office.';
    }
}
