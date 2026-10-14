<?php

namespace App\Filament\Resources\AdminAuditLogs;

use App\Filament\Resources\AdminAuditLogs\Pages\ListAdminAuditLogs;
use App\Models\AdminAuditLog;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminAuditLogResource extends Resource
{
    protected static ?string $model = AdminAuditLog::class;

    protected static ?string $modelLabel = 'registro de auditoria';

    protected static ?string $pluralModelLabel = 'Auditoria administrativa';

    protected static ?string $navigationLabel = 'Auditoria';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'auditoria';
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
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('admin.email')->label('Administrador')->searchable(),
            TextColumn::make('action')->label('Ação')->searchable(),
            TextColumn::make('auditable_type')->label('Tipo'),
            TextColumn::make('auditable_id')->label('ID'),
            TextColumn::make('created_at')->label('Quando')->dateTime()->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListAdminAuditLogs::route('/')];
    }
}
