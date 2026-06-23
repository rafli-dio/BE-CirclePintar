<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
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
     * Hanya guru pemilik kelas induk yang boleh update soal.
     * Kepemilikan ditelusuri: soal → kuis → kelas → user_id.
     */
    public function update(User $user, Question $question): bool
    {
        return $user->id === $question->quiz->course->user_id;
    }

    /**
     * Hanya guru pemilik kelas induk yang boleh hapus soal.
     */
    public function delete(User $user, Question $question): bool
    {
        return $user->id === $question->quiz->course->user_id;
    }
}
