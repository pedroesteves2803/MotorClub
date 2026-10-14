<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type', 'brand', 'model', 'public_name', 'slug', 'public_key', 'slug_updated_at', 'trim', 'generation', 'color', 'model_year', 'engine',
        'mileage', 'mileage_approximate', 'acquired_on', 'location', 'instagram_url', 'visibility',
    ];

    protected function casts(): array
    {
        return [
            'acquired_on' => 'date',
            'mileage' => 'integer',
            'mileage_approximate' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Vehicle $vehicle) {
            $vehicle->entries()->get()->each->delete();
            Project::where('vehicle_id', $vehicle->id)->each(fn (Project $project) => $project->delete());
            foreach ($vehicle->photos as $photo) {
                if (! Storage::disk(config('filesystems.private_disk'))->delete($photo->path)) {
                    throw new \RuntimeException('Não foi possível remover as fotos do veículo. Tente novamente.');
                }
            }
            $vehicle->mediaPhotos()->get()->each->delete();
        });
    }

    public function entries(): HasMany
    {
        return $this->hasMany(VehicleEntry::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class)->latestOfMany();
    }

    public function previewPhoto(): HasOne
    {
        return $this->hasOne(VehiclePhoto::class)->latestOfMany();
    }

    public function mediaPhotos(): MorphMany
    {
        return $this->morphMany(MediaPhoto::class, 'imageable')->orderBy('sort_order')->orderBy('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shotRequests(): HasMany
    {
        return $this->hasMany(ShotPurchaseRequest::class);
    }

    public function slugHistories(): HasMany
    {
        return $this->hasMany(VehicleSlugHistory::class);
    }

    public function magazineSetting(): HasOne
    {
        return $this->hasOne(MagazineSetting::class);
    }

    /**
     * A published edition that can be surfaced in public discovery surfaces.
     * The project visibility is checked by the calling query because it lives
     * on the related project record rather than on the magazine settings.
     */
    public function publishedMagazine(): HasOne
    {
        return $this->hasOne(MagazineSetting::class)->where('is_published', true);
    }

    public function shotAssets(): HasMany
    {
        return $this->hasMany(VehicleShotAsset::class);
    }
}
