<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectModification extends Model
{
    protected $fillable = ['title', 'category', 'status', 'description', 'started_on', 'completed_on', 'sort_order', 'is_public'];

    protected function casts(): array
    {
        return ['started_on' => 'date', 'completed_on' => 'date', 'is_public' => 'boolean'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(ProjectPart::class)->orderBy('sort_order')->orderBy('id');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(MediaPhoto::class, 'imageable')->orderBy('sort_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::deleting(function (ProjectModification $modification) {
            $modification->photos()->get()->each->delete();
            $modification->parts()->get()->each->delete();
        });
    }
}
