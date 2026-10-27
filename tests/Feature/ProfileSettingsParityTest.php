<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Every account - student, alumni, guest, parent, admin, registrar,
 * cashier, office and department - must get the same Profile & Account
 * Settings page: photo upload/removal, account information, password
 * change and account deletion. These cases pin the parity down role by
 * role, including the legacy per-office URLs the sidebars used to use.
 */
class ProfileSettingsParityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The four cards of the shared page, in every role's rendering.
     */
    private function assertFullSettingsPage($response): void
    {
        $response
            ->assertOk()
            ->assertSee('Account & Password Settings', false)
            ->assertSee('Profile Picture')
            ->assertSee('Account Information')
            ->assertSee('Security')
            ->assertSee('Danger Zone');
    }

    public function test_every_role_gets_the_full_settings_page(): void
    {
        $cases = [
            'admin' => ['role' => 'admin'],
            'registrar' => ['role' => 'registrar'],
            'cashier' => ['role' => 'cashier'],
            'department' => ['role' => 'department'],
            'office' => ['role' => 'office'],
            'ssc' => ['role' => 'ssc'],
            'accounting' => ['role' => 'accounting'],
            'library' => ['role' => 'library'],
            'student' => ['role' => 'student'],
            'alumni' => ['role' => 'student', 'registration_type' => 'alumni'],
            'guest' => ['role' => 'student', 'registration_type' => 'guest'],
            'parent' => ['role' => 'student', 'registration_type' => 'parent'],
            'guard' => ['role' => 'guard', 'office' => 'Main Gate'],
        ];

        foreach ($cases as $label => $attributes) {
            $user = User::factory()->create($attributes);

            $this->assertFullSettingsPage(
                $this->actingAs($user)->get('/profile')
            );

            $this->actingAs($user)->flushSession();

            $user->delete();
        }
    }

    /**
     * The old per-office URLs stay in bookmarks and printed pages; they
     * must land on the same full page instead of the deleted read-only
     * screens.
     */
    public function test_the_legacy_office_urls_reach_the_full_page(): void
    {
        $cases = [
            '/cici/profile' => ['role' => 'department', 'office' => 'CICI'],
            '/cbmsd/profile' => ['role' => 'department', 'office' => 'CBMSD'],
            '/coag/profile' => ['role' => 'department', 'office' => 'COAG'],
            '/coed/profile' => ['role' => 'department', 'office' => 'COED'],
            '/ssc/profile' => ['role' => 'ssc'],
            '/accounting/profile' => ['role' => 'accounting'],
            '/library/profile' => ['role' => 'library'],
            '/workspace/profile' => ['role' => 'office', 'office' => 'Registrar Extension'],
            '/alumni/profile' => ['role' => 'student', 'registration_type' => 'alumni'],
            '/student/profile' => ['role' => 'student'],
        ];

        foreach ($cases as $url => $attributes) {
            $user = User::factory()->create($attributes);

            $this->assertFullSettingsPage(
                $this->actingAs($user)->get($url)
            );

            $this->actingAs($user)->flushSession();

            $user->delete();
        }
    }

    /**
     * Staff photos live on the user record - they have no student profile
     * to hold one - and must render through the shared card.
     */
    public function test_a_staff_member_can_upload_and_remove_their_photo(): void
    {
        $user = User::factory()->create(['role' => 'registrar']);

        $this->actingAs($user)->put('/profile', [
            'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotNull($user->avatar_data);
        $this->assertStringStartsWith('data:image/', $user->avatar_data);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('data:image/', false);

        $this->actingAs($user)->put('/profile', ['remove_avatar' => 1])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->avatar_data);
    }

    /**
     * Student-role accounts (students, guests, parents, alumni) keep the
     * photo on the student profile, exactly like the old student-only
     * page stored it - their dashboards, QR pass and sidebar read it from
     * there.
     */
    public function test_a_student_role_account_keeps_the_photo_on_the_student_profile(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $profile = $user->studentProfile()->create();

        $this->actingAs($user)->put('/profile', [
            'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $data = $profile->refresh()->avatar_data;
        $this->assertNotNull($data);
        $this->assertStringStartsWith('data:image/', $data);

        $this->actingAs($user)->put('/profile', ['remove_avatar' => 1])
            ->assertSessionHasNoErrors();

        $this->assertNull($profile->refresh()->avatar_data);
    }

    /**
     * Staff used to be unable to edit their name/email anywhere but the
     * bare Breeze form; the same shared endpoint now serves it, with the
     * verification restart the Breeze flow always had.
     */
    public function test_a_staff_member_can_update_name_and_email(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Cashier Cruz',
            'email' => 'cashier.cruz@isufst.edu.ph',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Cashier Cruz', $user->name);
        $this->assertSame('cashier.cruz@isufst.edu.ph', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    /**
     * An untouched photo field must never bounce the save or clear a photo
     * that is already on file - the bug that used to trap students now
     * guards every role.
     */
    public function test_saving_without_re_picking_the_photo_keeps_it(): void
    {
        $user = User::factory()->create(['role' => 'registrar']);
        $user->forceFill(['avatar_data' => 'data:image/jpeg;base64,abc123'])->save();

        $this->actingAs($user)->put('/profile', [
            // exactly what the browser posts for an untouched file input
            'avatar' => new UploadedFile('', '', null, UPLOAD_ERR_NO_FILE, true),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('data:image/jpeg;base64,abc123', $user->fresh()->avatar_data);
    }
}
