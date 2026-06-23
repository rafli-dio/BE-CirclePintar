<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    // ─── Enum Constants ──────────────────────────────────────────────────────────

    const KEY_A = 'A';
    const KEY_B = 'B';
    const KEY_C = 'C';
    const KEY_D = 'D';

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    protected $fillable = [
        'quiz_id',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_key',
    ];

    // ─── Hidden ──────────────────────────────────────────────────────────────────

    /**
     * Sembunyikan correct_key agar tidak bocor ke response siswa saat
     * mengambil soal sebelum submit.
     *
     * Gunakan makeVisible('correct_key') khusus untuk keperluan admin/penilaian.
     */
    protected $hidden = [
        'correct_key',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Kuis yang memiliki soal ini.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
