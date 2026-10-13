<?php

namespace App\Filament\Resources\ProjectModifications;

use App\Filament\Resources\ProjectModifications\Pages\CreateProjectModification;
use App\Filament\Resources\ProjectModifications\Pages\EditProjectModification;
use App\Filament\Resources\ProjectModifications\Pages\ListProjectModifications;
use App\Filament\Resources\ProjectModifications\Schemas\ProjectModificationForm;
use App\Filament\Resources\ProjectModifications\Tables\ProjectModificationsTable;
use App\Models\ProjectModification;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectModificationResource extends Resource
{
    protected static ?string $model = ProjectModification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|\UnitEnum|null $navigationGroup = 'Garagem';

    protected static ?string $modelLabel = 'modificação';

    protected static ?string $pluralModelLabel = 'Modificações';

    protected static ?string $navigationLabel = 'Modificações';

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

    public static function getSlug(?Panel $panel = null): string
    {
        return 'modificacoes';
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectModificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectModificationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListProjectModifications::route('/'), 'create' => CreateProjectModification::route('/create'), 'edit' => EditProjectModification::route('/{record}/edit')];
    }
}
