<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Office;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminModuleTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_user_management(): void
    {
        User::factory()->create(['role' => 'registrar']);

        $this->actingAs($this->makeAdmin())
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee('registrar');
    }

    public function test_admin_can_create_and_deactivate_user(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'New Registrar',
                'email' => 'newregistrar@isufst.edu.ph',
                'role' => 'registrar',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $user = User::where('email', 'newregistrar@isufst.edu.ph')->first();
        $this->assertNotNull($user);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/toggle")
            ->assertRedirect();

        $this->assertFalse($user->fresh()->is_active);

        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated']);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_can_reset_a_users_password(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create(['role' => 'student', 'password' => 'password123']);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/password", [
                'password' => 'BrandNewPass42',
                'password_confirmation' => 'BrandNewPass42',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertTrue(Hash::check('BrandNewPass42', $user->password));
        $this->assertFalse(Hash::check('password123', $user->password));

        $this->assertDatabaseHas('audit_logs', ['action' => 'user.password_reset']);
    }

    public function test_password_reset_rejects_short_and_mismatched_passwords(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create(['role' => 'student', 'password' => 'password123']);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/password", [
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/password", [
                'password' => 'BrandNewPass42',
                'password_confirmation' => 'DifferentPass42',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.password_reset']);
    }

    public function test_student_cannot_reset_a_password(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $victim = User::factory()->create(['role' => 'student', 'password' => 'password123']);

        $this->actingAs($student)
            ->put("/admin/users/{$victim->id}/password", [
                'password' => 'BrandNewPass42',
                'password_confirmation' => 'BrandNewPass42',
            ])
            ->assertForbidden();

        $this->assertTrue(Hash::check('password123', $victim->fresh()->password));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.password_reset']);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'role' => 'registrar',
            'password' => 'password123',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_manage_departments(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/offices', [
                'name' => 'Office of the Registrar',
                'location' => 'Main Building, Ground Floor',
                'contact' => '(033) 555-1234',
                'hours' => 'Mon - Fri, 8 AM - 5 PM',
                'description' => 'Student records and document issuance.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $office = Office::where('name', 'Office of the Registrar')->first();
        $this->assertNotNull($office);

        $this->actingAs($admin)
            ->put("/admin/offices/{$office->id}", [
                'name' => 'Office of the Registrar',
                'location' => 'Main Building, 1st Floor',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertEquals('Main Building, 1st Floor', $office->fresh()->location);

        $this->actingAs($admin)
            ->get('/admin/offices')
            ->assertOk()
            ->assertSee('Department Settings');

        $this->assertDatabaseHas('audit_logs', ['action' => 'office.created']);
    }

    public function test_admin_can_save_system_configuration(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put('/admin/settings', [
                'institution_name' => 'ISUFST',
                'institution_address' => 'Tiwi, Barotac Nuevo',
                'academic_term' => '2nd Semester, AY 2026-2027',
                'support_email' => 'admin@isufst.edu.ph',
                'support_phone' => '(033) 555-0000',
                'banner_enabled' => '1',
                'banner_text' => 'Offices closed on Aug 25-26.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertEquals('ISUFST', Setting::get('institution_name'));
        $this->assertEquals('true', Setting::get('banner_enabled'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.updated']);
    }

    public function test_admin_can_publish_and_unpublish_broadcast(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/announcements', [
                'title' => 'Enrollment Schedule',
                'office' => 'Registrar',
                'body' => 'Enrollment opens next week.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $announcement = Announcement::where('title', 'Enrollment Schedule')->first();
        $this->assertTrue($announcement->is_published);

        $this->actingAs($admin)
            ->put("/admin/announcements/{$announcement->id}/toggle")
            ->assertRedirect();

        $this->assertFalse($announcement->fresh()->is_published);

        $this->actingAs($admin)
            ->get('/admin/announcements')
            ->assertOk()
            ->assertSee('System Notifications');

        $this->assertDatabaseHas('audit_logs', ['action' => 'announcement.created']);
    }

    public function test_admin_can_view_audit_logs_with_filters(): void
    {
        $admin = $this->makeAdmin();

        AuditLog::create([
            'user_id' => $admin->id,
            'actor_name' => $admin->name,
            'role' => 'admin',
            'action' => 'user.created',
            'description' => 'Created registrar account.',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get('/admin/audits')
            ->assertOk()
            ->assertSee('Master Logs')
            ->assertSee('user.created');

        $this->actingAs($admin)
            ->get('/admin/audits?action=user.created')
            ->assertOk()
            ->assertSee('user.created');
    }

    public function test_student_cannot_access_admin_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        foreach (['/admin/users', '/admin/offices', '/admin/settings', '/admin/announcements', '/admin/audits'] as $url) {
            $this->actingAs($student)
                ->get($url)
                ->assertForbidden();
        }
    }

    public function test_admin_dashboard_shows_system_metrics(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->count(3)->create(['role' => 'student']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Total Accounts');
    }

    public function test_student_dashboard_shows_announcement_banner(): void
    {
        Setting::query()->updateOrCreate(['key' => 'banner_enabled'], ['value' => 'true']);
        Setting::query()->updateOrCreate(['key' => 'banner_text'], ['value' => 'Offices closed on Aug 25-26.']);

        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Offices closed on Aug 25-26.');
    }
}