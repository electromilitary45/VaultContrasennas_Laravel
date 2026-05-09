<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para rutas exclusivas de admin de organización.
 *
 * Solo permite acceso a usuarios con organización (org admin o org super admin).
 * El platform super admin no tiene org y no puede acceder.
 */
class OrgAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        if (!Auth::user()->isOrgAdmin()) {
            abort(403, 'Solo los administradores de organización pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
