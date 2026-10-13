<?php

namespace App\Filament\Resources\ShotGalleries\Pages;

use App\Filament\Resources\ShotGalleries\ShotGalleryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShotGalleries extends ListRecords
{
    protected static string $resource = ShotGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
