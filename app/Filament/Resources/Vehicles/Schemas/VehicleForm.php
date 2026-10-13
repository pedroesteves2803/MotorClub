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
                TextInput::make('public_name')
                    ->label('Nome público')
                    ->maxLength(120),
                TextInput::make('trim')
                    ->label('Versão')
                    ->required(),
                TextInput::make('generation')
                    ->label('Geração')
                    ->maxLength(50),
                TextInput::make('color')
                    ->label('Cor')
                    ->maxLength(50),
                TextInput::make('model_year')
                    ->label('Ano do modelo')
                    ->required()
                    ->numeric(),
                TextInput::make('engine')
                    ->label('Motor')
                    ->required(),
                TextInput::make('mileage')
                    ->label('Quilometragem')
                    ->numeric(),
                TextInput::make('acquired_on')
                    ->label('Data de aquisição')
                    ->type('date'),
                TextInput::make('location')
                    ->label('Localização geral')
                    ->maxLength(100),
                TextInput::make('instagram_url')
                    ->label('Instagram do carro')
                    ->url()
                    ->maxLength(255),
                TextInput::make('visibility')
                    ->label('Visibilidade')
                    ->required()
                    ->default('private'),
            ]);
    }
}
