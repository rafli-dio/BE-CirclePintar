<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    // ─── Enum Constants ──────────────────────────────────────────────────────────

    /**
     * Diberikan saat siswa mendapat score >= requirement_value pada kuis apapun.
     * Contoh: "Nilai Sempurna" → requirement_value = 100
     */
    const TYPE_QUIZ_SCORE = 'quiz_score';

    /**
     * Diberikan saat total XP siswa >= requirement_value.
     * Contoh: "XP Legend" → requirement_value = 1000
     */
    const TYPE_XP_MILESTONE = 'xp_milestone';

    /**
     * Diberikan saat siswa menyelesaikan >= requirement_value kelas (100% materi).
     * Contoh: "Rajin Belajar" → requirement_value = 3
     */
    const TYPE_COURSE_COMPLETE = 'course_complete';

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    protected $fillable = [
        'name',
        'description',
        'icon',
        'badge_type',
        'requirement_value',
        'reward_xp',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'requirement_value' => 'integer',
            'reward_xp'         => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Siswa-siswa yang telah mendapatkan badge ini.
     */
    public function earnedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
                    ->withPivot('earned_at');
    }
}
