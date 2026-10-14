<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagazineSetting extends Model
{
    protected $fillable = [
        'primary_color', 'theme', 'editorial_style', 'title', 'subtitle', 'palette', 'is_published',
        'cover_photo_id', 'highlight_photo_ids', 'chapter_order', 'qr_foreground', 'qr_background',
        'qr_label', 'qr_brand',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'highlight_photo_ids' => 'array',
            'chapter_order' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(VehiclePhoto::class, 'cover_photo_id');
    }
}
