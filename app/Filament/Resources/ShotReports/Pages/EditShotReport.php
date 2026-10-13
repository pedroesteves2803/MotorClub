<?php

namespace App\Filament\Resources\ShotReports\Pages;

use App\Filament\Resources\ShotReports\ShotReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShotReport extends EditRecord
{
    protected static string $resource = ShotReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
