<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrar alias para los middlewares de admin
        $middleware->alias([
            'extension.cors' => \App\Http\Middleware\ExtensionCorsMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'super-admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'platform-super-admin' => \App\Http\Middleware\PlatformSuperAdminMiddleware::class,
            'org-admin' => \App\Http\Middleware\OrgAdminMiddleware::class,
            'ensure-password-changed' => \App\Http\Middleware\EnsurePasswordChangedMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejar errores 419 (CSRF token expired) para la ruta de 2FA
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('two-factor/login') && $request->isMethod('post')) {
                if ($request->session()->has('login.id')) {
                    $request->session()->regenerateToken();
                    return redirect()->route('two-factor.login')
                        ->withErrors(['code' => 'La sesión expiró. Por favor, intenta ingresar el código nuevamente.'])
                        ->withInput($request->only('code'));
                }
                
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sesión expirada. Por favor, inicia sesión nuevamente.']);
            }
            
            return null;
        });
    })->create();
