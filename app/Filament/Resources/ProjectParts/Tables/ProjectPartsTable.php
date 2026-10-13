<?php

namespace App\Filament\Resources\ProjectParts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectPartsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Peça')->searchable()->sortable(),
            TextColumn::make('brand')->label('Marca')->searchable(),
            TextColumn::make('model')->label('Modelo')->searchable(),
            TextColumn::make('modification.title')->label('Modificação')->searchable(),
            TextColumn::make('status')->label('Status')->formatStateUsing(fn (?string $state) => ['planned' => 'Planejada', 'in_progress' => 'Em andamento', 'installed' => 'Instalada'][$state] ?? $state),
            TextColumn::make('photos_count')->label('Fotos')->counts('photos'),
            IconColumn::make('is_public')->label('Pública')->boolean(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
