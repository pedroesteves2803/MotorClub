<?php

namespace App\Filament\Resources\Feedbacks;

use App\Filament\Resources\Feedbacks\Pages\EditFeedback;
use App\Filament\Resources\Feedbacks\Pages\ListFeedbacks;
use App\Filament\Resources\Feedbacks\Schemas\FeedbackForm;
use App\Filament\Resources\Feedbacks\Tables\FeedbacksTable;
use App\Models\Feedback;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $modelLabel = 'feedback';

    protected static ?string $pluralModelLabel = 'Feedbacks';

    protected static ?string $navigationLabel = 'Feedbacks';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'feedbacks';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return FeedbackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedbacksTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListFeedbacks::route('/'), 'edit' => EditFeedback::route('/{record}/edit')];
    }
}
