<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Turns two-factor on for a user through the real setup flow.
     *
     * @return array<int, string> Plain recovery codes.
     */
    private function enableTwoFactor(User $user): array
    {
        $twoFactor = app(TwoFactorService::class);
        $secret = $twoFactor->beginSetup($user);

        return $twoFactor->confirm($user->fresh(), $twoFactor->totp($secret, time()));
    }

    public function test_totp_matches_the_rfc_6238_reference_vector(): void
    {
        // RFC 6238 SHA-1 test secret "12345678901234567890", T = 59 -> 8-digit 94287082, 6-digit 287082.
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertSame('287082', app(TwoFactorService::class)->totp($secret, 59));
    }

    public function test_setup_is_not_enforced_until_confirmed(): void
    {
        $user = User::factory()->create();

        app(TwoFactorService::class)->beginSetup($user);

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_confirming_with_a_wrong_code_does_not_enable(): void
    {
        $user = User::factory()->create();
        app(TwoFactorService::class)->beginSetup($user);

        $this->assertNull(app(TwoFactorService::class)->confirm($user->fresh(), '000000'));
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_confirming_issues_eight_recovery_codes(): void
    {
        $user = User::factory()->create();

        $codes = $this->enableTwoFactor($user);

        $this->assertCount(8, $codes);
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
        $this->assertCount(8, $user->fresh()->two_factor_recovery_codes);
    }

    public function test_recovery_code_works_only_once(): void
    {
        $user = User::factory()->create();
        $codes = $this->enableTwoFactor($user);
        $twoFactor = app(TwoFactorService::class);

        $this->assertTrue($twoFactor->verifyChallenge($user->fresh(), $codes[0]));
        $this->assertFalse($twoFactor->verifyChallenge($user->fresh(), $codes[0]));
    }

    public function test_api_login_requires_a_valid_code_when_enabled(): void
    {
        $user = User::factory()->create(['email' => 'mfa@example.com', 'password' => 'secret-pass']);
        $this->enableTwoFactor($user);
        $twoFactor = app(TwoFactorService::class);

        $this->postJson('/api/auth/login', ['email' => 'mfa@example.com', 'password' => 'secret-pass'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('two_factor_code');

        $this->postJson('/api/auth/login', [
            'email' => 'mfa@example.com',
            'password' => 'secret-pass',
            'two_factor_code' => $twoFactor->totp($user->fresh()->two_factor_secret, time()),
        ])->assertOk();
    }

    public function test_disabling_removes_the_second_factor(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactor($user);

        app(TwoFactorService::class)->disable($user->fresh());

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_challenge_page_redirects_to_login_without_pending_sign_in(): void
    {
        Livewire::test('pages::auth.two-factor-challenge')
            ->assertRedirect(route('login'));
    }

    public function test_login_with_two_factor_completes_through_the_challenge(): void
    {
        $user = User::factory()->create(['email' => 'challenge@example.com', 'password' => 'secret-pass']);
        $this->enableTwoFactor($user);
        $twoFactor = app(TwoFactorService::class);

        Livewire::test('pages::auth.login')
            ->set('email', 'challenge@example.com')
            ->set('password', 'secret-pass')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertGuest();

        Livewire::test('pages::auth.two-factor-challenge')
            ->set('code', $twoFactor->totp($user->fresh()->two_factor_secret, time()))
            ->call('verify')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }
}
