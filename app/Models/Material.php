<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory;

    // ─── Enum Constants ──────────────────────────────────────────────────────────

    const TYPE_VIDEO = 'video';
    const TYPE_PDF   = 'pdf';
    const TYPE_TEXT  = 'text';

    const DISK_LOCAL    = 'local';
    const DISK_EXTERNAL = 'external';

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
        'disk',
        'order_number',
    ];

    // ─── Casts ───────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'order_number' => 'integer',
        ];
    }

    // ─── Accessor ────────────────────────────────────────────────────────────────

    /**
     * Ubah content_url secara otomatis:
     * - Jika disk = 'local'    → kembalikan URL publik lengkap dari storage
     * - Jika disk = 'external' → kembalikan URL apa adanya
     *
     * Dengan ini, response JSON selalu berisi URL yang siap dipakai frontend.
     */
    protected function contentUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function (?string $value) {
                if ($value === null) {
                    return null;
                }

                if ($this->disk === self::DISK_LOCAL) {
                    return Storage::disk('public')->url($value);
                }

                return $value;
            }
        );
    }

    // ─── Model Events ─────────────────────────────────────────────────────────────

    /**
     * Otomatis hapus file dari storage saat record material dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (Material $material) {
            if ($material->disk === self::DISK_LOCAL) {
                // getRawOriginal() untuk mendapat path asli sebelum melewati accessor
                $path = $material->getRawOriginal('content_url');
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Modul yang memiliki materi ini.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Record progres belajar siswa untuk materi ini.
     */
    public function progress(): HasMany
    {
        return $this->hasMany(MaterialStudent::class);
    }
}
