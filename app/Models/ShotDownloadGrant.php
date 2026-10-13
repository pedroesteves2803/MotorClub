<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShotDownloadGrant extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['granted_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function payment()
    {
        return $this->belongsTo(ShotPurchaseRequest::class, 'shot_purchase_request_id');
    }
}
