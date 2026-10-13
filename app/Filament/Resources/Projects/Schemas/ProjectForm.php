<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vehicle_id')
                    ->required()
                    ->numeric(),
                TextInput::make('workshop_id')
                    ->numeric(),
                TextInput::make('workshop_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('category')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('planning'),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('parts')
                    ->columnSpanFull(),
                Textarea::make('services')
                    ->columnSpanFull(),
                TextInput::make('cost')
                    ->numeric()
                    ->prefix('$'),
                Toggle::make('show_cost')
                    ->required(),
                Toggle::make('is_public')
                    ->required(),
            ]);
    }
}
