<?php

namespace App\Filament\Resources\ShotReports\Pages;

use App\Filament\Resources\ShotReports\ShotReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShotReports extends ListRecords
{
    protected static string $resource = ShotReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
