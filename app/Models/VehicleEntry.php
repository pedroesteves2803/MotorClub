<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VehicleEntry extends Model
{
    protected $fillable = ['category', 'title', 'description', 'occurred_on', 'parts', 'services', 'cost', 'is_public'];

    protected function casts(): array
    {
        return ['occurred_on' => 'date', 'cost' => 'decimal:2', 'is_public' => 'boolean'];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(MediaPhoto::class, 'imageable')->orderBy('sort_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::deleting(fn (VehicleEntry $entry) => $entry->photos()->get()->each->delete());
    }
}
