<?php

namespace Tests\Feature\Services;

use App\Exceptions\DomainActionException;
use App\Models\User;
use App\Services\UserService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->service = app(UserService::class);
    }

    public function test_register_hashes_password_and_dispatches_registered_event(): void
    {
        Event::fake();

        $user = $this->service->register([
            'name' => 'Reg User',
            'email' => 'reg@example.com',
            'password' => 'plain-password',
        ]);

        $this->assertTrue(Hash::check('plain-password', $user->password));
        $this->assertNotSame('plain-password', $user->password);
        Event::assertDispatched(Registered::class);
    }

    public function test_create_hashes_password_and_syncs_roles(): void
    {
        $user = $this->service->create([
            'name' => 'Made User',
            'email' => 'made@example.com',
            'password' => 'secret-pass',
        ], ['admin']);

        $this->assertTrue(Hash::check('secret-pass', $user->password));
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_create_without_roles_leaves_user_role_less(): void
    {
        $user = $this->service->create([
            'name' => 'Plain User',
            'email' => 'plain@example.com',
            'password' => 'secret-pass',
        ]);

        $this->assertCount(0, $user->roles);
    }

    public function test_update_user_changing_email_resets_verification(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        $this->assertNotNull($user->email_verified_at);

        $this->service->updateUser($user, [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_update_user_keeps_verification_when_email_unchanged(): void
    {
        $user = User::factory()->create(['email' => 'same@example.com']);

        $this->service->updateUser($user, [
            'name' => 'Renamed',
            'email' => 'same@example.com',
        ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame('Renamed', $user->fresh()->name);
    }

    public function test_update_user_only_rehashes_password_when_provided(): void
    {
        $user = User::factory()->create();
        $originalHash = $user->password;

        // Empty password must not overwrite the existing hash.
        $this->service->updateUser($user, [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
        ]);
        $this->assertSame($originalHash, $user->fresh()->password);

        // A non-empty password is hashed and stored.
        $this->service->updateUser($user, [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'brand-new-pass',
        ]);
        $this->assertTrue(Hash::check('brand-new-pass', $user->fresh()->password));
    }

    public function test_toggle_admin_role_assigns_then_removes(): void
    {
        $actor = User::factory()->admin()->create();
        $target = User::factory()->create();

        $this->service->toggleAdminRole($actor, $target);
        $this->assertTrue($target->fresh()->hasRole('admin'));

        $this->service->toggleAdminRole($actor, $target->fresh());
        $this->assertFalse($target->fresh()->hasRole('admin'));
    }

    public function test_toggle_admin_role_rejects_self(): void
    {
        $actor = User::factory()->admin()->create();

        $this->expectException(DomainActionException::class);
        $this->expectExceptionMessage('You cannot change your own admin status.');

        $this->service->toggleAdminRole($actor, $actor);
    }

    public function test_delete_rejects_self_and_keeps_the_record(): void
    {
        $actor = User::factory()->create();

        try {
            $this->service->delete($actor, $actor);
            $this->fail('Expected DomainActionException was not thrown.');
        } catch (DomainActionException $e) {
            $this->assertSame('You cannot delete your own account here.', $e->getMessage());
        }

        $this->assertNotNull($actor->fresh());
    }

    public function test_delete_removes_a_different_user(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->service->delete($actor, $target);

        $this->assertModelMissing($target);
    }

    public function test_update_profile_resets_verification_only_on_email_change(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->service->updateProfile($user, ['name' => 'Fresh Name', 'email' => 'me@example.com']);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame('Fresh Name', $user->fresh()->name);

        $this->service->updateProfile($user, ['name' => 'Fresh Name', 'email' => 'moved@example.com']);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_update_password_hashes_the_new_password(): void
    {
        $user = User::factory()->create();

        $this->service->updatePassword($user, 'totally-new');

        $this->assertTrue(Hash::check('totally-new', $user->fresh()->password));
    }

    public function test_delete_own_account_logs_out_and_deletes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $this->service->deleteOwnAccount($user);

        $this->assertGuest();
        $this->assertModelMissing($user);
    }

    public function test_role_names_are_returned_sorted(): void
    {
        // Seeder creates the "admin" role; add more to prove ordering.
        Role::findOrCreate('editor', 'web');
        Role::findOrCreate('author', 'web');

        $names = $this->service->roleNames();

        $sorted = $names;
        sort($sorted);
        $this->assertSame($sorted, $names);
        $this->assertContains('admin', $names);
        $this->assertContains('editor', $names);
    }

    public function test_search_paginated_returns_all_when_search_is_null(): void
    {
        User::factory()->count(3)->create();

        $results = $this->service->searchPaginated(null);

        $this->assertSame(3, $results->total());
    }

    public function test_search_paginated_matches_name_and_email(): void
    {
        User::factory()->create(['name' => 'Findable Person', 'email' => 'a@example.com']);
        User::factory()->create(['name' => 'Other', 'email' => 'needle@example.com']);
        User::factory()->create(['name' => 'Nothing', 'email' => 'z@example.com']);

        $this->assertSame(1, $this->service->searchPaginated('Findable')->total());
        $this->assertSame(1, $this->service->searchPaginated('needle')->total());
    }

    public function test_search_paginated_respects_per_page(): void
    {
        User::factory()->count(5)->create();

        $results = $this->service->searchPaginated(null, 2);

        $this->assertSame(2, $results->perPage());
        $this->assertSame(2, $results->count());
        $this->assertSame(5, $results->total());
    }

    public function test_stats_returns_zeroes_when_no_users_exist(): void
    {
        $stats = $this->service->stats();

        $this->assertSame(['total' => 0, 'admins' => 0, 'newThisWeek' => 0, 'newToday' => 0], $stats);
    }

    public function test_stats_counts_totals_admins_and_recent_windows(): void
    {
        User::factory()->admin()->create(['created_at' => now()]);            // today + week
        User::factory()->create(['created_at' => now()->subDays(3)]);         // week only
        User::factory()->create(['created_at' => now()->subDays(10)]);        // neither window

        $stats = $this->service->stats();

        $this->assertSame(3, $stats['total']);
        $this->assertSame(1, $stats['admins']);
        $this->assertSame(2, $stats['newThisWeek']);
        $this->assertSame(1, $stats['newToday']);
    }
}
