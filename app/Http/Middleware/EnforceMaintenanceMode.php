<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves a 503 to everyone except administrators while the maintenance_mode
 * setting is on. Sign-in and language switching stay reachable so an admin
 * can still get in.
 */
class EnforceMaintenanceMode
{
    /** @var array<int, string> */
    private const EXEMPT = ['login', 'logout', 'language/*', 'livewire/*', 'two-factor-challenge', 'up'];

    public function __construct(private readonly SettingService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->settings->current()->maintenance_mode
            || $request->is(...self::EXEMPT)
            || $request->user()?->isAdmin()
        ) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'The site is currently in maintenance mode.'], 503);
        }

        return response()->view('maintenance', [], 503);
    }
}
