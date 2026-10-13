<?php

namespace App\Filament\Resources\MediaPhotos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaPhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('imageable_type')->label('Vínculo')->formatStateUsing(fn (?string $state) => str($state)->afterLast('\\')->toString()),
            TextColumn::make('imageable_id')->label('ID')->numeric()->sortable(),
            TextColumn::make('kind')->label('Classificação')->searchable(),
            TextColumn::make('caption')->label('Legenda')->limit(40),
            IconColumn::make('is_primary')->label('Principal')->boolean(),
            IconColumn::make('is_public')->label('Pública')->boolean(),
            TextColumn::make('created_at')->label('Criada em')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
