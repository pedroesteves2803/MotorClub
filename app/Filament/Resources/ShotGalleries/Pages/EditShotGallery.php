<?php

namespace App\Filament\Resources\ShotGalleries\Pages;

use App\Filament\Resources\ShotGalleries\ShotGalleryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShotGallery extends EditRecord
{
    protected static string $resource = ShotGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
