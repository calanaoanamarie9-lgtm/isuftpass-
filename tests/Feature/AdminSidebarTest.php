<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin's own sidebar.
 *
 * The System Admin gets seven items of their own, and the pages they open
 * under /admin are the Registrar's own - the policies have always let the
 * admin manage any office's bookings, so the menu and the pages agree.
 */
class AdminSidebarTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_the_admin_sidebar_lists_its_seven_items(): void
    {
        $html = $this->actingAs($this->admin())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Admin Panel', $html);

        foreach ([
            'Dashboard',
            'Appointments Management',
            'Document Requests',
            'QR Scanner &amp; Verification',
            'Student &amp; User Accounts',
            'Transaction History &amp; Reports',
            'System Settings',
        ] as $item) {
            $this->assertStringContainsString($item, $html, 'Missing sidebar item: '.$item);
        }

        // The old admin items are gone from the menu.
        $this->assertStringNotContainsString('System Dashboard', $html);
        $this->assertStringNotContainsString('User Management', $html);
        $this->assertStringNotContainsString('Department Settings', $html);
        $this->assertStringNotContainsString('Slot Availability', $html);
        $this->assertStringNotContainsString('System Documentation', $html);
        $this->assertStringNotContainsString('Master Logs &amp; Audits', $html);
    }

    public function test_the_admin_opens_the_pages_its_menu_points_at(): void
    {
        $admin = $this->admin();

        foreach ([
            '/admin/appointments',
            '/admin/document-requests',
            '/admin/qr',
            '/admin/users',
            '/admin/audits',
            '/admin/settings',
        ] as $path) {
            $this->actingAs($admin)
                ->get($path)
                ->assertOk();
        }
    }

    public function test_the_registrar_still_sees_its_own_menu(): void
    {
        $html = $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->get('/registrar/appointments')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Document Requests', $html);
        $this->assertStringNotContainsString('Admin Panel', $html);
    }
}
