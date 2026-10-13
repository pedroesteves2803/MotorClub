<?php

namespace App\Filament\Resources\MediaPhotos;

use App\Filament\Resources\MediaPhotos\Pages\EditMediaPhoto;
use App\Filament\Resources\MediaPhotos\Pages\ListMediaPhotos;
use App\Filament\Resources\MediaPhotos\Schemas\MediaPhotoForm;
use App\Filament\Resources\MediaPhotos\Tables\MediaPhotosTable;
use App\Models\MediaPhoto;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MediaPhotoResource extends Resource
{
    protected static ?string $model = MediaPhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Garagem';

    protected static ?string $modelLabel = 'imagem vinculada';

    protected static ?string $pluralModelLabel = 'Imagens vinculadas';

    protected static ?string $navigationLabel = 'Imagens vinculadas';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
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
        return 'imagens-vinculadas';
    }

    public static function form(Schema $schema): Schema
    {
        return MediaPhotoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaPhotosTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListMediaPhotos::route('/'), 'edit' => EditMediaPhoto::route('/{record}/edit')];
    }
}
