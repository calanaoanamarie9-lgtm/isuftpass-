<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every office / department portal must be able to reach its own
 * notifications page from the sidebar, and the badge count must stay
 * filtered to the signed-in account.
 *
 * Before this, only the Registrar, Cashier and Student portals had a
 * Notifications link (and routes). A Library or SSC staffer received
 * appointment alerts in the database but had no page to open them on,
 * and /registrar/notifications 403'd them anyway. The sidebar badge was
 * also rendered once on page load and never refreshed.
 */
class OfficeNotificationAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * One staff account per portal that must expose a Notifications page.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function staffPortals(): array
    {
        return [
            'admin' => ['admin', 'admin.notifications.index'],
            'registrar' => ['registrar', 'registrar.notifications.index'],
            'cashier' => ['cashier', 'cashier.notifications.index'],
            'ssc' => ['ssc', 'ssc.notifications.index'],
            'accounting' => ['accounting', 'accounting.notifications.index'],
            'library' => ['library', 'library.notifications.index'],
            'guard' => ['guard', 'guard.notifications.index'],
            'cici' => ['department', 'cici.notifications.index'],
            'cbmsd' => ['department', 'cbmsd.notifications.index'],
            'coag' => ['department', 'coag.notifications.index'],
            'coed' => ['department', 'coed.notifications.index'],
            'workspace office' => ['office', 'workspace.notifications.index'],
        ];
    }

    private function makeStaff(string $role, ?string $office = null): User
    {
        return User::factory()->create([
            'role' => $role,
            'office' => $office,
        ]);
    }

    #[DataProvider('staffPortals')]
    public function test_every_staff_portal_can_open_its_notifications_page(string $role, string $routeName): void
    {
        $office = match ($role) {
            'department' => strtoupper(strtok($routeName, '.')),
            'office' => 'Guidance',
            default => null,
        };

        $staff = $this->makeStaff($role, $office);

        $this->actingAs($staff)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('Notifications');
    }

    /**
     * The sidebar must link every staff portal to its own notifications
     * page, so the desk finds the alerts without typing a URL. The
     * sidebar renders on every page, so the portal's own notifications
     * page is the natural place to assert it.
     */
    #[DataProvider('staffPortals')]
    public function test_the_sidebar_links_every_staff_portal_to_its_notifications_page(string $role, string $routeName): void
    {
        $office = match ($role) {
            'department' => strtoupper(strtok($routeName, '.')),
            'office' => 'Guidance',
            default => null,
        };

        $staff = $this->makeStaff($role, $office);

        $this->actingAs($staff)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee(route($routeName), false)
            ->assertSee('data-notif-badge', false);
    }

    /**
     * Drop one unread database alert on the given account, the same row
     * the badge counts.
     */
    private function giveAlert(User $target, string $title = 'Desk Alert'): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\\Notifications\\RegistrarRequestAlert',
            'notifiable_type' => $target->getMorphClass(),
            'notifiable_id' => $target->id,
            'data' => [
                'title' => $title,
                'message' => 'A new item is waiting at the desk.',
                'url' => '/',
            ],
        ]);
    }

    public function test_the_unread_count_endpoint_answers_with_the_session_users_own_count(): void
    {
        $library = $this->makeStaff('library', 'Library');

        $this->giveAlert($library);

        $this->assertSame(1, $library->unreadNotifications()->count());

        $response = $this->actingAs($library)->getJson(route('notifications.unreadCount'));

        $response->assertOk()
            ->assertExactJson(['count' => 1]);
    }

    public function test_the_unread_count_endpoint_requires_a_signed_in_user(): void
    {
        $this->getJson(route('notifications.unreadCount'))
            ->assertUnauthorized();
    }

    /**
     * The badge must count only the signed-in account's alerts: a Library
     * staffer never sees the Registrar's queue and vice versa, even when
     * both hold unread rows at the same time.
     */
    public function test_the_unread_count_is_filtered_per_office(): void
    {
        $registrar = $this->makeStaff('registrar', 'Registrar');
        $library = $this->makeStaff('library', 'Library');

        $this->giveAlert($registrar, 'Registrar Only');

        $this->actingAs($library)
            ->getJson(route('notifications.unreadCount'))
            ->assertOk()
            ->assertExactJson(['count' => 0]);

        $this->actingAs($registrar)
            ->getJson(route('notifications.unreadCount'))
            ->assertOk()
            ->assertExactJson(['count' => 1]);
    }

    /**
     * The shared notifications page shows only the session user's rows -
     * the same filter the badge applies.
     */
    public function test_the_notifications_page_lists_only_this_accounts_alerts(): void
    {
        $ssc = $this->makeStaff('ssc', 'SSC');

        $other = $this->makeStaff('accounting', 'Accounting');

        $this->giveAlert($ssc, 'New Counseling Request');

        $this->actingAs($ssc)
            ->get(route('ssc.notifications.index'))
            ->assertOk()
            ->assertSee('New Counseling Request')
            ->assertDontSee('Accounting');

        $this->actingAs($other)
            ->get(route('accounting.notifications.index'))
            ->assertOk()
            ->assertDontSee('New Counseling Request');
    }

    /**
     * A student must not reach a staff portal's notifications page, and a
     * guard must not reach the registrar's - the role middleware on each
     * group is what keeps the desks' queues private.
     */
    public function test_each_portal_rejects_a_different_roles_account(): void
    {
        $student = $this->makeStaff('student');
        $guard = $this->makeStaff('guard', 'Main Gate');

        $this->actingAs($student)
            ->get(route('registrar.notifications.index'))
            ->assertForbidden();

        $this->actingAs($guard)
            ->get(route('ssc.notifications.index'))
            ->assertForbidden();

        $this->actingAs($guard)
            ->get(route('library.notifications.index'))
            ->assertForbidden();
    }

    /**
     * Marking everything read from any portal must drop that account's
     * badge to zero without touching another office's queue.
     */
    public function test_marking_all_read_clears_only_this_accounts_badge(): void
    {
        $accounting = $this->makeStaff('accounting', 'Accounting');
        $library = $this->makeStaff('library', 'Library');

        $this->giveAlert($accounting);
        $this->giveAlert($library);

        $this->actingAs($accounting)
            ->put(route('accounting.notifications.readAll'))
            ->assertRedirect();

        $this->assertSame(0, $accounting->unreadNotifications()->count());
        $this->assertSame(1, $library->unreadNotifications()->count());
    }

    /**
     * The sidebar polls this endpoint, so the response must be JSON and
     * must not vary by portal - one URL serves every role.
     */
    public function test_the_poll_endpoint_serves_every_role_from_one_url(): void
    {
        $guard = $this->makeStaff('guard', 'Main Gate');
        $student = $this->makeStaff('student');

        foreach ([$guard, $student] as $account) {
            $this->actingAs($account)
                ->getJson(route('notifications.unreadCount'))
                ->assertOk()
                ->assertExactJson(['count' => 0]);
        }
    }
}
