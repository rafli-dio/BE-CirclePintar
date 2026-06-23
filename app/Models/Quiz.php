<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    protected $fillable = [
        'course_id',
        'title',
        'time_limit',
        'reward_xp',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'time_limit' => 'integer',
            'reward_xp'  => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Kelas yang memiliki kuis ini.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Soal-soal yang ada dalam kuis ini.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Riwayat pengerjaan kuis oleh semua siswa.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
