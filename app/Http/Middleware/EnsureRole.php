<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Middleware RBAC — pastikan user yang sedang login memiliki
     * salah satu dari role yang diizinkan.
     *
     * Penggunaan di route:
     *   Route::middleware(['auth:sanctum', 'role:super_admin'])
     *   Route::middleware(['auth:sanctum', 'role:super_admin,teacher'])
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Satu atau lebih role yang diizinkan (pisahkan dengan koma)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Pastikan user sudah ter-autentikasi (guard sanctum sudah menangani ini,
        // tapi kita tambahkan defensive check agar pesan error tetap konsisten)
        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Cek apakah role user ada di daftar role yang diizinkan
        if (! in_array($user->role, $roles, strict: true)) {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.',
            ], 403);
        }

        return $next($request);
    }
}
