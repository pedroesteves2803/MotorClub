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
                    ->label('Veículo')
                    ->required()
                    ->numeric(),
                TextInput::make('workshop_id')
                    ->label('Oficina')
                    ->numeric(),
                TextInput::make('workshop_status')
                    ->label('Situação da oficina')
                    ->required()
                    ->default('pending'),
                TextInput::make('title')
                    ->label('Título')
                    ->required(),
                TextInput::make('category')
                    ->label('Categoria')
                    ->required(),
                TextInput::make('status')
                    ->label('Situação')
                    ->required()
                    ->default('planning'),
                Textarea::make('description')
                    ->label('Descrição')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('parts')
                    ->label('Peças')
                    ->columnSpanFull(),
                Textarea::make('services')
                    ->label('Serviços')
                    ->columnSpanFull(),
                TextInput::make('cost')
                    ->label('Custo')
                    ->numeric()
                    ->prefix('R$'),
                Toggle::make('show_cost')
                    ->label('Exibir custo')
                    ->required(),
                Toggle::make('is_public')
                    ->label('Público')
                    ->required(),
            ]);
    }
}
