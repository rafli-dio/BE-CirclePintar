<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias middleware untuk Role-Based Access Control (RBAC)
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // FIX: Aktifkan method spoofing untuk semua request (termasuk API).
        // Ini memungkinkan frontend mengirim POST multipart/form-data
        // dengan field tambahan `_method = PUT` agar Laravel memperlakukannya sebagai PUT.
        // Diperlukan karena PHP tidak bisa membaca body multipart/form-data
        // ketika HTTP method adalah PUT atau PATCH.
        $middleware->validateCsrfTokens(except: ['api/*']);
        $middleware->convertEmptyStringsToNull();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON 403 saat policy/gate menolak akses
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.',
                ], 403);
            }
        });

        // Return JSON 401 saat tidak login (menghindari error "Route [login] not defined")
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Token tidak valid atau sudah kadaluarsa.',
                ], 401);
            }
        });
    })->create();
