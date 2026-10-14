<x-error-page
    code="500"
    title="Algo saiu do trajeto"
    description="Ocorreu um problema interno no Autoverse. Já estamos tratando isso ou você pode tentar novamente em instantes."
    variant="workshop"
    illustration-label="Carro do Autoverse em uma área de manutenção"
    :actions="[
        ['label' => 'Tentar novamente', 'type' => 'reload', 'style' => 'primary'],
        ['label' => 'Página inicial', 'href' => url('/')],
    ]"
 />
