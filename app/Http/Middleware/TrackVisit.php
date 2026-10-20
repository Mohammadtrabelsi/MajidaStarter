<?php

namespace App\Http\Middleware;

use App\Services\VisitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisit
{
    public function __construct(private VisitService $visits) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Record the visit after the response has been sent so tracking never
     * slows down or breaks a page.
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($this->visits->shouldTrack($request, $response)) {
            $this->visits->record($request);
        }
    }
}
