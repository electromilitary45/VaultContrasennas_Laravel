<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para proteger rutas de super administración
 * 
 * Solo permite acceso a usuarios con rol 'super_admin'
 */
class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        $user = Auth::user();

        // Verificar que el usuario sea super_admin
        if (!$user->isSuperAdmin()) {
            abort(403, 'Solo los super administradores pueden acceder a la configuración del sistema.');
        }

        return $next($request);
    }
}
