<?php

namespace App\Filament\Resources\ShotReports;

use App\Filament\Resources\ShotReports\Pages\CreateShotReport;
use App\Filament\Resources\ShotReports\Pages\EditShotReport;
use App\Filament\Resources\ShotReports\Pages\ListShotReports;
use App\Filament\Resources\ShotReports\Schemas\ShotReportForm;
use App\Filament\Resources\ShotReports\Tables\ShotReportsTable;
use App\Models\ShotReport;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShotReportResource extends Resource
{
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

    protected static ?string $modelLabel = 'denúncia';

    protected static ?string $pluralModelLabel = 'Denúncias';

    protected static ?string $navigationLabel = 'Denúncias';

    protected static string|\UnitEnum|null $navigationGroup = 'Shots';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'denuncias';
    }

    protected static ?string $model = ShotReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ShotReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShotReportsTable::configure($table);
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
            'index' => ListShotReports::route('/'),
            'create' => CreateShotReport::route('/create'),
            'edit' => EditShotReport::route('/{record}/edit'),
        ];
    }
}
