<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle_id')
                    ->label('Veículo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('workshop_id')
                    ->label('Oficina')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('workshop_status')
                    ->label('Situação da oficina')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Categoria')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Situação')
                    ->searchable(),
                TextColumn::make('cost')
                    ->label('Custo')
                    ->money('BRL')
                    ->sortable(),
                IconColumn::make('show_cost')
                    ->label('Exibe custo')
                    ->boolean(),
                IconColumn::make('is_public')
                    ->label('Público')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
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
