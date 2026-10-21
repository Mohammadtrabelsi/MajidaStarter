<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        activity('export-test')->log('Exported entry');
    }

    public function test_admin_can_export_csv(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/activity-log/export/csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('id,created_at,log_name', $response->streamedContent());
    }

    public function test_admin_can_export_json_with_the_same_filters(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/activity-log/export/json?log=export-test');

        $rows = json_decode($response->streamedContent(), true);

        $this->assertCount(1, $rows);
        $this->assertSame('Exported entry', $rows[0]['description']);
    }

    public function test_members_cannot_export(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/activity-log/export/csv')
            ->assertForbidden();
    }
}
