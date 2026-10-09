<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes: ->middleware('permission:manage-news')
 * Multiple allowed permissions: ->middleware('permission:manage-news,publish-news')
 * (passes if the user has ANY of the listed permissions)
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            abort(403, 'Your account has been deactivated.');
        }

        if (! $user->hasAnyPermission($permissions)) {
            Log::warning('Admin permission denied', [
                'user_id' => $user->id,
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
