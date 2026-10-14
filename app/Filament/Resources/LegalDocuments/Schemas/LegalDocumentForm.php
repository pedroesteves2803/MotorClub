<?php

namespace App\Filament\Resources\LegalDocuments\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LegalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('slug')->label('Identificador')->required()->unique(ignoreRecord: true),
            TextInput::make('title')->label('Título')->required()->maxLength(160),
            TextInput::make('version')->label('Versão')->required()->maxLength(30),
            Textarea::make('content')->label('Conteúdo HTML')->required()->rows(20)->columnSpanFull(),
            Toggle::make('is_published')->label('Publicar')->live(),
        ]);
    }
}
