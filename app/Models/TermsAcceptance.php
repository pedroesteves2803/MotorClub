<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsAcceptance extends Model
{
    protected $fillable = ['user_id', 'document_type', 'version', 'accepted_at', 'ip_hash', 'user_agent_hash'];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
