<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShotPurchaseRequest extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['authorized_at' => 'datetime', 'decided_at' => 'datetime', 'delivery_sent_at' => 'datetime', 'refunded_at' => 'datetime', 'quoted_price' => 'decimal:2', 'photographer_amount' => 'decimal:2', 'platform_amount' => 'decimal:2'];
    }

    public function photo()
    {
        return $this->belongsTo(ShotPhoto::class, 'shot_photo_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function decisionAdmin()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function events()
    {
        return $this->hasMany(ShotPaymentEvent::class);
    }

    public function grants()
    {
        return $this->hasMany(ShotDownloadGrant::class);
    }
}
