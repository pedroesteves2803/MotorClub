<x-error-page
    code="403"
    title="Você não tem acesso a esta garagem"
    description="Esta área não está disponível para o seu perfil ou você não tem permissão para acessar este conteúdo."
    variant="blocked"
    illustration-label="Carro do Autoverse diante de uma barreira de acesso"
    :actions="[
        ['label' => 'Voltar', 'type' => 'back'],
        ['label' => 'Minha garagem', 'href' => url('/garagem'), 'style' => 'primary'],
        ['label' => 'Página inicial', 'href' => url('/')],
    ]"
 />
