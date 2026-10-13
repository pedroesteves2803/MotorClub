<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShotReport extends Model
{
    protected $guarded = ['id'];

    public function photo()
    {
        return $this->belongsTo(ShotPhoto::class, 'shot_photo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
