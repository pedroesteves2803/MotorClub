<?php

namespace App\Filament\Resources\ShotGalleries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShotGalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->label('Fotógrafo')
                    ->required()
                    ->numeric(),
                TextInput::make('car_event_id')
                    ->label('Evento')
                    ->numeric(),
                TextInput::make('title')
                    ->label('Título')
                    ->required(),
                Textarea::make('description')
                    ->label('Descrição')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->label('Cidade')
                    ->required(),
                TextInput::make('state')
                    ->label('Estado')
                    ->required(),
                DatePicker::make('shot_on')
                    ->label('Data das fotos')
                    ->required(),
                Toggle::make('is_public')
                    ->label('Pública')
                    ->required(),
            ]);
    }
}
