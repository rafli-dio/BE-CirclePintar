<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => fake('id_ID')->name(),
            'email'    => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role'     => User::ROLE_STUDENT,
            'status'   => User::STATUS_ACTIVE,
            'total_xp' => 0,
        ];
    }

    // ─── Role States ─────────────────────────────────────────────────────────────

    /** Buat user dengan role super_admin. */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'   => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /** Buat user dengan role teacher. */
    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'   => User::ROLE_TEACHER,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /** Buat user dengan role student. */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'   => User::ROLE_STUDENT,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    // ─── Status States ────────────────────────────────────────────────────────────

    /** Tandai user sebagai pending (belum aktif). */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => User::STATUS_PENDING,
        ]);
    }
}
