<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleEntry extends Model
{
    protected $fillable = ['category', 'title', 'description', 'occurred_on', 'parts', 'services', 'cost', 'is_public'];

    protected function casts(): array
    {
        return ['occurred_on' => 'date', 'cost' => 'decimal:2', 'is_public' => 'boolean'];
    }
}
