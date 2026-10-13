<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingAccount extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['cancel_at_period_end' => 'boolean', 'period_end' => 'datetime'];
    }

    public function label(): string
    {
        return match ($this->status) {
            'active' => 'Ativa em teste',
            'trialing' => 'Período de avaliação em teste',
            'past_due' => 'Pagamento de teste pendente',
            'unpaid' => 'Pagamento de teste não recebido',
            'canceled' => 'Cancelada',
            'incomplete_expired' => 'Contratação expirada',
            'incomplete' => 'Pagamento de teste incompleto',
            'paused' => 'Pausada',
            'pending' => 'Aguardando confirmação da Stripe',
            default => 'Ainda não contratada',
        };
    }
}
