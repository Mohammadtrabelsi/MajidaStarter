<?php

namespace Tests\Feature\Exceptions;

use App\Exceptions\DomainActionException;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class DomainActionExceptionTest extends TestCase
{
    public function test_it_extends_the_native_domain_exception(): void
    {
        // Livewire try/catch blocks rely on catching DomainException.
        $this->assertInstanceOf(DomainException::class, new DomainActionException('nope'));
    }

    public function test_render_returns_a_422_json_response_for_json_requests(): void
    {
        $exception = new DomainActionException('Not allowed here.');

        $request = Request::create('/api/x', 'DELETE', server: ['HTTP_ACCEPT' => 'application/json']);

        $response = $exception->render($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['message' => 'Not allowed here.'], $response->getData(true));
    }

    public function test_render_returns_null_for_non_json_requests(): void
    {
        $exception = new DomainActionException('Not allowed here.');

        $request = Request::create('/x', 'GET');

        // A null return lets the framework fall through to the default handler,
        // which is what web/Livewire callers rely on.
        $this->assertNull($exception->render($request));
    }
}
