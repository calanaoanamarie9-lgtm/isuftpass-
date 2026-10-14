<?php

namespace Tests\Feature\Student;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Same signup path a real student takes; registering creates the
     * student_profiles row this controller edits. The signup form leaves
     * `role` to the database default, so the in-memory instance needs it
     * spelled out before the role:student middleware will let it through.
     */
    private function student(string $email = 'jane@example.com'): User
    {
        $this->post('/register', [
            'first_name' => 'Jane',
            'middle_name' => 'M',
            'last_name' => 'Student',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        $user = auth()->user();
        $user->forceFill(['email_verified_at' => now(), 'role' => User::ROLE_STUDENT])->save();

        return $user;
    }

    /**
     * The profile form posts multipart, so an untouched photo field still
     * arrives as an empty upload (UPLOAD_ERR_NO_FILE) that the request
     * reports as null. The old `nullable|image` rules still failed that
     * with "The avatar failed to upload." - every profile save bounced
     * unless a new photo was picked. The untouched field must stay
     * optional.
     */
    public function test_saving_without_re_picking_the_photo_succeeds(): void
    {
        $user = $this->student();

        $this->actingAs($user)->put('/student/profile', [
            'student_id' => '2024-12345',
            'course' => 'BSIS',
            'year_level' => '2nd Year',
            'contact_number' => '09171234567',
            'address' => 'Barotac Nuevo, Iloilo',
            // exactly what the browser posts for an untouched file input
            'avatar' => new UploadedFile('', '', null, UPLOAD_ERR_NO_FILE, true),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $user->id,
            'student_id' => '2024-12345',
            'contact_number' => '09171234567',
        ]);
    }

    /**
     * Saving must not disturb a photo that is already on file when the
     * field is left untouched.
     */
    public function test_an_untouched_photo_field_keeps_the_existing_photo(): void
    {
        Storage::fake('public');

        $user = $this->student('keep-photo@example.com');
        $user->studentProfile->update(['avatar' => 'avatars/existing.jpg']);

        $this->actingAs($user)->put('/student/profile', [
            'contact_number' => '09171234567',
            'avatar' => new UploadedFile('', '', null, UPLOAD_ERR_NO_FILE, true),
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            'avatars/existing.jpg',
            $user->studentProfile->refresh()->avatar,
            'an untouched photo field must not clear the stored photo'
        );
    }

    public function test_a_new_photo_is_stored_and_can_be_removed(): void
    {
        Storage::fake('public');

        $user = $this->student('photo-owner@example.com');

        $this->actingAs($user)->put('/student/profile', [
            'contact_number' => '09171234567',
            'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
        ])->assertSessionHasNoErrors();

        $data = $user->studentProfile->refresh()->avatar_data;
        $this->assertNotNull($data);
        $this->assertStringStartsWith('data:image/', $data);

        $this->actingAs($user)->put('/student/profile', [
            'contact_number' => '09171234567',
            'remove_avatar' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertNull($user->studentProfile->refresh()->avatar_data);
    }

    /**
     * A non-image can never be stored - the rule must still reject it and
     * leave the record untouched.
     */
    public function test_a_non_image_photo_is_rejected(): void
    {
        Storage::fake('public');

        $user = $this->student('not-an-image@example.com');

        $this->actingAs($user)->put('/student/profile', [
            'contact_number' => '09171234567',
            'avatar' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('avatar');

        $this->assertNull($user->studentProfile->refresh()->avatar);
    }
}
