<?php

namespace App\Filament\Resources\ProjectParts\Pages;

use App\Filament\Resources\ProjectParts\ProjectPartResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectParts extends ListRecords
{
    protected static string $resource = ProjectPartResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
