<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;

    // ─── Config ──────────────────────────────────────────────────────────────────

    protected $table = 'user_badges';

    public $timestamps = false;

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Siswa yang mendapatkan badge ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Badge yang diraih.
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
