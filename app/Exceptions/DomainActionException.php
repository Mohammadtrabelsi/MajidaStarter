<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A guard-rail violation in a domain service (e.g. "you cannot delete your own
 * account here"). Extends the native DomainException so existing Livewire
 * try/catch blocks keep working, while adding a single, central mapping to a
 * 422 JSON response for API callers — no per-controller try/catch needed.
 */
class DomainActionException extends DomainException
{
    public function render(Request $request): ?JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return null;
    }
}
