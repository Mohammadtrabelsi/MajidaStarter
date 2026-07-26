<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserIsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function handleFor(?User $user): Response
    {
        $request = Request::create('/admin');
        $request->setUserResolver(fn () => $user);

        return (new EnsureUserIsAdmin)->handle($request, fn () => new Response('passed'));
    }

    public function test_admin_request_passes_through(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->handleFor($admin);

        $this->assertSame('passed', $response->getContent());
    }

    public function test_non_admin_request_is_forbidden(): void
    {
        $user = User::factory()->create();

        try {
            $this->handleFor($user);
            $this->fail('Expected a 403 HttpException.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_guest_request_is_forbidden(): void
    {
        // The null-safe operator on a missing user must resolve to a 403,
        // never a "method on null" fatal.
        try {
            $this->handleFor(null);
            $this->fail('Expected a 403 HttpException.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }
}
