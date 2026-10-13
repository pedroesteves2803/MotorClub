<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShotPhoto extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'price' => 'decimal:2', 'published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (ShotPhoto $photo) {
            $photo->uuid ??= (string) Str::uuid();
            if ($photo->is_published && ! $photo->published_at) {
                $photo->published_at = now();
            }
        });
        static::deleting(function (ShotPhoto $photo) {
            if (! Storage::disk('local')->delete([$photo->original_path, $photo->preview_path])) {
                throw new \RuntimeException('Não foi possível excluir os arquivos da foto. Tente novamente.');
            }
        });
    }

    public function gallery()
    {
        return $this->belongsTo(ShotGallery::class, 'shot_gallery_id');
    }

    public function requests()
    {
        return $this->hasMany(ShotPurchaseRequest::class);
    }

    public function reports()
    {
        return $this->hasMany(ShotReport::class);
    }

    public function publiclyVisible(): bool
    {
        return $this->is_published && $this->gallery->is_public && $this->gallery->user->photographerProfile?->is_public;
    }
}
