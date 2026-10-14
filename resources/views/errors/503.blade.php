<x-error-page
    code="503"
    title="Estamos em manutenção na pista"
    description="O Autoverse está temporariamente indisponível. Tente novamente em alguns minutos."
    variant="pause"
    illustration-label="Carro do Autoverse parado em um box de manutenção"
    :actions="[
        ['label' => 'Atualizar página', 'type' => 'reload', 'style' => 'primary'],
        ['label' => 'Voltar mais tarde', 'type' => 'back'],
    ]"
 />
