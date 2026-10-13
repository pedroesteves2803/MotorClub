<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockSubscription extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['period_end' => 'datetime', 'cancel_at_period_end' => 'boolean'];
    }

    public static function enabled(): bool
    {
        return config('billing.driver') === 'mock' && app()->environment(['local', 'testing', 'staging']) && config('production.test_mode');
    }

    public function label(): string
    {
        return ['none' => 'Não contratada', 'pending' => 'Aguardando pagamento', 'active' => 'Ativa', 'past_due' => 'Pagamento recusado', 'canceled' => 'Cancelada', 'refunded' => 'Estornada'][$this->status] ?? 'Indisponível';
    }
}
