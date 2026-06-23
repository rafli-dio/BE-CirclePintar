<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Material;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Policies\CoursePolicy;
use App\Policies\MaterialPolicy;
use App\Policies\ModulePolicy;
use App\Policies\QuestionPolicy;
use App\Policies\QuizPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * Daftarkan semua Laravel Policies untuk Data Ownership.
     */
    public function boot(): void
    {
        // Course — guru hanya bisa edit/hapus kelas miliknya sendiri
        Gate::policy(Course::class, CoursePolicy::class);

        // Module — guru hanya bisa edit/hapus modul di kelas miliknya
        Gate::policy(Module::class, ModulePolicy::class);

        // Material — guru hanya bisa edit/hapus materi di kelas miliknya
        Gate::policy(Material::class, MaterialPolicy::class);

        // Quiz — guru hanya bisa edit/hapus kuis di kelas miliknya
        Gate::policy(Quiz::class, QuizPolicy::class);

        // Question — guru hanya bisa edit/hapus soal di kelas miliknya
        Gate::policy(Question::class, QuestionPolicy::class);
    }
}
