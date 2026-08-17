<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/settings/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::settings.profile')
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
    }

    public function test_changing_email_resets_verification_status(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->email_verified_at);

        Livewire::actingAs($user)
            ->test('pages::settings.profile')
            ->set('email', 'new-address@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_email_stays_verified_when_unchanged(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::settings.profile')
            ->set('name', 'Just The Name')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::settings.profile')
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_correct_current_password_is_required_to_update_password(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::settings.profile')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasErrors('current_password');
    }

    public function test_account_can_be_deleted(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::settings.profile')
            ->set('delete_password', 'password')
            ->call('deleteAccount')
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertModelMissing($user);
    }

    public function test_account_is_not_deleted_with_wrong_password(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::settings.profile')
            ->set('delete_password', 'wrong-password')
            ->call('deleteAccount')
            ->assertHasErrors('delete_password');

        $this->assertNotNull($user->fresh());
    }
}
