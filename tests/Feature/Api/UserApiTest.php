<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_non_admin_cannot_access_user_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/users')->assertForbidden();
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->create();

        $this->actingAs($admin)->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'total']);
    }

    public function test_admin_can_create_a_user_with_roles(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'roles' => ['admin'],
        ])->assertCreated();

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_admin_can_update_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'roles' => [],
        ])->assertOk();

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/users/{$user->id}")
            ->assertNoContent();

        $this->assertModelMissing($user);
    }

    public function test_admin_cannot_delete_their_own_account_here(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->deleteJson("/api/users/{$admin->id}")
            ->assertUnprocessable();

        $this->assertModelExists($admin);
    }

    public function test_admin_can_toggle_admin_role_of_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->postJson("/api/users/{$user->id}/toggle-admin")
            ->assertOk();

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    public function test_admin_cannot_toggle_their_own_admin_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson("/api/users/{$admin->id}/toggle-admin")
            ->assertUnprocessable();
    }

    public function test_admin_can_fetch_role_names(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/api/users/roles')
            ->assertOk()
            ->assertJsonFragment(['admin']);
    }

    public function test_admin_can_fetch_user_stats(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->create();

        $this->actingAs($admin)->getJson('/api/users/stats')
            ->assertOk()
            ->assertJsonStructure(['total', 'admins', 'newThisWeek', 'newToday']);
    }
}
