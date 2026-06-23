<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseStudent extends Model
{
    use HasFactory;

    // ─── Config ──────────────────────────────────────────────────────────────────

    /**
     * Nama tabel secara eksplisit (pivot table tidak mengikuti konvensi plural).
     */
    protected $table = 'course_student';

    /**
     * Tidak menggunakan created_at / updated_at default Laravel.
     * Sebagai gantinya menggunakan kolom enrolled_at.
     */
    public $timestamps = false;

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    protected $fillable = [
        'course_id',
        'user_id',
        'enrolled_at',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Kelas yang didaftarkan.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Siswa yang mendaftar.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
