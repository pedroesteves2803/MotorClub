<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleShotAsset extends Model
{
    protected $fillable = ['vehicle_id', 'shot_photo_id', 'license_snapshot', 'credit', 'is_public', 'revoked_at'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'revoked_at' => 'datetime'];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function shotPhoto(): BelongsTo
    {
        return $this->belongsTo(ShotPhoto::class);
    }

    public function publiclyVisible(): bool
    {
        return $this->is_public && $this->revoked_at === null && $this->shotPhoto->publiclyVisible();
    }
}
