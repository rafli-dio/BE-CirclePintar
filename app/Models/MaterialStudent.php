<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialStudent extends Model
{
    use HasFactory;

    // ─── Config ──────────────────────────────────────────────────────────────────

    /**
     * Nama tabel secara eksplisit.
     */
    protected $table = 'material_student';

    /**
     * Tidak menggunakan timestamps default Laravel.
     * Menggunakan completed_at sebagai penanda waktu selesai.
     */
    public $timestamps = false;

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    protected $fillable = [
        'material_id',
        'user_id',
        'is_completed',
        'completed_at',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Materi yang sedang dilacak progressnya.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Siswa yang mengakses materi ini.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
