<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Circle Pintar Backend
|--------------------------------------------------------------------------
|
| Prefix  : /api
| Auth    : Laravel Sanctum (Bearer Token)
| RBAC    : Middleware 'role' (EnsureRole) — role:super_admin,teacher,student
|
| ┌─────────────────────────────────────────────────────────────────────┐
| │ [Public]       POST  /api/auth/register                             │
| │ [Public]       POST  /api/auth/login                                │
| ├─────────────────────────────────────────────────────────────────────┤
| │ [All Auth]     POST  /api/auth/logout                               │
| │ [All Auth]     GET   /api/auth/me                                   │
| │ [All Auth]     GET   /api/categories                                │
| │ [All Auth]     GET   /api/courses                                   │
| │ [All Auth]     GET   /api/courses/{course}                          │
| │ [All Auth]     GET   /api/badges                                    │
| ├─────────────────────────────────────────────────────────────────────┤
| │ [Admin+Teacher] CRUD  /api/categories                               │
| │ [Admin+Teacher] CRUD  /api/courses                                  │
| │ [Admin+Teacher] CRUD  /api/modules                                  │
| │ [Admin+Teacher] CRUD  /api/materials                                │
| │ [Admin+Teacher] CRUD  /api/quizzes & /api/questions                 │
| │ [Admin+Teacher] GET   /api/courses/{course}/students                │
| │ [Admin+Teacher] CRUD  /api/badges                                   │
| ├─────────────────────────────────────────────────────────────────────┤
| │ [Student Only] POST   /api/courses/{course}/enroll                  │
| │ [Student Only] DELETE /api/courses/{course}/enroll                  │
| │ [Student Only] GET    /api/my-courses                               │
| │ [Student Only] GET    /api/my-progress                              │
| │ [Student Only] POST   /api/materials/{material}/progress            │
| │ [Student Only] POST   /api/quizzes/{quiz}/submit                    │
| │ [Student Only] GET    /api/my-attempts                              │
| │ [Student Only] GET    /api/my-badges                                │
| └─────────────────────────────────────────────────────────────────────┘
|
*/

// ─── [PUBLIC] Tidak perlu login ──────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login',    [AuthController::class, 'login'])->name('login');
});

