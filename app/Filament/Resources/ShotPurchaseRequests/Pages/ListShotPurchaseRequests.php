<?php

namespace App\Filament\Resources\ShotPurchaseRequests\Pages;

use App\Filament\Resources\ShotPurchaseRequests\ShotPurchaseRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShotPurchaseRequests extends ListRecords
{
    protected static string $resource = ShotPurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
