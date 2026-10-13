<?php

namespace App\Filament\Resources\FeatureSettings\Pages;

use App\Filament\Resources\FeatureSettings\FeatureSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeatureSetting extends EditRecord
{
    protected static string $resource = FeatureSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
