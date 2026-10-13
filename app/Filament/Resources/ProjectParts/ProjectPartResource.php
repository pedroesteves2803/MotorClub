<?php

namespace App\Filament\Resources\ProjectParts;

use App\Filament\Resources\ProjectParts\Pages\CreateProjectPart;
use App\Filament\Resources\ProjectParts\Pages\EditProjectPart;
use App\Filament\Resources\ProjectParts\Pages\ListProjectParts;
use App\Filament\Resources\ProjectParts\Schemas\ProjectPartForm;
use App\Filament\Resources\ProjectParts\Tables\ProjectPartsTable;
use App\Models\ProjectPart;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectPartResource extends Resource
{
    protected static ?string $model = ProjectPart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|\UnitEnum|null $navigationGroup = 'Garagem';

    protected static ?string $modelLabel = 'peça';

    protected static ?string $pluralModelLabel = 'Peças';

    protected static ?string $navigationLabel = 'Peças';

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
        return 'pecas';
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectPartForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectPartsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListProjectParts::route('/'), 'create' => CreateProjectPart::route('/create'), 'edit' => EditProjectPart::route('/{record}/edit')];
    }
}
