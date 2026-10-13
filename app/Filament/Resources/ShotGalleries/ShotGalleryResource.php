<?php

namespace App\Filament\Resources\ShotGalleries;

use App\Filament\Resources\ShotGalleries\Pages\CreateShotGallery;
use App\Filament\Resources\ShotGalleries\Pages\EditShotGallery;
use App\Filament\Resources\ShotGalleries\Pages\ListShotGalleries;
use App\Filament\Resources\ShotGalleries\Schemas\ShotGalleryForm;
use App\Filament\Resources\ShotGalleries\Tables\ShotGalleriesTable;
use App\Models\ShotGallery;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShotGalleryResource extends Resource
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

    protected static ?string $modelLabel = 'galeria';

    protected static ?string $pluralModelLabel = 'Galerias';

    protected static ?string $navigationLabel = 'Galerias';

    protected static string|\UnitEnum|null $navigationGroup = 'Shots';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'galerias';
    }

    protected static ?string $model = ShotGallery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ShotGalleryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShotGalleriesTable::configure($table);
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
            'index' => ListShotGalleries::route('/'),
            'create' => CreateShotGallery::route('/create'),
            'edit' => EditShotGallery::route('/{record}/edit'),
        ];
    }
}
