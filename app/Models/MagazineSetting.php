<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagazineSetting extends Model
{
    protected $fillable = ['primary_color', 'theme', 'editorial_style', 'cover_photo_id'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(VehiclePhoto::class, 'cover_photo_id');
    }
}
