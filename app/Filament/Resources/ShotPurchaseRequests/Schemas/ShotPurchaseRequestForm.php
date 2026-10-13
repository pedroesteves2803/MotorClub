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
                    ->required()
                    ->numeric(),
                TextInput::make('buyer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('vehicle_id')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                Textarea::make('message')
                    ->columnSpanFull(),
                TextInput::make('quoted_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('photographer_amount')
                    ->numeric(),
                TextInput::make('platform_amount')
                    ->numeric(),
                DateTimePicker::make('authorized_at'),
            ]);
    }
}
