<?php

namespace App\Filament\Resources\ProjectModifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectModificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Modificação')->searchable()->sortable(),
            TextColumn::make('project.title')->label('Projeto')->searchable(),
            TextColumn::make('status')->label('Status')->formatStateUsing(fn (?string $state) => ['planned' => 'Planejada', 'in_progress' => 'Em andamento', 'completed' => 'Concluída'][$state] ?? $state),
            TextColumn::make('photos_count')->label('Fotos')->counts('photos'),
            IconColumn::make('is_public')->label('Pública')->boolean(),
            TextColumn::make('updated_at')->label('Atualizada em')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
