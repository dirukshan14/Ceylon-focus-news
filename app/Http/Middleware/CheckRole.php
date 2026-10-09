<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:super-admin')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->hasRole(...$roles)) {
            Log::warning('Role check denied', ['user_id' => $user->id, 'path' => $request->path(), 'ip' => $request->ip()]);
            abort(403, 'You do not have access to this section.');
        }

        return $next($request);
    }
}
