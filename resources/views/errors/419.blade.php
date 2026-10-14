<x-error-page
    code="419"
    title="Sua sessão expirou"
    description="Por segurança, sua sessão terminou. Faça login novamente para continuar."
    variant="restart"
    illustration-label="Carro do Autoverse aguardando uma nova partida"
    :actions="[
        ['label' => 'Fazer login novamente', 'href' => route('login'), 'style' => 'primary'],
        ['label' => 'Página inicial', 'href' => url('/')],
    ]"
 />
