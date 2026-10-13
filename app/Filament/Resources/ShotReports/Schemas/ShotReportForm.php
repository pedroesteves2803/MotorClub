<?php

namespace App\Filament\Resources\ShotReports\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShotReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('shot_photo_id')
                    ->label('Foto')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->label('Usuário')
                    ->required()
                    ->numeric(),
                TextInput::make('reason')
                    ->label('Motivo')
                    ->required(),
                Textarea::make('details')
                    ->label('Detalhes')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->label('Situação')
                    ->required()
                    ->default('open'),
            ]);
    }
}
