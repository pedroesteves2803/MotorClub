<?php

namespace App\Filament\Resources\ShotPurchaseRequests\Pages;

use App\Filament\Resources\ShotPurchaseRequests\ShotPurchaseRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShotPurchaseRequest extends EditRecord
{
    protected static string $resource = ShotPurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
