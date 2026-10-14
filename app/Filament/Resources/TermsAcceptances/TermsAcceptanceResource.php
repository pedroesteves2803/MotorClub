<?php

namespace App\Filament\Resources\TermsAcceptances;

use App\Filament\Resources\TermsAcceptances\Pages\ListTermsAcceptances;
use App\Models\TermsAcceptance;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TermsAcceptanceResource extends Resource
{
    protected static ?string $model = TermsAcceptance::class;

    protected static ?string $modelLabel = 'aceite de termos';

    protected static ?string $pluralModelLabel = 'Aceites de termos';

    protected static ?string $navigationLabel = 'Aceites de termos';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'aceites-de-termos';
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
        return $table->defaultSort('accepted_at', 'desc')->columns([
            TextColumn::make('user.email')->label('Conta')->searchable(),
            TextColumn::make('document_type')->label('Documento'),
            TextColumn::make('version')->label('Versão'),
            TextColumn::make('accepted_at')->label('Aceito em')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTermsAcceptances::route('/')];
    }
}
