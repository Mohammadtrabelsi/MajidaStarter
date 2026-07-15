<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_view_create_user_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/users/create')->assertOk();
    }

    public function test_non_admin_cannot_view_create_user_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/users/create')->assertForbidden();
    }

    public function test_admin_can_create_a_user_with_roles(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users.create')
            ->set('name', 'New Person')
            ->set('email', 'new-person@example.com')
            ->set('password', 'password123')
            ->set('roles', ['admin'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $created = User::where('email', 'new-person@example.com')->first();

        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('admin'));
    }

    public function test_admin_can_update_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Before']);

        Livewire::actingAs($admin)
            ->test('pages::admin.users.edit', ['user' => $user])
            ->set('name', 'After')
            ->set('roles', ['admin'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $user->refresh();

        $this->assertSame('After', $user->name);
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_admin_can_remove_a_role_from_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users.edit', ['user' => $user])
            ->set('roles', [])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($user->fresh()->hasRole('admin'));
    }

    public function test_admin_can_delete_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.dashboard')
            ->call('deleteUser', $user->id)
            ->assertHasNoErrors();

        $this->assertModelMissing($user);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.dashboard')
            ->call('deleteUser', $admin->id)
            ->assertHasErrors('toggle');

        $this->assertNotNull($admin->fresh());
    }

    public function test_non_admin_cannot_delete_a_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::admin.dashboard')
            ->call('deleteUser', $other->id)
            ->assertForbidden();

        $this->assertNotNull($other->fresh());
    }
}
