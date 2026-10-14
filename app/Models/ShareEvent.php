<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareEvent extends Model
{
    protected $fillable = ['vehicle_id', 'event_type', 'source', 'user_agent_hash', 'ip_hash'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
