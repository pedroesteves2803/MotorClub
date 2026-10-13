<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'show_cost' => 'boolean'];
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function photos()
    {
        return $this->hasMany(ProjectPhoto::class);
    }

    public function previewPhoto(): HasOne
    {
        return $this->hasOne(ProjectPhoto::class)->latestOfMany();
    }

    public function reports()
    {
        return $this->hasMany(ProjectReport::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_public', true)->whereHas('vehicle', fn ($q) => $q->where('visibility', 'public'));
    }

    public function publiclyVisible(): bool
    {
        return $this->is_public && $this->vehicle->visibility === 'public';
    }

    protected static function booted(): void
    {
        static::deleting(function ($project) {
            foreach ($project->photos as $photo) {
                if (! Storage::disk(config('filesystems.private_disk'))->delete($photo->path)) {
                    throw new \RuntimeException('Não foi possível excluir as fotos.');
                }
            }
        });
    }
}
