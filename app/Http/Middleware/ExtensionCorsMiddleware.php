<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CORS para peticiones desde la extensión de Chrome (origen chrome-extension://...).
 * Permite credenciales (cookies de sesión) en las rutas de la API de extensión.
 */
class ExtensionCorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin', '');

        if ($request->isMethod('OPTIONS') && str_starts_with($origin, 'chrome-extension://')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');
        }

        $response = $next($request);

        if (str_starts_with($origin, 'chrome-extension://')) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');
        }

        return $response;
    }
}
