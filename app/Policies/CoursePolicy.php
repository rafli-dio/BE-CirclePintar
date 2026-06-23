<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Super admin selalu diizinkan untuk semua aksi.
     * Method ini dipanggil sebelum method policy lain.
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null; // lanjut ke method policy berikutnya
    }

    /**
     * Siapa saja yang boleh membuat kelas baru.
     * (Sudah dibatasi oleh middleware role:super_admin,teacher di route)
     */
    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    /**
     * Hanya guru pemilik kelas (atau super admin via before()) yang boleh update.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->id === $course->user_id;
    }

    /**
     * Hanya guru pemilik kelas (atau super admin via before()) yang boleh hapus.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->id === $course->user_id;
    }

    /**
     * Hanya guru pemilik kelas (atau super admin via before()) yang boleh
     * menambahkan modul baru ke dalam kelas ini.
     */
    public function addModule(User $user, Course $course): bool
    {
        return $user->id === $course->user_id;
    }

    /**
     * Hanya guru pemilik kelas (atau super admin via before()) yang boleh
     * melihat daftar siswa di kelas ini.
     */
    public function viewStudents(User $user, Course $course): bool
    {
        return $user->id === $course->user_id;
    }

    /**
     * Hanya guru pemilik kelas (atau super admin via before()) yang boleh
     * membuat kuis di kelas ini.
     */
    public function addQuiz(User $user, Course $course): bool
    {
        return $user->id === $course->user_id;
    }
}
