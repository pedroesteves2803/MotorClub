<?php

namespace App\Filament\Resources\ProjectModifications\Pages;

use App\Filament\Resources\ProjectModifications\ProjectModificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectModifications extends ListRecords
{
    protected static string $resource = ProjectModificationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
