<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para rutas exclusivas de super admin de plataforma.
 *
 * Solo permite acceso a usuarios con rol super_admin y organization_id NULL
 * (super admin de plataforma, no de organización).
 */
class PlatformSuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        $user = Auth::user();

        if (!$user->isPlatformSuperAdmin()) {
            abort(403, 'Solo el super administrador de plataforma puede acceder a esta sección.');
        }

        return $next($request);
    }
}
