<?php

namespace App\Models;

use App\Models\Concerns\HasAvatarUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasAvatarUrl;

    protected $fillable = [
        'user_id',
        'student_id',
        'course',
        'year_level',
        'contact_number',
        'address',
        'pass_token',
        'avatar',
    ];

    public const YEAR_LEVELS = [
        '1st Year',
        '2nd Year',
        '3rd Year',
        '4th Year',
    ];

    public const COLLEGES = [
        'College of Informatics and Computing Innovations',
        'College of Agriculture',
        'College of Education',
        'College of Business Management and Sustainable Development',
    ];

    protected function casts(): array
    {
        return [
            'pass_token' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}