<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if (! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account has been deactivated. Contact the Super Admin.']);
        }

        if (! $user->canAccessAdmin()) {
            abort(403, 'You do not have access to the admin panel.');
        }

        if ($user->must_change_password && ! $request->routeIs('admin.password.force*')) {
            return redirect()->route('admin.password.force');
        }

        return $next($request);
    }
}
