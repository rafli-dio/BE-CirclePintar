<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Circle Pintar Backend
|--------------------------------------------------------------------------
|
| Prefix  : /api
| Auth    : Laravel Sanctum (Bearer Token)
|
| Struktur:
|   [Public]    POST   /api/auth/register
|   [Public]    POST   /api/auth/login
|   [Auth]      POST   /api/auth/logout
|   [Auth]      GET    /api/auth/me
|
|   [Auth]      GET|POST              /api/categories
|   [Auth]      GET|PUT|DELETE        /api/categories/{category}
|
|   [Auth]      GET|POST              /api/courses
|   [Auth]      GET|PUT|DELETE        /api/courses/{course}
|
|   [Auth]      GET|POST              /api/courses/{course}/modules
|   [Auth]      GET|PUT|DELETE        /api/courses/{course}/modules/{module}
|
|   [Auth]      GET|POST              /api/courses/{course}/modules/{module}/materials
|   [Auth]      GET|PUT|DELETE        /api/courses/{course}/modules/{module}/materials/{material}
|
*/

// ─── Public Routes (Tidak perlu login) ──────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login',    [AuthController::class, 'login'])->name('login');
});

// ─── Protected Routes (Perlu Bearer Token) ──────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // --- Auth ---
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me',      [AuthController::class, 'me'])->name('me');
    });

    // --- Categories ---
    Route::apiResource('categories', CategoryController::class);

    // --- Courses ---
    Route::apiResource('courses', CourseController::class);

    // --- Modules (nested dalam Course) ---
    Route::apiResource('courses.modules', ModuleController::class)
        ->shallow();

    // --- Materials (nested dalam Module) ---
    Route::apiResource('courses.modules.materials', MaterialController::class)
        ->shallow();
});
