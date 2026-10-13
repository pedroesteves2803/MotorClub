<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')->label('E-mail confirmado em'),
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Deixe em branco ao editar para manter a senha atual.'),
                TextInput::make('google_id'),
                Toggle::make('is_demo')
                    ->label('Conta de demonstração')
                    ->required(),
                TextInput::make('account_type')
                    ->label('Tipo de conta')
                    ->required()
                    ->default('enthusiast'),
                Select::make('admin_role')
                    ->label('Acesso administrativo')
                    ->options(['user' => 'Usuário', 'moderator' => 'Moderador', 'super_admin' => 'Super Admin'])
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->required(),
                Textarea::make('bio')
                    ->label('Biografia')
                    ->columnSpanFull(),
                TextInput::make('city')->label('Cidade'),
                Toggle::make('profile_public')
                    ->label('Perfil público')
                    ->required(),
            ]);
    }
}