// ─── [PROTECTED] Perlu Bearer Token ─────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ── [All Roles] Auth utility ─────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me',      [AuthController::class, 'me'])->name('me');
    });

    // ── [All Roles] Baca kategori & kelas ────────────────────────────────────
    Route::get('categories',        [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('courses',           [CourseController::class, 'index'])->name('courses.index');
    Route::get('courses/{course}',  [CourseController::class, 'show'])->name('courses.show');
    Route::get('badges',            [BadgeController::class, 'index'])->name('badges.index');
    Route::get('badges/{badge}',    [BadgeController::class, 'show'])->name('badges.show');

    // ── [Super Admin Only] Kelola Sistem & Pengguna ──────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // ── [Admin + Teacher] Kelola konten ──────────────────────────────────────
    Route::middleware('role:super_admin,teacher')->group(function () {

        // Categories — hanya super admin & teacher yang bisa CRUD
        Route::post('categories',                [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}',      [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}',   [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Courses — create, update, delete
        Route::post('courses',                   [CourseController::class, 'store'])->name('courses.store');
        Route::put('courses/{course}',           [CourseController::class, 'update'])->name('courses.update');
        Route::delete('courses/{course}',        [CourseController::class, 'destroy'])->name('courses.destroy');

        // Modules — nested di course (shallow: update/delete langsung via /modules/{module})
        Route::apiResource('courses.modules', ModuleController::class)
            ->except(['index', 'show'])
            ->shallow();

        // GET modules (baca oleh semua login — dipindah ke All Roles)
        // Materials — nested di module (shallow)
        // PUT  /api/materials/{material}        → update via JSON (tanpa file upload)
        // POST /api/materials/{material}        → update via multipart/form-data + _method=PUT
        //      (diperlukan karena PHP tidak bisa baca multipart body pada PUT/PATCH)
        Route::apiResource('courses.modules.materials', MaterialController::class)
            ->except(['index', 'show'])
            ->shallow();

        // FIX: Route alternatif POST untuk update material dengan file upload (method spoofing)
        // Cara penggunaan: kirim POST multipart/form-data + field `_method = PUT` di body
        Route::post('materials/{material}', [MaterialController::class, 'update'])
            ->name('materials.update.post');

        // Dashboard Stats
        Route::get('dashboard/stats', [DashboardController::class, 'stats']);

        // List siswa per kelas
        Route::get('courses/{course}/students', [EnrollmentController::class, 'courseStudents'])
            ->name('courses.students');

        // Quizzes — CRUD oleh teacher/admin
        Route::get('courses/{course}/quizzes',  [QuizController::class, 'index'])->name('courses.quizzes.index');
        Route::post('courses/{course}/quizzes', [QuizController::class, 'store'])->name('courses.quizzes.store');
        Route::put('quizzes/{quiz}',            [QuizController::class, 'update'])->name('quizzes.update');
        Route::delete('quizzes/{quiz}',         [QuizController::class, 'destroy'])->name('quizzes.destroy');

        // Questions — CRUD oleh teacher/admin
        Route::get('quizzes/{quiz}/questions',  [QuestionController::class, 'index'])->name('quizzes.questions.index');
        Route::post('quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('quizzes.questions.store');
        Route::put('questions/{question}',      [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('questions/{question}',   [QuestionController::class, 'destroy'])->name('questions.destroy');

        // Badges — CRUD oleh teacher/admin
        Route::post('badges',           [BadgeController::class, 'store'])->name('badges.store');
        Route::put('badges/{badge}',    [BadgeController::class, 'update'])->name('badges.update');
        Route::delete('badges/{badge}', [BadgeController::class, 'destroy'])->name('badges.destroy');
    });

    // ── [All Auth] Baca modul & materi (teacher perlu lihat juga) ────────────
    Route::get('courses/{course}/modules',                               [ModuleController::class, 'index'])->name('courses.modules.index');
    Route::get('modules/{module}',                                       [ModuleController::class, 'show'])->name('modules.show');
    Route::get('courses/{course}/modules/{module}/materials',            [MaterialController::class, 'index'])->name('courses.modules.materials.index');
    Route::get('materials/{material}',                                   [MaterialController::class, 'show'])->name('materials.show');
    Route::get('quizzes/{quiz}',                                         [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('questions/{question}',                                   [QuestionController::class, 'show'])->name('questions.show');

    // ── [Student Only] Enrollment & aktivitas belajar ────────────────────────
    Route::middleware('role:student')->group(function () {

        // Enrollment
        Route::post('courses/{course}/enroll',   [EnrollmentController::class, 'enroll'])->name('courses.enroll');
        Route::delete('courses/{course}/enroll', [EnrollmentController::class, 'unenroll'])->name('courses.unenroll');
        Route::get('my-courses',                 [EnrollmentController::class, 'myCourses'])->name('my-courses');

        // Progress belajar
        Route::get('my-progress',                            [ProgressController::class, 'myProgress'])->name('my-progress');
        Route::post('materials/{material}/progress',         [ProgressController::class, 'markProgress'])->name('materials.progress');
        Route::get('courses/{course}/progress',              [ProgressController::class, 'courseProgress'])->name('courses.progress');

        // Quiz attempts (submit & riwayat nilai)
        Route::post('quizzes/{quiz}/submit', [QuizAttemptController::class, 'submit'])->name('quizzes.submit');
        Route::get('quizzes/{quiz}/attempts',[QuizAttemptController::class, 'quizAttempts'])->name('quizzes.attempts');
        Route::get('my-attempts',            [QuizAttemptController::class, 'myAttempts'])->name('my-attempts');

        // Badge milik siswa
        Route::get('my-badges', [BadgeController::class, 'myBadges'])->name('my-badges');
    });
});


