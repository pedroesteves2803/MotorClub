<?php

namespace App\Filament\Resources\ShotPurchaseRequests\Tables;

use App\Models\ShotPurchaseRequest;
use App\Services\ShotPaymentManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShotPurchaseRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Pedido')
                    ->sortable(),
                TextColumn::make('buyer.email')->label('Comprador')->searchable(),
                TextColumn::make('photo.gallery.user.photographerProfile.professional_name')->label('Fotógrafo'),
                TextColumn::make('vehicle_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ['pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Recusado', 'declined' => 'Recusado', 'refunded' => 'Estornado'][$state] ?? $state)
                    ->searchable(),
                TextColumn::make('quoted_price')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('photographer_amount')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('platform_amount')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('authorized_at')
                    ->label('Aprovado em')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(['pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Recusado', 'refunded' => 'Estornado']),
            ])
            ->recordActions([
                Action::make('approve')->label('Aprovar')->color('success')->visible(fn (ShotPurchaseRequest $record) => $record->status === 'pending' && auth()->user()?->isSuperAdmin())->requiresConfirmation()->action(fn (ShotPurchaseRequest $record) => app(ShotPaymentManager::class)->approve($record, auth()->user())),
                Action::make('reject')->label('Recusar')->color('danger')->visible(fn (ShotPurchaseRequest $record) => $record->status === 'pending' && auth()->user()?->isSuperAdmin())->schema([Textarea::make('reason')->label('Justificativa')->required()])->action(fn (ShotPurchaseRequest $record, array $data) => app(ShotPaymentManager::class)->reject($record, auth()->user(), $data['reason'])),
                Action::make('refund')->label('Estornar')->color('warning')->visible(fn (ShotPurchaseRequest $record) => $record->status === 'approved' && auth()->user()?->isSuperAdmin())->requiresConfirmation()->schema([Textarea::make('reason')->label('Justificativa')->required()])->action(fn (ShotPurchaseRequest $record, array $data) => app(ShotPaymentManager::class)->refund($record, auth()->user(), $data['reason'])),
            ]);
    }
}
