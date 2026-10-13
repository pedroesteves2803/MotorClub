<?php

namespace App\Filament\Resources\FeatureSettings;

use App\Filament\Resources\FeatureSettings\Pages\CreateFeatureSetting;
use App\Filament\Resources\FeatureSettings\Pages\EditFeatureSetting;
use App\Filament\Resources\FeatureSettings\Pages\ListFeatureSettings;
use App\Filament\Resources\FeatureSettings\Schemas\FeatureSettingForm;
use App\Filament\Resources\FeatureSettings\Tables\FeatureSettingsTable;
use App\Models\FeatureSetting;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FeatureSettingResource extends Resource
{
    protected static ?string $modelLabel = 'funcionalidade';

    protected static ?string $pluralModelLabel = 'Funcionalidades';

    protected static ?string $navigationLabel = 'Funcionalidades';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'funcionalidades';
    }

    protected static ?string $model = FeatureSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return FeatureSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeatureSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeatureSettings::route('/'),
            'create' => CreateFeatureSetting::route('/create'),
            'edit' => EditFeatureSetting::route('/{record}/edit'),
        ];
    }
}
