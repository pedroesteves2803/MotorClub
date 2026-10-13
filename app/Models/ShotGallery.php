<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShotGallery extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'shot_on' => 'date'];
    }

    protected static function booted(): void
    {
        static::deleting(function (ShotGallery $gallery) {
            foreach ($gallery->photos as $photo) {
                $photo->delete();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(CarEvent::class, 'car_event_id');
    }

    public function photos()
    {
        return $this->hasMany(ShotPhoto::class);
    }

    public function previewPhoto(): HasOne
    {
        return $this->hasOne(ShotPhoto::class)->where('is_published', true)->latestOfMany();
    }
}
