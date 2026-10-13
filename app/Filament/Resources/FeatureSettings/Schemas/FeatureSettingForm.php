<?php

namespace App\Filament\Resources\FeatureSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FeatureSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('feature')
                    ->label('Funcionalidade')
                    ->options([
                        'garage' => 'Garagem',
                        'shots' => 'Shots',
                        'explore' => 'Explorar',
                        'ai_reports' => 'Relatórios IA',
                        'workshops' => 'Oficinas e orçamentos',
                        'events' => 'Eventos',
                        'subscriptions' => 'Assinaturas',
                        'demo' => 'Demonstrações',
                    ])
                    ->required()
                    ->unique(ignoreRecord: true),
                Toggle::make('enabled')->label('Ativa')->default(false)->required(),
            ]);
    }
}
