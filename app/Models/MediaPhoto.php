<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class MediaPhoto extends Model
{
    protected $fillable = ['path', 'kind', 'caption', 'sort_order', 'is_primary', 'is_public'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'is_public' => 'boolean', 'sort_order' => 'integer'];
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::deleting(function (MediaPhoto $photo) {
            $disk = Storage::disk(config('filesystems.private_disk'));
            if ($disk->exists($photo->path)) {
                $disk->delete($photo->path);
            }
        });
    }
}
