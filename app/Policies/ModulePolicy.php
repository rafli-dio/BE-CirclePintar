<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy
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
     * Hanya guru pemilik kelas induk yang boleh update modul.
     * Kepemilikan ditelusuri dari modul → kelas → user_id.
     */
    public function update(User $user, Module $module): bool
    {
        return $user->id === $module->course->user_id;
    }

    /**
     * Hanya guru pemilik kelas induk yang boleh hapus modul.
     */
    public function delete(User $user, Module $module): bool
    {
        return $user->id === $module->course->user_id;
    }

    /**
     * Hanya guru pemilik kelas induk yang boleh menambahkan materi ke modul ini.
     */
    public function addMaterial(User $user, Module $module): bool
    {
        return $user->id === $module->course->user_id;
    }
}
