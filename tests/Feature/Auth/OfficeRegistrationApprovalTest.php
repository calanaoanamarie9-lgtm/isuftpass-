<?php

namespace Tests\Feature\Auth;

use App\Mail\OfficeAccountApproved;
use App\Models\User;
use App\Notifications\OfficeAccountApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OfficeRegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function officeApplications(): array
    {
        return [
            'library' => ['office' => 'Library', 'position' => 'Librarian II'],
            'guidance' => ['office' => 'Guidance', 'position' => 'Guidance Counselor'],
            'accounting' => ['office' => 'Accounting', 'position' => 'Accountant I'],
        ];
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    // ------------------------------------------------------------------
    // Registration
    // ------------------------------------------------------------------

    public function test_office_registration_form_renders(): void
    {
        $response = $this->get('/register/form?type=office');

        $response->assertStatus(200);
        $response->assertSee('Office / Staff');
        $response->assertSee('name="office"', false);
        $response->assertSee('name="position"', false);

        // The office is typed, not picked from a list.
        $response->assertSee('Type the name of your office', false);
        $response->assertDontSee('Select your office');
    }

    public function test_office_registration_creates_a_pending_account(): void
    {
        $response = $this->post('/register', [
            'name' => 'Librarian Probe',
            'email' => 'librarian@isufst.edu.ph',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'office',
            'office' => 'Library',
            'position' => 'Librarian II',
            'contact_number' => '09171234567',
        ]);

        $response->assertRedirect(route('register.pending', absolute: false));

        // Deliberately NOT logged in: the account cannot be used until approved.
        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email' => 'librarian@isufst.edu.ph',
            // Self-registered offices get their own role, so they land in
            // the generic workspace instead of a built-in department's.
            'role' => User::ROLE_OFFICE,
            'office' => 'Library',
            'position' => 'Librarian II',
            'contact_number' => '09171234567',
            'approval_status' => User::APPROVAL_PENDING,
            // Inactive as well as pending - two locks, not one. The login
            // gate already holds the account on the status alone; approve()
            // is what switches this flag on.
            'is_active' => false,
        ]);
    }

    public function test_office_registration_requires_office_position_and_contact(): void
    {
        $response = $this->post('/register', [
            'name' => 'Incomplete Staff',
            'email' => 'incomplete@isufst.edu.ph',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'office',
        ]);

        $response->assertSessionHasErrors(['office', 'position', 'contact_number']);
        $this->assertDatabaseMissing('users', ['email' => 'incomplete@isufst.edu.ph']);
    }

    public function test_office_registration_takes_the_office_the_applicant_types(): void
    {
        $this->post('/register', [
            'name' => 'Property Officer',
            'email' => 'property@isufst.edu.ph',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'office',
            'office' => 'Property Management Office',
            'position' => 'Property Officer III',
            'contact_number' => '09171234567',
        ])->assertRedirect(route('register.pending', absolute: false));

        // No list to belong to: an office may apply before it exists in the
        // enum, so the name is stored exactly as typed.
        $this->assertDatabaseHas('users', [
            'email' => 'property@isufst.edu.ph',
            'office' => 'Property Management Office',
            'approval_status' => User::APPROVAL_PENDING,
        ]);
    }

    public function test_a_typed_name_of_an_existing_office_resolves_to_that_office(): void
    {
        $this->post('/register', [
            'name' => 'Librarian Probe',
            'email' => 'librarian2@isufst.edu.ph',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'office',
            'office' => 'University Library',
            'position' => 'Librarian II',
            'contact_number' => '09171234567',
        ])->assertRedirect(route('register.pending', absolute: false));

        // Appointments, consultation services and availability are keyed by
        // that value, so the label has to become the office it names.
        $this->assertDatabaseHas('users', [
            'email' => 'librarian2@isufst.edu.ph',
            'office' => 'Library',
        ]);
    }

    public function test_pending_confirmation_page_is_public(): void
    {
        // No session: this page has to stay reachable by an unapproved applicant.
        $this->get('/register/pending')
            ->assertStatus(200)
            ->assertSee('Application Submitted');
    }

    public function test_the_submission_popup_greets_the_applicant_with_the_promise(): void
    {
        $this->get('/register/pending')
            ->assertStatus(200)
            // The SweetAlert2 dialog the applicant sees after "Create Account".
            ->assertSee('Registration Submitted!')
            ->assertSee('Your registration is pending administrator approval. You will receive an email notification once your account is approved, after which you may log in to ISUFSTPASS.')
            ->assertSee('window.Swal.fire', false)
            ->assertSee("confirmButtonText: 'OK'", false)
            // Confirming takes the applicant back to the Registration Page.
            ->assertSee("window.location.href = '".route('register')."'", false);
    }

    // ------------------------------------------------------------------
    // Login gate
    // ------------------------------------------------------------------

    public function test_pending_office_account_cannot_log_in(): void
    {
        $user = $this->pendingOffice('pending@isufst.edu.ph');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_rejected_office_account_cannot_log_in_and_sees_the_reason(): void
    {
        $user = $this->pendingOffice('rejected@isufst.edu.ph');
        $user->reject($this->admin(), 'Employee ID could not be verified.');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Your office account application was declined. Reason: Employee ID could not be verified.',
        ]);

        $this->assertGuest();
    }

    public function test_approved_office_account_can_log_in(): void
    {
        $user = $this->pendingOffice('approved@isufst.edu.ph');
        $user->approve($this->admin());

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_accounts_are_not_gated_by_the_new_approval_field(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'approval_status' => User::APPROVAL_APPROVED,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    // ------------------------------------------------------------------
    // Admin decisions
    // ------------------------------------------------------------------

    public function test_admin_sees_the_pending_office_queue(): void
    {
        $this->pendingOffice('queue@isufst.edu.ph');

        $this->actingAs($this->admin())
            ->get('/admin/users?approval=pending')
            ->assertStatus(200)
            ->assertSee('queue@isufst.edu.ph')
            ->assertSee('Review Now');
    }

    public function test_admin_can_approve_a_pending_office_account(): void
    {
        $admin = $this->admin();
        $user = $this->pendingOffice('approve-me@isufst.edu.ph');

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/approve")
            ->assertRedirect();

        $user->refresh();

        $this->assertTrue($user->isApproved());
        $this->assertFalse($user->isPendingApproval());
        $this->assertNotNull($user->approved_at);
        $this->assertSame($admin->id, $user->approved_by);
        $this->assertTrue($user->is_active);
    }

    public function test_approving_the_application_notifies_the_applicant_by_email_and_in_app(): void
    {
        Notification::fake();
        Mail::fake();

        $admin = $this->admin();
        $user = $this->pendingOffice('notify-me@isufst.edu.ph', 'Library');

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/approve")
            ->assertRedirect();

        // The registration popup promised an email at approval...
        Mail::assertSent(OfficeAccountApproved::class, function (OfficeAccountApproved $mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // ...and the portal bell lights up for whoever is already around.
        Notification::assertSentTo($user, OfficeAccountApprovedNotification::class);
    }

    public function test_admin_can_reject_a_pending_office_account_with_a_reason(): void
    {
        $user = $this->pendingOffice('reject-me@isufst.edu.ph');

        $this->actingAs($this->admin())
            ->put("/admin/users/{$user->id}/reject", [
                'rejection_reason' => 'No employee ID on file.',
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertTrue($user->isRejected());
        $this->assertSame('No employee ID on file.', $user->rejection_reason);
        $this->assertFalse($user->is_active);
    }

    public function test_approving_twice_is_rejected(): void
    {
        $user = $this->pendingOffice('twice@isufst.edu.ph');
        $user->approve($this->admin());

        $this->actingAs($this->admin())
            ->put("/admin/users/{$user->id}/approve")
            ->assertSessionHas('error');
    }

    public function test_a_rejected_applicant_can_be_approved_after_resubmission(): void
    {
        $admin = $this->admin();
        $user = $this->pendingOffice('resubmit@isufst.edu.ph');

        $user->reject($admin, 'Wrong office.');
        $user->forceFill([
            'approval_status' => User::APPROVAL_PENDING,
            'rejection_reason' => null,
        ])->save();

        $this->actingAs($admin)->put("/admin/users/{$user->id}/approve");

        $user->refresh();

        $this->assertTrue($user->isApproved());
        $this->assertNull($user->rejection_reason);
    }

    // ------------------------------------------------------------------
    // Authorisation
    // ------------------------------------------------------------------

    public function test_non_admins_cannot_approve_or_reject(): void
    {
        $user = $this->pendingOffice('target@isufst.edu.ph');
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->put("/admin/users/{$user->id}/approve")
            ->assertForbidden();

        $this->actingAs($student)
            ->put("/admin/users/{$user->id}/reject")
            ->assertForbidden();

        $this->assertTrue($user->refresh()->isPendingApproval());
    }

    public function test_guests_cannot_approve(): void
    {
        $user = $this->pendingOffice('guest-target@isufst.edu.ph');

        $this->put("/admin/users/{$user->id}/approve")->assertRedirect(route('login'));

        $this->assertTrue($user->refresh()->isPendingApproval());
    }

    public function test_approval_filter_rejects_an_unknown_value(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/users?approval=bogus')
            ->assertNotFound();
    }

    public function test_approve_action_is_audited(): void
    {
        $user = $this->pendingOffice('audited@isufst.edu.ph');

        $this->actingAs($this->admin())
            ->put("/admin/users/{$user->id}/approve");

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.approved',
        ]);
    }

    public function test_approved_office_user_reaches_their_office_dashboard(): void
    {
        $user = $this->pendingOffice('dash@isufst.edu.ph', 'Library');
        $user->approve($this->admin());

        // role=department + office=Library routes to the library dashboard.
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('library.dashboard'));
    }

    /**
     * Build a pending office applicant the way registration does.
     */
    protected function pendingOffice(string $email, string $office = 'Registrar'): User
    {
        return User::factory()->create([
            'email' => $email,
            'role' => User::ROLE_DEPARTMENT,
            'office' => $office,
            'position' => 'Administrative Aide III',
            'approval_status' => User::APPROVAL_PENDING,
            'is_active' => true,
        ]);
    }
}
