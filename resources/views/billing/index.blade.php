<x-layouts.app title="Plano para oficinas — MotorClub">
    <p class="mc-muted text-sm uppercase tracking-widest">MotorClub para oficinas</p>
    <h1 class="mc-title mt-3 text-4xl">Seu trabalho merece ser visto.</h1>
    <p class="mt-5 max-w-2xl">Reúna seus projetos, apresente sua oficina e receba pedidos de orçamento em um só lugar.</p>
    <div class="mc-notice mt-6 rounded-xl p-4" role="status">Pagamento em teste. Nenhum dinheiro real será movimentado. O uso do MotorClub continua liberado durante a homologação, independentemente da assinatura simulada.</div>
    @if(session('status'))<p role="status" class="mt-4">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert" class="mc-notice mt-4 p-4">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @if(request('retorno') === 'sucesso')<p role="status" class="mt-4">Você voltou do checkout. A assinatura só será confirmada após a notificação da Stripe. Atualize esta página em alguns instantes.</p>@endif
    @if(request('retorno') === 'cancelado')<p role="status" class="mt-4">Você saiu do checkout. Nenhum plano foi ativado por este retorno.</p>@endif
    <div class="mt-10 grid gap-8 md:grid-cols-2">
        <section class="mc-surface rounded-2xl p-8">
            <h2 class="text-2xl font-bold">Oficina MotorClub</h2>
            <p class="my-6"><span class="text-4xl font-bold">R$ 99</span> / mês</p>
            <ul class="list-disc space-y-3 pl-5"><li>Perfil público da oficina</li><li>Portfólio de projetos com vínculos aprovados</li><li>Caixa de entrada de pedidos de orçamento</li><li>Gerenciamento da assinatura no portal Stripe</li></ul>
            <p class="mc-muted mt-6 text-sm">Valor de referência para o piloto. Nesta versão, a contratação, as renovações e os cancelamentos são apenas testes.</p>
            @guest
                <a href="{{ route('login') }}" class="mc-link-button mt-6 inline-block">Entrar com Google</a>
            @else
                @if(!$workshop)
                    <a href="{{ route('workshops.edit') }}" class="mc-link-button mt-6 inline-block">Cadastrar minha oficina</a>
                @elseif(!$configured)
                    <p class="mt-6 font-semibold">Checkout aguardando configuração das credenciais de teste.</p>
                @elseif(!$account || in_array($account->status, ['none', 'pending', 'canceled', 'incomplete_expired']))
                    <form method="POST" action="{{ route('billing.checkout') }}" class="mt-6 space-y-4">@csrf
                        <label class="flex gap-3"><input type="checkbox" name="consent" value="1" required><span>Entendi que esta é uma assinatura mensal simulada de R$ 99,00, sem cobrança real.</span></label>
                        <button class="mc-link-button" type="submit">{{ $account?->status === 'pending' ? 'Continuar contratação de teste' : 'Testar assinatura' }}</button>
                    </form>
                @endif
            @endguest
        </section>
        <section class="mc-surface rounded-2xl p-8">
            <h2 class="text-2xl font-bold">Minha assinatura</h2>
            <p class="mt-6 text-lg font-semibold">{{ $account?->label() ?? 'Ainda não contratada' }}</p>
            @if($account?->period_end)<p class="mt-3">{{ $account->cancel_at_period_end ? 'Cancelamento programado para' : 'Fim do período atual' }}: {{ $account->period_end->timezone('America/Sao_Paulo')->format('d/m/Y') }}.</p>@endif
            @if($configured && $account?->customer_id)
                <form method="POST" action="{{ route('billing.portal') }}" class="mt-6">@csrf<button type="submit" class="mc-link-button">Gerenciar ou cancelar na Stripe</button></form>
            @endif
            <p class="mc-muted mt-6">Dados de cartão são preenchidos somente na página da Stripe. Não informe cartões reais no ambiente de testes.</p>
            <p class="mc-muted mt-4">O pagamento de serviços automotivos continua sendo combinado diretamente com a oficina.</p>
        </section>
    </div>
</x-layouts.app>
