<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->assertSee('Profile Information')
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
            'osas' => ['role' => 'osas'],
            'accounting' => ['role' => 'accounting'],
            'library' => ['role' => 'library'],
            'guidance' => ['role' => 'guidance'],
            'student' => ['role' => 'student'],
            'alumni' => ['role' => 'student', 'registration_type' => 'alumni'],
            'guest' => ['role' => 'student', 'registration_type' => 'guest'],
            'parent' => ['role' => 'student', 'registration_type' => 'parent'],
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
            '/osas/profile' => ['role' => 'osas'],
            '/accounting/profile' => ['role' => 'accounting'],
            '/library/profile' => ['role' => 'library'],
            '/guidance/profile' => ['role' => 'guidance'],
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
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'registrar']);

        $this->actingAs($user)->put('/profile', [
            'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertStringStartsWith('avatars/', $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('storage/avatars/');

        $this->actingAs($user)->put('/profile', ['remove_avatar' => 1])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->avatar);
    }

    /**
     * Student-role accounts (students, guests, parents, alumni) keep the
     * photo on the student profile, exactly like the old student-only
     * page stored it - their dashboards, QR pass and sidebar read it from
     * there.
     */
    public function test_a_student_role_account_keeps_the_photo_on_the_student_profile(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'student']);
        $profile = $user->studentProfile()->create();

        $this->actingAs($user)->put('/profile', [
            'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $path = $profile->refresh()->avatar;
        $this->assertStringStartsWith('avatars/', $path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($user)->put('/profile', ['remove_avatar' => 1])
            ->assertSessionHasNoErrors();

        $this->assertNull($profile->refresh()->avatar);
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
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'registrar', 'avatar' => 'avatars/existing.jpg']);
        Storage::disk('public')->put('avatars/existing.jpg', 'data');

        $this->actingAs($user)->put('/profile', [
            // exactly what the browser posts for an untouched file input
            'avatar' => new UploadedFile('', '', null, UPLOAD_ERR_NO_FILE, true),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('avatars/existing.jpg', $user->fresh()->avatar);
    }
}
