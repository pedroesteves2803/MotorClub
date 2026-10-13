<?php

namespace App\Filament\Resources\ShotPurchaseRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShotPurchaseRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('shot_photo_id')
                    ->label('Foto')
                    ->required()
                    ->numeric(),
                TextInput::make('buyer_id')
                    ->label('Comprador')
                    ->required()
                    ->numeric(),
                TextInput::make('vehicle_id')
                    ->label('Veículo')
                    ->numeric(),
                TextInput::make('status')
                    ->label('Situação')
                    ->required()
                    ->default('pending'),
                Textarea::make('message')
                    ->label('Mensagem')
                    ->columnSpanFull(),
                TextInput::make('quoted_price')
                    ->label('Preço informado')
                    ->numeric()
                    ->prefix('R$'),
                TextInput::make('photographer_amount')
                    ->label('Valor do fotógrafo')
                    ->numeric(),
                TextInput::make('platform_amount')
                    ->label('Valor da plataforma')
                    ->numeric(),
                DateTimePicker::make('authorized_at')->label('Autorizado em'),
            ]);
    }
}
