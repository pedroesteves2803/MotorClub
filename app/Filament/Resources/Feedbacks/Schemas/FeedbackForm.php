<?php

namespace App\Filament\Resources\Feedbacks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Mensagem')->schema([
                Select::make('category')->label('Categoria')->options([
                    'suggestion' => 'Sugestão de melhoria', 'feature' => 'Nova funcionalidade', 'bug' => 'Bug',
                    'usability' => 'Usabilidade', 'praise' => 'Elogio', 'other' => 'Outro',
                ])->required(),
                TextInput::make('title')->label('Título')->disabled(),
                Textarea::make('description')->label('Descrição')->disabled()->rows(8),
                TextInput::make('email')->label('E-mail para retorno')->disabled(),
                TextInput::make('image_path')->label('Anexo privado')->disabled(),
            ])->columns(2),
            Section::make('Moderação')->schema([
                Select::make('status')->label('Status')->options([
                    'received' => 'Recebido', 'reviewing' => 'Em análise', 'planned' => 'Planejado',
                    'in_progress' => 'Em desenvolvimento', 'resolved' => 'Resolvido', 'discarded' => 'Descartado',
                ])->required(),
                DateTimePicker::make('resolved_at')->label('Resolvido em'),
                Textarea::make('internal_notes')->label('Observações internas')->rows(5)->columnSpanFull(),
            ]),
        ]);
    }
}
