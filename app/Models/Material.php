<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    use HasFactory;

    // ─── Enum Constants ──────────────────────────────────────────────────────────

    const TYPE_VIDEO = 'video';
    const TYPE_PDF   = 'pdf';
    const TYPE_TEXT  = 'text';

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'module_id',
        'title',
        'type',
        'content_url',
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
     * Modul yang memiliki materi ini.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
