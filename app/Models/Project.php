<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use App\Services\VehicleSlugService;

class Project extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'show_cost' => 'boolean'];
    }

    /**
     * Projects are public-facing chapters of a vehicle. Use the vehicle's
     * stable, readable slug in generated URLs instead of leaking the numeric
     * project primary key.
     */
    public function getRouteKey(): string
    {
        if (! $this->exists) {
            return (string) parent::getRouteKey();
        }

        $vehicle = $this->relationLoaded('vehicle')
            ? $this->getRelation('vehicle')
            : $this->vehicle()->first();

        if (! $vehicle) {
            return (string) parent::getRouteKey();
        }

        return app(VehicleSlugService::class)->ensure($vehicle);
    }

    /**
     * Resolve both the new vehicle slug and legacy numeric project URLs while
     * keeping all project actions on the same authorization path.
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (is_numeric($value)) {
            return $query->whereKey((int) $value);
        }

        return $query->whereHas('vehicle', fn ($vehicle) => $vehicle->where('slug', $value));
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
        return $this->hasMany(ProjectPhoto::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    public function mediaPhotos(): MorphMany
    {
        return $this->morphMany(MediaPhoto::class, 'imageable')->orderBy('sort_order')->orderBy('id');
    }

    public function modifications(): HasMany
    {
        return $this->hasMany(ProjectModification::class)->orderBy('sort_order')->orderBy('id');
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
            $project->mediaPhotos()->get()->each->delete();
            $project->modifications()->get()->each->delete();
        });
    }
}
