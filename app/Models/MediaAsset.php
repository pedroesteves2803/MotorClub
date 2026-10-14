<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = ['path', 'mime_type', 'size_bytes', 'contents'];

    protected $hidden = ['contents'];
}
