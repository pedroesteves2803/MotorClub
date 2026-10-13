<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Proprietário')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('brand')
                    ->label('Marca')
                    ->required(),
                TextInput::make('model')
                    ->label('Modelo')
                    ->required(),
                TextInput::make('trim')
                    ->label('Versão')
                    ->required(),
                TextInput::make('model_year')
                    ->label('Ano do modelo')
                    ->required()
                    ->numeric(),
                TextInput::make('engine')
                    ->label('Motor')
                    ->required(),
                TextInput::make('visibility')
                    ->label('Visibilidade')
                    ->required()
                    ->default('private'),
            ]);
    }
}
