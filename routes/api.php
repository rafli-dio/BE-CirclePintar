<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\QuizController;
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

    // --- Enrollment (Pendaftaran Kelas) ---
    Route::get('my-courses', [EnrollmentController::class, 'myCourses'])->name('my-courses');
    Route::post('courses/{course}/enroll', [EnrollmentController::class, 'enroll'])->name('courses.enroll');
    Route::delete('courses/{course}/enroll', [EnrollmentController::class, 'unenroll'])->name('courses.unenroll');
    Route::get('courses/{course}/students', [EnrollmentController::class, 'courseStudents'])->name('courses.students');

    // --- Progress Belajar ---
    Route::get('my-progress', [ProgressController::class, 'myProgress'])->name('my-progress');
    Route::post('materials/{material}/progress', [ProgressController::class, 'markProgress'])->name('materials.progress');
    Route::get('courses/{course}/progress', [ProgressController::class, 'courseProgress'])->name('courses.progress');

    // --- Gamifikasi: Quiz ---
    Route::get('courses/{course}/quizzes', [QuizController::class, 'index'])->name('courses.quizzes.index');
    Route::post('courses/{course}/quizzes', [QuizController::class, 'store'])->name('courses.quizzes.store');
    Route::get('quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::put('quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');

    // --- Gamifikasi: Questions (soal kuis) ---
    Route::get('quizzes/{quiz}/questions', [QuestionController::class, 'index'])->name('quizzes.questions.index');
    Route::post('quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('quizzes.questions.store');
    Route::get('questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
    Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // --- Gamifikasi: Quiz Attempts (submit & riwayat nilai) ---
    Route::post('quizzes/{quiz}/submit', [QuizAttemptController::class, 'submit'])->name('quizzes.submit');
    Route::get('quizzes/{quiz}/attempts', [QuizAttemptController::class, 'quizAttempts'])->name('quizzes.attempts');
    Route::get('my-attempts', [QuizAttemptController::class, 'myAttempts'])->name('my-attempts');

    // --- Gamifikasi: Badges ---
    Route::apiResource('badges', BadgeController::class);
    Route::get('my-badges', [BadgeController::class, 'myBadges'])->name('my-badges');
});
