<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Creates a category as $actor, so every other admin gets a notification.
     * Admins are created before this runs: creating an admin while another
     * admin exists would notify that admin about the user creation too.
     */
    private function createCategoryAs(User $actor): void
    {
        $this->actingAs($actor);

        Category::factory()->create();
    }

    private function categoryNotificationFor(User $recipient)
    {
        return $recipient->fresh()->notifications
            ->first(fn ($notification) => $notification->data['model_type'] === Category::class);
    }

    public function test_non_admin_cannot_view_notifications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/notifications')->assertForbidden();
    }

    public function test_admin_sees_their_own_notifications(): void
    {
        $actor = User::factory()->admin()->create();
        $admin = User::factory()->admin()->create();

        $this->createCategoryAs($actor);

        $this->actingAs($admin)
            ->get('/admin/notifications')
            ->assertOk()
            ->assertSee(__('notifications.titles.created', ['label' => __('notifications.resources.category')]));
    }

    public function test_the_topbar_bell_shows_the_unread_count(): void
    {
        $actor = User::factory()->admin()->create();
        $admin = User::factory()->admin()->create();

        $this->createCategoryAs($actor);

        $this->actingAs($admin)
            ->get('/admin/notifications')
            ->assertSee('btn-badge', escape: false);
    }

    public function test_admin_can_mark_a_notification_as_read(): void
    {
        $actor = User::factory()->admin()->create();
        $admin = User::factory()->admin()->create();

        $this->createCategoryAs($actor);

        $notification = $this->categoryNotificationFor($admin);

        Livewire::actingAs($admin)
            ->test('pages::admin.notifications.index')
            ->call('markAsRead', $notification->id);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $actor = User::factory()->admin()->create();
        $admin = User::factory()->admin()->create();

        $this->createCategoryAs($actor);
        $this->createCategoryAs($actor);

        $this->assertSame(2, $admin->fresh()->unreadNotifications()->count());

        Livewire::actingAs($admin)
            ->test('pages::admin.notifications.index')
            ->call('markAllAsRead');

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_an_admin_cannot_mark_another_admins_notification_as_read(): void
    {
        $actor = User::factory()->admin()->create();
        $owner = User::factory()->admin()->create();
        $intruder = User::factory()->admin()->create();

        $this->createCategoryAs($actor);

        $notification = $this->categoryNotificationFor($owner);

        rescue(fn () => Livewire::actingAs($intruder)
            ->test('pages::admin.notifications.index')
            ->call('markAsRead', $notification->id), report: false);

        $this->assertNull($notification->fresh()->read_at);
    }
}
