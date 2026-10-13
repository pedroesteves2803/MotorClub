<?php

namespace App\Filament\Resources\FeatureSettings\Pages;

use App\Filament\Resources\FeatureSettings\FeatureSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeatureSettings extends ListRecords
{
    protected static string $resource = FeatureSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
