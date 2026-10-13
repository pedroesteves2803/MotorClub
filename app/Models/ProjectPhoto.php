<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPhoto extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'sort_order' => 'integer'];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
