<?php

namespace App\Filament\Resources\ShotPurchaseRequests;

use App\Filament\Resources\ShotPurchaseRequests\Pages\ListShotPurchaseRequests;
use App\Filament\Resources\ShotPurchaseRequests\Schemas\ShotPurchaseRequestForm;
use App\Filament\Resources\ShotPurchaseRequests\Tables\ShotPurchaseRequestsTable;
use App\Models\ShotPurchaseRequest;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShotPurchaseRequestResource extends Resource
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    protected static ?string $modelLabel = 'pagamento';

    protected static ?string $pluralModelLabel = 'Pagamentos';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'pagamentos';
    }

    protected static ?string $model = ShotPurchaseRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Shots';

    protected static ?string $navigationLabel = 'Pagamentos';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ShotPurchaseRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShotPurchaseRequestsTable::configure($table);
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
            'index' => ListShotPurchaseRequests::route('/'),
        ];
    }
}
