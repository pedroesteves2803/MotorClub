<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectPart extends Model
{
    protected $fillable = ['name', 'brand', 'model', 'status', 'description', 'sort_order', 'is_public'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public function modification(): BelongsTo
    {
        return $this->belongsTo(ProjectModification::class, 'project_modification_id');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(MediaPhoto::class, 'imageable')->orderBy('sort_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::deleting(function (ProjectPart $part) {
            $part->photos()->get()->each->delete();
        });
    }
}
