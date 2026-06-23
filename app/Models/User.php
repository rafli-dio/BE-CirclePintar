<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    // ─── Enum Constants ──────────────────────────────────────────────────────────

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_TEACHER     = 'teacher';
    const ROLE_STUDENT     = 'student';

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE  = 'active';

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'total_xp',
    ];

    // ─── Hidden ──────────────────────────────────────────────────────────────────

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'total_xp' => 'integer',
        ];
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────────

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if the user is a teacher.
     */
    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    /**
     * Check if the user is a student.
     */
    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Kelas-kelas yang diikuti oleh siswa ini.
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_student')
                    ->withPivot('enrolled_at');
    }

    /**
     * Progres belajar materi milik siswa ini.
     */
    public function materialProgress(): HasMany
    {
        return $this->hasMany(MaterialStudent::class);
    }

    /**
     * Riwayat semua pengerjaan kuis oleh siswa ini.
     */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Badge-badge yang telah diraih oleh siswa ini.
     */
    public function earnedBadges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
                    ->withPivot('earned_at')
                    ->orderByPivot('earned_at', 'desc');
    }
}
