<?php

namespace App\Filament\Resources\ShotReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShotReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shot_photo_id')
                    ->label('Foto')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user_id')
                    ->label('Usuário')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Situação')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
