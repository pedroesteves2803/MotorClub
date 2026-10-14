<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected $fillable = ['admin_id', 'action', 'auditable_type', 'auditable_id', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
