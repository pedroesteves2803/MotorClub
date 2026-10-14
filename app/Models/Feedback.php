<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id', 'category', 'title', 'description', 'image_path', 'email',
        'status', 'internal_notes', 'technical_context', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['technical_context' => 'array', 'resolved_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
