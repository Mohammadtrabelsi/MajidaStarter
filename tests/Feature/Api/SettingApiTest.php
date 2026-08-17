<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/settings')->assertForbidden();
    }

    public function test_admin_can_fetch_current_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/api/settings')
            ->assertOk()
            ->assertJsonStructure(['id', 'site_name']);

        $this->assertSame(1, Setting::count());
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->putJson('/api/settings', [
            'site_name' => ['en' => 'My Site', 'ar' => 'موقعي'],
            'site_description' => ['en' => 'A description'],
            'support_email' => 'support@example.com',
            'maintenance_mode' => true,
        ])->assertOk();

        $setting = Setting::first();
        $this->assertSame('My Site', $setting->getTranslation('site_name', 'en'));
        $this->assertSame('support@example.com', $setting->support_email);
        $this->assertTrue($setting->maintenance_mode);
    }

    public function test_settings_update_rejects_invalid_email(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->putJson('/api/settings', [
            'support_email' => 'not-an-email',
        ])->assertUnprocessable()->assertJsonValidationErrors('support_email');
    }
}
