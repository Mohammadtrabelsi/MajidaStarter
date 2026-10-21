<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function enableMaintenance(bool $enabled = true): void
    {
        $settings = app(SettingService::class);
        $settings->update($settings->current(), ['maintenance_mode' => $enabled]);
    }

    public function test_guests_see_the_maintenance_page(): void
    {
        $this->enableMaintenance();

        $this->get('/blog')
            ->assertStatus(503)
            ->assertSee(__('maintenance.title'));
    }

    public function test_members_are_blocked_and_api_gets_json(): void
    {
        $this->enableMaintenance();

        $this->actingAs(User::factory()->create())->get('/blog')->assertStatus(503);
        $this->getJson('/api/posts')->assertStatus(503)->assertJsonPath('message', 'The site is currently in maintenance mode.');
    }

    public function test_admins_keep_access_during_maintenance(): void
    {
        $this->enableMaintenance();

        $this->actingAs(User::factory()->admin()->create())->get('/blog')->assertOk();
    }

    public function test_login_stays_reachable_during_maintenance(): void
    {
        $this->enableMaintenance();

        $this->get('/login')->assertOk();
    }

    public function test_site_is_open_when_maintenance_is_off(): void
    {
        $this->enableMaintenance(false);

        $this->get('/blog')->assertOk();
    }
}
