<?php

namespace App\Filament\Resources\ProjectParts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectPartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('project_modification_id')->label('Modificação')->relationship('modification', 'title')->searchable()->preload()->required(),
            TextInput::make('name')->label('Nome da peça')->required()->maxLength(150),
            TextInput::make('brand')->label('Marca')->maxLength(100),
            TextInput::make('model')->label('Modelo')->maxLength(120),
            Select::make('status')->label('Status')->options(['planned' => 'Planejada', 'in_progress' => 'Em andamento', 'installed' => 'Instalada'])->required(),
            Textarea::make('description')->label('Detalhes técnicos')->columnSpanFull(),
            TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
            Toggle::make('is_public')->label('Pública'),
        ]);
    }
}
