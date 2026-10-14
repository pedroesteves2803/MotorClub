<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookieConsent extends Model
{
    protected $fillable = ['user_id', 'visitor_id', 'analytics', 'personalization', 'policy_version', 'consented_at', 'revoked_at', 'ip_hash', 'user_agent_hash'];

    protected function casts(): array
    {
        return ['analytics' => 'boolean', 'personalization' => 'boolean', 'consented_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
