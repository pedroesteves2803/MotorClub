<x-error-page
    code="422"
    title="Confira as informações"
    description="Não foi possível concluir esta operação com os dados enviados. Revise os campos e tente novamente."
    variant="blocked"
    illustration-label="Carro do Autoverse parado enquanto os dados são conferidos"
    :actions="[
        ['label' => 'Voltar', 'type' => 'back'],
        ['label' => 'Página inicial', 'href' => url('/'), 'style' => 'primary'],
    ]"
 />
