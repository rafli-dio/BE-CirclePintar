<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed Super Admin, Teacher, dan Student dummy.
     */
    public function run(): void
    {
        // ─── Super Admin ──────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@circlepintar.id',
            'password' => Hash::make('SuperAdmin@123'),
            'role'     => User::ROLE_SUPER_ADMIN,
            'status'   => User::STATUS_ACTIVE,
            'total_xp' => 0,
        ]);

        // ─── Guru / Teacher ───────────────────────────────────────────────────────
        $teachers = [
            [
                'name'     => 'Budi Santoso',
                'email'    => 'budi.santoso@circlepintar.id',
                'password' => Hash::make('Teacher@123'),
                'role'     => User::ROLE_TEACHER,
                'status'   => User::STATUS_ACTIVE,
                'total_xp' => 0,
            ],
            [
                'name'     => 'Siti Rahayu',
                'email'    => 'siti.rahayu@circlepintar.id',
                'password' => Hash::make('Teacher@123'),
                'role'     => User::ROLE_TEACHER,
                'status'   => User::STATUS_ACTIVE,
                'total_xp' => 0,
            ],
            [
                'name'     => 'Ahmad Fauzi',
                'email'    => 'ahmad.fauzi@circlepintar.id',
                'password' => Hash::make('Teacher@123'),
                'role'     => User::ROLE_TEACHER,
                'status'   => User::STATUS_ACTIVE,
                'total_xp' => 0,
            ],
        ];

        foreach ($teachers as $teacher) {
            User::create($teacher);
        }

        // ─── Siswa / Student ──────────────────────────────────────────────────────
        $students = [
            [
                'name'     => 'Andi Pratama',
                'email'    => 'andi.pratama@siswa.circlepintar.id',
                'password' => Hash::make('Student@123'),
                'role'     => User::ROLE_STUDENT,
                'status'   => User::STATUS_ACTIVE,
                'total_xp' => 350,
            ],
            [
                'name'     => 'Dewi Lestari',
                'email'    => 'dewi.lestari@siswa.circlepintar.id',
                'password' => Hash::make('Student@123'),
                'role'     => User::ROLE_STUDENT,
                'status'   => User::STATUS_ACTIVE,
                'total_xp' => 120,
            ],
            [
                'name'     => 'Rizky Firmansyah',
                'email'    => 'rizky.firmansyah@siswa.circlepintar.id',
                'password' => Hash::make('Student@123'),
                'role'     => User::ROLE_STUDENT,
                'status'   => User::STATUS_ACTIVE,
                'total_xp' => 780,
            ],
            [
                'name'     => 'Nadia Putri',
                'email'    => 'nadia.putri@siswa.circlepintar.id',
                'password' => Hash::make('Student@123'),
                'role'     => User::ROLE_STUDENT,
                'status'   => User::STATUS_ACTIVE,
                'total_xp' => 200,
            ],
            [
                'name'     => 'Fajar Nugroho',
                'email'    => 'fajar.nugroho@siswa.circlepintar.id',
                'password' => Hash::make('Student@123'),
                'role'     => User::ROLE_STUDENT,
                'status'   => User::STATUS_PENDING, // masih pending (belum diaktifkan)
                'total_xp' => 0,
            ],
            [
                'name'     => 'Maya Anggraini',
                'email'    => 'maya.anggraini@siswa.circlepintar.id',
                'password' => Hash::make('Student@123'),
                'role'     => User::ROLE_STUDENT,
                'status'   => User::STATUS_ACTIVE,
                'total_xp' => 55,
            ],
        ];

        foreach ($students as $student) {
            User::create($student);
        }

        $this->command->info('✅ UserSeeder: 1 Super Admin, 3 Guru, 6 Siswa berhasil dibuat.');
    }
}
