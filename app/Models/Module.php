<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'order_number',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'order_number' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Kelas yang memiliki modul ini.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Materi / konten yang ada di modul ini.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class)->orderBy('order_number');
    }
}
