<?php

namespace App\Filament\Resources\ProjectModifications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectModificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('project_id')->label('Projeto')->relationship('project', 'title')->searchable()->preload()->required(),
            TextInput::make('title')->label('Nome da modificação')->required()->maxLength(150),
            TextInput::make('category')->label('Categoria')->required()->maxLength(50),
            Select::make('status')->label('Status')->options(['planned' => 'Planejada', 'in_progress' => 'Em andamento', 'completed' => 'Concluída'])->required(),
            DatePicker::make('started_on')->label('Início'),
            DatePicker::make('completed_on')->label('Conclusão'),
            Textarea::make('description')->label('Descrição')->columnSpanFull(),
            TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
            Toggle::make('is_public')->label('Pública'),
        ]);
    }
}
