<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Notifications\CrudActionNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CrudNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admins_are_notified_when_a_configured_model_is_created(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        $category = Category::factory()->create();

        Notification::assertSentTo(
            $admin,
            CrudActionNotification::class,
            function (CrudActionNotification $notification) use ($category) {
                return $notification->payload['event'] === 'created'
                    && $notification->payload['model_type'] === Category::class
                    && $notification->payload['model_id'] === $category->id
                    && $notification->payload['type'] === 'success'
                    && $notification->payload['action_url'] !== null;
            }
        );
    }

    public function test_admins_are_notified_when_a_configured_model_is_updated_and_deleted(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $category->update(['is_active' => false]);
        $category->delete();

        Notification::assertSentToTimes($admin, CrudActionNotification::class, 3);
    }

    public function test_the_acting_user_is_not_notified_about_their_own_action(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Category::factory()->create();

        Notification::assertNotSentTo($admin, CrudActionNotification::class);
    }

    public function test_a_second_admin_is_still_notified_about_another_admins_action(): void
    {
        Notification::fake();

        $actor = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $this->actingAs($actor);

        Category::factory()->create();

        Notification::assertNotSentTo($actor, CrudActionNotification::class);
        Notification::assertSentTo($other, CrudActionNotification::class);
    }

    public function test_notification_is_persisted_to_the_database(): void
    {
        $admin = User::factory()->admin()->create();

        Category::factory()->create();

        $this->assertDatabaseCount('notifications', 1);

        $stored = $admin->fresh()->notifications()->first();

        $this->assertNotNull($stored);
        $this->assertSame(CrudActionNotification::class, $stored->type);
        $this->assertSame('created', $stored->data['event']);
        $this->assertSame(Category::class, $stored->data['model_type']);
    }

    public function test_unconfigured_models_do_not_notify(): void
    {
        Notification::fake();

        User::factory()->admin()->create();

        User::factory()->create();

        Notification::assertNotSentTo(
            User::whereNotNull('id')->get(),
            CrudActionNotification::class
        );
    }
}
