<?php

namespace App\Filament\Resources\MediaPhotos\Pages;

use App\Filament\Resources\MediaPhotos\MediaPhotoResource;
use Filament\Resources\Pages\ListRecords;

class ListMediaPhotos extends ListRecords
{
    protected static string $resource = MediaPhotoResource::class;
}
