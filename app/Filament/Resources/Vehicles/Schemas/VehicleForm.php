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
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('brand')
                    ->required(),
                TextInput::make('model')
                    ->required(),
                TextInput::make('trim')
                    ->required(),
                TextInput::make('model_year')
                    ->required()
                    ->numeric(),
                TextInput::make('engine')
                    ->required(),
                TextInput::make('visibility')
                    ->required()
                    ->default('private'),
            ]);
    }
}
