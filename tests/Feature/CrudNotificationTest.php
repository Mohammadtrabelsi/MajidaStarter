<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CrudActionNotification;
use App\Services\SettingService;
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
        $actor = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        Notification::fake();

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
        config(['crud_notifications.models' => []]);

        User::factory()->admin()->create();

        Notification::fake();

        Category::factory()->create();

        Notification::assertNothingSent();
    }

    public function test_admins_are_notified_when_a_category_is_restored(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $category->delete();

        Notification::fake();

        $category->restore();

        Notification::assertSentToTimes($admin, CrudActionNotification::class, 1);
        Notification::assertSentTo(
            $admin,
            CrudActionNotification::class,
            fn (CrudActionNotification $notification) => $notification->payload['event'] === 'restored'
                && $notification->payload['model_id'] === $category->id
                && $notification->payload['type'] === 'success'
        );
    }

    public function test_admins_are_notified_when_a_user_is_created_updated_and_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        Notification::fake();

        $member->update(['name' => 'Renamed Member']);
        $member->delete();

        Notification::assertSentTo(
            $admin,
            CrudActionNotification::class,
            fn (CrudActionNotification $notification) => $notification->payload['event'] === 'updated'
                && $notification->payload['model_type'] === User::class
                && $notification->payload['action_url'] !== null
        );

        Notification::assertSentTo(
            $admin,
            CrudActionNotification::class,
            fn (CrudActionNotification $notification) => $notification->payload['event'] === 'deleted'
                && $notification->payload['model_type'] === User::class
        );
    }

    public function test_admins_are_notified_when_settings_are_updated(): void
    {
        $admin = User::factory()->admin()->create();
        $setting = app(SettingService::class)->current();

        Notification::fake();

        app(SettingService::class)->update($setting, ['maintenance_mode' => true]);

        Notification::assertSentTo(
            $admin,
            CrudActionNotification::class,
            fn (CrudActionNotification $notification) => $notification->payload['event'] === 'updated'
                && $notification->payload['model_type'] === Setting::class
                && $notification->payload['action_url'] === route('admin.settings')
        );
    }

    public function test_remember_token_rotation_does_not_notify_admins(): void
    {
        User::factory()->admin()->create();
        $member = User::factory()->create();

        Notification::fake();

        $member->forceFill(['remember_token' => 'rotated'])->save();

        Notification::assertNothingSent();
    }

    public function test_the_message_is_translated_for_the_acting_locale(): void
    {
        $admin = User::factory()->admin()->create();

        app()->setLocale('fr');
        Category::factory()->create(['name' => ['fr' => 'Voyages']]);

        $stored = $admin->fresh()->notifications()
            ->get()
            ->first(fn ($notification) => $notification->data['model_type'] === Category::class);

        $this->assertSame(
            __('notifications.messages.created', ['label' => __('notifications.resources.category'), 'name' => 'Voyages'], 'fr'),
            $stored->data['message'],
        );
    }
}
