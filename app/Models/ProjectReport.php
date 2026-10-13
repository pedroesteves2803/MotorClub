<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectReport extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'missing_information' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function publiclyVisible(): bool
    {
        return $this->status === 'approved' && $this->is_public && $this->project->publiclyVisible();
    }
}
