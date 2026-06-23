<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    protected $fillable = [
        'quiz_id',
        'user_id',
        'score',
        'earned_xp',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'score'     => 'integer',
            'earned_xp' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Kuis yang dikerjakan.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Siswa yang mengerjakan kuis.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
