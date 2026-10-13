<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    protected $fillable = ['feature', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public static function enabled(string $feature): bool
    {
        return static::query()->where('feature', $feature)->value('enabled')
            ?? (bool) config("features.{$feature}", false);
    }
}
