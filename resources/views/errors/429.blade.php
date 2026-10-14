<x-error-page
    code="429"
    title="Calma, você acelerou rápido demais"
    description="Recebemos muitas tentativas em pouco tempo. Aguarde alguns instantes e tente novamente."
    variant="speed"
    illustration-label="Carro do Autoverse com um aviso para reduzir a velocidade"
    :actions="[
        ['label' => 'Página inicial', 'href' => url('/'), 'style' => 'primary'],
        ['label' => 'Voltar', 'type' => 'back'],
    ]"
 />
