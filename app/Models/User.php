<?php

namespace App\Models;

use App\Enums\Office;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Concerns\HasAvatarUrl;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasAvatarUrl, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_REGISTRAR = 'registrar';

    public const ROLE_CASHIER = 'cashier';

    public const ROLE_STUDENT = 'student';

    public const ROLE_DEPARTMENT = 'department';

    public const ROLE_OFFICE = 'office';

    /**
     * Campus security desk. A guard is not a bookable office, which is why
     * this is a real role and not an office account wearing a "Guard" label:
     * Office::registeredOffices() would otherwise offer the guard hut as a
     * destination students can book.
     */
    public const ROLE_GUARD = 'guard';

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
     * email_verified_at is listed deliberately. The seeders and the admin
     * panel both pass a timestamp to User::create(), and a guarded column is
     * dropped without a word — which left every system-issued account
     * unverified. Nothing request-supplied can reach it: the only fill() sink
     * is ProfileUpdateRequest, whose rules allow name and email only, and the
     * signup form builds its attributes by hand.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'first_name',
        'middle_name',
        'email',
        'email_verified_at',
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
        'valid_id',
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
        'avatar',
        'avatar_data',
    ];

    /**
     * Send the "please verify your address" mail.
     *
     * The trait would fall back to Laravel's stock template; ISUFSTPASS ships
     * its own branded one, so point the notification at it instead. Callers
     * must go through SafeMailer::verifyEmail() — the notification travels by
     * notify() rather than Mail::to(), so nothing else guards it.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

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

    public function isGuard(): bool
    {
        return $this->role === self::ROLE_GUARD;
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
     * True once the personal-details step has been submitted: students
     * keep their contact number on the profile row, every other type on
     * the user itself.
     */
    public function hasCompletedProfile(): bool
    {
        return (bool) ($this->contact_number || $this->studentProfile?->contact_number);
    }

    /**
     * True while the personal-details step still stands between this user
     * and the dashboard.
     *
     * The step belongs to the registration flow, so it applies to everyone
     * who signed up through it — students and the typed registration types
     * (alumni, guest, parent). Staff accounts are seeded or created by an
     * admin and never saw a registration form: for them the step does not
     * exist, complete or not.
     */
    public function requiresProfileCompletion(): bool
    {
        if (! $this->isStudent() && ! $this->registration_type) {
            return false;
        }

        return ! $this->hasCompletedProfile();
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
     * Effective role used by the sidebar/top-bar label and role-based
     * authorisation. Alumni/parent/guest keep the underlying 'student' role in
     * the DB (middleware still checks $this->role) but are surfaced here as
     * their registration type so callers can branch on a real
     * 'alumni'|'parent'|'guest' role value.
     */
    public function effectiveRole(): string
    {
        return match ($this->registration_type) {
            'alumni' => 'alumni',
            'parent', 'guardian' => 'parent',
            'guest' => 'guest',
            default => $this->role ?: 'student',
        };
    }

    /**
     * Backwards-compatible alias for effectiveRole().
     */
    public function roleRule(): string
    {
        return $this->effectiveRole();
    }

    /**
     * Applicant classification surfaced to staff portals. Alumni, parent and
     * guest come from the registration_type typed at signup; every other
     * signup is a student (registration_type is left null for them).
     */
    public function classification(): string
    {
        return match ($this->registration_type) {
            'alumni' => 'alumni',
            'parent', 'guardian' => 'parent',
            'guest' => 'guest',
            default => 'student',
        };
    }

    public function classificationLabel(): string
    {
        return ucfirst($this->classification());
    }

    /*
    |--------------------------------------------------------------------------
    | Role-based access control
    |--------------------------------------------------------------------------
    |
    | The catalog of roles and permissions lives in App\Enums\Role and
    | App\Enums\Permission (mirrored into the roles / permissions tables by
    | RolesAndPermissionsSeeder). These helpers resolve what the current
    | account may do, and are the single source the Gates read from.
    |
    */

    /**
     * The RBAC role this account holds.
     */
    public function rbacRole(): Role
    {
        return Role::fromEffective($this->effectiveRole());
    }

    public function hasPermission(Permission|string $permission): bool
    {
        return $this->rbacRole()->hasPermission($permission);
    }

    /**
     * Whether this account may submit document requests at all.
     */
    public function canRequestDocuments(): bool
    {
        return $this->hasPermission(Permission::RequestDocuments);
    }

    /**
     * Whether this account keeps a Last / First / Middle name.
     *
     * Parents and guests book appointments only: no record is ever issued in
     * their name, so their sign-up asks for one Full Name and it is kept on
     * the display `name` as typed, with the parts left empty. Students,
     * alumni and every staff role keep the structured name — a document
     * request or a printed form is issued under it.
     */
    public function hasStructuredName(): bool
    {
        return ! in_array($this->effectiveRole(), ['parent', 'guest'], true);
    }

    /**
     * Document audiences this account may request.
     *
     * @return list<string>
     */
    public function documentAudiences(): array
    {
        return match ($this->classification()) {
            'alumni' => ['alumni', 'both'],
            'student' => ['student', 'both'],
            default => [],
        };
    }

    /**
     * Offices this account may book, as value => label.
     *
     * Every role sees the same list — SSC, Registrar, Accounting, Library and
     * the four colleges — because the booking form must offer exactly what a
     * student may book. The Cashier's is not among them: it is a payment
     * counter, not an appointment desk. Validation, the book-appointment gate
     * and the form dropdown all read from this method, so the three can never
     * disagree.
     *
     * @return array<string, string>
     */
    public function bookableOffices(): array
    {
        return Office::bookable();
    }

    public function canBookOffice(string $office): bool
    {
        return array_key_exists($office, $this->bookableOffices());
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

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
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

    /**
     * Keep the display `name` in sync with the structured name parts whenever
     * they are present, so the rest of the app keeps rendering a natural
     * "First Middle Last" while official print-outs use formattedName().
     *
     * Accounts that have not filled the parts in (users created before the
     * split) keep their existing `name` untouched.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            $natural = trim(implode(' ', array_filter([
                $user->first_name,
                $user->middle_name,
                $user->last_name,
            ], fn ($part) => $part !== null && $part !== '')));

            if ($natural !== '') {
                $user->name = $natural;
            }
        });
    }

    /**
     * Official rendering of the applicant's name for requisition forms,
     * claim passes and other print-outs: "[Last Name], [First Name] [Middle
     * Name]". Falls back to the display `name` for accounts that have not
     * filled in the parts yet.
     */
    public function formattedName(): string
    {
        if ($this->last_name === null && $this->first_name === null) {
            return (string) $this->name;
        }

        $middle = $this->middle_name ? ' '.$this->middle_name : '';

        return trim(($this->last_name ?? '').', '.($this->first_name ?? '').$middle);
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
