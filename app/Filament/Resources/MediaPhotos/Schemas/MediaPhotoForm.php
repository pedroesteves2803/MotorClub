<?php

namespace App\Filament\Resources\MediaPhotos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MediaPhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('imageable_type')->label('Tipo do vínculo')->disabled(),
            TextInput::make('imageable_id')->label('ID do vínculo')->disabled(),
            TextInput::make('path')->label('Arquivo')->disabled(),
            TextInput::make('kind')->label('Classificação')->required()->maxLength(30),
            TextInput::make('caption')->label('Legenda')->maxLength(255),
            TextInput::make('sort_order')->label('Ordem')->numeric()->required(),
            Toggle::make('is_primary')->label('Imagem principal'),
            Toggle::make('is_public')->label('Pública'),
        ]);
    }
}
