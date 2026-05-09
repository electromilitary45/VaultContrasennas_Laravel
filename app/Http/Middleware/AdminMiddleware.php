<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para proteger rutas de administración
 * 
 * Solo permite acceso a usuarios con rol 'admin' o 'super_admin'
 */
class AdminMiddleware
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
                ->with('error', 'Debes iniciar sesión para acceder al panel de administración.');
        }

        $user = Auth::user();

        // Verificar que el usuario sea admin o super_admin
        if (!$user->isAdmin()) {
            abort(403, 'No tienes permisos para acceder al panel de administración.');
        }

        return $next($request);
    }
}
