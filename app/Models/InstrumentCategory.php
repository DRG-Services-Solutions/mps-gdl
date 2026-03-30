<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InstrumentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    // ═══════════════════════════════════════════
    // RELACIONES
    // ═══════════════════════════════════════════

    public function instruments()
    {
        return $this->hasMany(Instrument::class, 'category_id');
    }

    // ═══════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    // ═══════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════

    public function getInstrumentCountAttribute(): int
    {
        return $this->relationLoaded('instruments')
            ? $this->instruments->count()
            : $this->instruments()->count();
    }
}