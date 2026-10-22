<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes:  RoleMiddleware::class . ':doctor,admin'
 * Blocks anyone whose role is not in the list (students cannot open doctor/admin pages).
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have permission to view this page.');
        }

        return $next($request);
    }
}
