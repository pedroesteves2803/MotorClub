<x-error-page
    code="404"
    title="Essa rota não foi encontrada"
    description="A página que você tentou acessar não existe, foi movida ou não está mais disponível."
    variant="detour"
    illustration-label="Carro do Autoverse seguindo uma placa de desvio"
    :actions="[
        ['label' => 'Voltar', 'type' => 'back'],
        ['label' => 'Página inicial', 'href' => url('/'), 'style' => 'primary'],
        ['label' => 'Explorar veículos', 'href' => url('/explorar')],
    ]"
 />
