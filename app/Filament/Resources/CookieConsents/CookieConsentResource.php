<?php

namespace App\Filament\Resources\CookieConsents;

use App\Filament\Resources\CookieConsents\Pages\ListCookieConsents;
use App\Models\CookieConsent;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CookieConsentResource extends Resource
{
    protected static ?string $model = CookieConsent::class;

    protected static ?string $modelLabel = 'consentimento de cookies';

    protected static ?string $pluralModelLabel = 'Consentimentos de cookies';

    protected static ?string $navigationLabel = 'Consentimentos de cookies';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'consentimentos-de-cookies';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('consented_at', 'desc')->columns([
            TextColumn::make('user.email')->label('Conta')->searchable(),
            IconColumn::make('analytics')->label('Métricas')->boolean(),
            IconColumn::make('personalization')->label('Personalização')->boolean(),
            TextColumn::make('policy_version')->label('Versão'),
            TextColumn::make('consented_at')->label('Manifestado em')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCookieConsents::route('/')];
    }
}
