<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_upload_avatar(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->studentProfile()->create();

        $this->actingAs($user)
            ->put('/student/profile', [
                'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
            ])
            ->assertRedirect();

        // The photo is stored as a permanent data URI in the database, not a
        // file on an ephemeral disk, so it survives deploys.
        $data = $user->studentProfile->refresh()->avatar_data;
        $this->assertNotNull($data);
        $this->assertStringStartsWith('data:image/', $data);

        $this->actingAs($user)
            ->get('/student/profile')
            ->assertOk()
            ->assertSee('data:image/', false);
    }

    public function test_student_can_remove_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'student']);
        $profile = $user->studentProfile()->create([
            'avatar' => 'avatars/old.jpg',
            'avatar_data' => 'data:image/jpeg;base64,abc123',
        ]);
        Storage::disk('public')->put('avatars/old.jpg', 'data');

        $this->actingAs($user)
            ->put('/student/profile', ['remove_avatar' => '1'])
            ->assertRedirect();

        $profile->refresh();
        $this->assertNull($profile->avatar);
        $this->assertNull($profile->avatar_data);
        Storage::disk('public')->assertMissing('avatars/old.jpg');
    }
}
