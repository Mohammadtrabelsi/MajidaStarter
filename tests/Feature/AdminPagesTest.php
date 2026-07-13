<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_non_admin_cannot_view_activity_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/activity-log')->assertForbidden();
    }

    public function test_admin_can_view_activity_log(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/activity-log')->assertOk();
    }

    public function test_activity_is_recorded_when_user_updated(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Original Name']);

        $user->update(['name' => 'New Name']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.activity-log')
            ->assertOk();
    }

    public function test_non_admin_cannot_view_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/settings')->assertForbidden();
    }

    public function test_admin_can_view_and_update_settings(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.settings')
            ->set('siteName.en', 'My App')
            ->set('siteName.ar', 'تطبيقي')
            ->set('supportEmail', 'help@example.com')
            ->set('maintenanceMode', true)
            ->call('save')
            ->assertHasNoErrors();

        $setting = Setting::first();

        $this->assertSame('My App', $setting->getTranslation('site_name', 'en'));
        $this->assertSame('تطبيقي', $setting->getTranslation('site_name', 'ar'));
        $this->assertSame('help@example.com', $setting->support_email);
        $this->assertTrue($setting->maintenance_mode);
    }

    public function test_settings_update_is_logged(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.settings')
            ->set('siteName.en', 'Updated Name')
            ->call('save');

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'settings',
        ]);
    }
}
