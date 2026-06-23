<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialPolicy
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
     * Hanya guru pemilik kelas induk yang boleh update materi.
     * Kepemilikan ditelusuri: materi → modul → kelas → user_id.
     */
    public function update(User $user, Material $material): bool
    {
        return $user->id === $material->module->course->user_id;
    }

    /**
     * Hanya guru pemilik kelas induk yang boleh hapus materi.
     */
    public function delete(User $user, Material $material): bool
    {
        return $user->id === $material->module->course->user_id;
    }
}
