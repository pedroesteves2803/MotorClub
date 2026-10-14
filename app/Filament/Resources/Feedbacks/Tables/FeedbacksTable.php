<?php

namespace App\Filament\Resources\Feedbacks\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeedbacksTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('category')->label('Categoria')->formatStateUsing(fn ($state) => [
                'suggestion' => 'Sugestão', 'feature' => 'Funcionalidade', 'bug' => 'Bug',
                'usability' => 'Usabilidade', 'praise' => 'Elogio', 'other' => 'Outro',
            ][$state] ?? $state)->badge(),
            TextColumn::make('title')->label('Título')->searchable()->limit(50),
            TextColumn::make('email')->label('Retorno')->toggleable(),
            TextColumn::make('status')->label('Status')->badge(),
            TextColumn::make('created_at')->label('Recebido')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('category')->label('Categoria')->options(['suggestion' => 'Sugestão', 'feature' => 'Funcionalidade', 'bug' => 'Bug', 'usability' => 'Usabilidade', 'praise' => 'Elogio', 'other' => 'Outro']),
            SelectFilter::make('status')->label('Status')->options(['received' => 'Recebido', 'reviewing' => 'Em análise', 'planned' => 'Planejado', 'in_progress' => 'Em desenvolvimento', 'resolved' => 'Resolvido', 'discarded' => 'Descartado']),
        ])->recordActions([EditAction::make()]);
    }
}
