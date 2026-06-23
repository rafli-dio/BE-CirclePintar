<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    /**
     * Super admin selalu diizinkan untuk semua aksi.
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Hanya guru pemilik kelas induk yang boleh update kuis.
     * Kepemilikan ditelusuri: kuis → kelas → user_id.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->course->user_id;
    }

    /**
     * Hanya guru pemilik kelas induk yang boleh hapus kuis.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->course->user_id;
    }

    /**
     * Hanya guru pemilik kelas induk yang boleh menambahkan soal ke kuis ini.
     */
    public function addQuestion(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->course->user_id;
    }
}
