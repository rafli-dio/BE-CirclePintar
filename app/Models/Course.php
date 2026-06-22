<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    // ─── Mass Assignable ─────────────────────────────────────────────────────────

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'thumbnail',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────────

    /**
     * Guru (User) yang membuat kelas ini.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Kategori kelas.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Modul / bab yang ada di kelas ini.
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order_number');
    }
}
