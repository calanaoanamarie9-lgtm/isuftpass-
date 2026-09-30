<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_REGISTRAR = 'registrar';
    public const ROLE_CASHIER = 'cashier';
    public const ROLE_STUDENT = 'student';
    public const ROLE_DEPARTMENT = 'department';

    /**
     * Approval states for accounts created through the office / staff
     * self-registration form. Everything else defaults to APPROVED so the
     * seeded and admin-created accounts are never gated.
     */
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'office',
        'registration_type',
        'contact_number',
        'student_id',
        'course',
        'year_graduated',
        'organization',
        'address',
        'purpose',
        'relationship_to_student',
        'student_full_name',
        'position',
        'employee_id',
        'password',
        'is_active',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isRegistrar(): bool
    {
        return $this->role === self::ROLE_REGISTRAR;
    }

    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isDepartment(): bool
    {
        return $this->role === self::ROLE_DEPARTMENT;
    }

    /**
     * True once an admin has signed off the account. Pending and rejected
     * accounts are held out of the application at the login gate.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::APPROVAL_REJECTED;
    }

    /**
     * Apply an admin decision to this office / staff account.
     */
    public function approve(User $admin): void
    {
        $this->forceFill([
            'approval_status' => self::APPROVAL_APPROVED,
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'rejection_reason' => null,
            'is_active' => true,
        ])->save();
    }

    public function reject(User $admin, ?string $reason = null): void
    {
        $this->forceFill([
            'approval_status' => self::APPROVAL_REJECTED,
            'approved_at' => null,
            'approved_by' => $admin->id,
            'rejection_reason' => $reason,
            'is_active' => false,
        ])->save();
    }

    /**
     * Office this staff account manages. Defaults to the Registrar office.
     */
    public function officeScope(): string
    {
        return $this->office ?? 'Registrar';
    }

    /**
     * Effective role used by the sidebar/top-bar label. Alumni/parent/guest
     * keep the underlying 'student' role in the DB (middleware still checks
     * $this->role) but are surfaced here as their registration type so the
     * sidebar can branch on a real 'alumni'|'parent'|'guest' role value.
     */
    public function roleRule(): string
    {
        return match ($this->registration_type) {
            'alumni' => 'alumni',
            'parent', 'guardian' => 'parent',
            'guest' => 'guest',
            default => $this->role ?: 'student',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Admin who signed off this account, if any.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function gateLogs(): HasMany
    {
        return $this->hasMany(GateLog::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'user_id');
    }

    public function feedbacks(): MorphMany
    {
        return $this->morphMany(Feedback::class, 'feedbackable');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    /**
     * Office / staff accounts still waiting on an admin decision.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
