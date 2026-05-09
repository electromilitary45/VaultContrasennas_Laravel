<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirige a "cambiar contraseña obligatorio" si el usuario tiene must_change_password.
 * No aplica a las rutas de cambio obligatorio ni a logout.
 */
class EnsurePasswordChangedMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->hasMustChangePassword()) {
            return $next($request);
        }

        if ($request->routeIs('password.change-required') || $request->routeIs('password.change-required.store')) {
            return $next($request);
        }

        if ($request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('password.change-required');
    }
}
