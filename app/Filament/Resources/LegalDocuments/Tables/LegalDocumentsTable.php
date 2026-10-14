<?php

namespace App\Filament\Resources\LegalDocuments\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LegalDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('updated_at', 'desc')->columns([
            TextColumn::make('title')->label('Documento')->searchable(),
            TextColumn::make('slug')->label('Identificador'),
            TextColumn::make('version')->label('Versão'),
            IconColumn::make('is_published')->label('Publicado')->boolean(),
            TextColumn::make('updated_at')->label('Atualizado')->since(),
        ])->recordActions([EditAction::make()]);
    }
}
