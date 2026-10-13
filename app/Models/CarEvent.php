<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'is_public' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shotGalleries()
    {
        return $this->hasMany(ShotGallery::class);
    }
}
