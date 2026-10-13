<x-layouts.app title="Pagamento simulado — MotorClub">
    <p class="mc-muted text-sm uppercase tracking-widest">Assinatura para oficinas · Simulação local</p>
    <h1 class="mc-title mt-3 text-4xl">Teste o pagamento do começo ao fim.</h1>
    <div class="mc-notice mt-6 rounded-xl p-5">Nenhuma cobrança real. Não precisamos de cartão, banco ou conta Stripe. Os valores e comprovantes abaixo são fictícios. Todos os recursos do site continuam liberados.</div>
    @if(session('status'))<p role="status" class="mc-surface mt-4 rounded-xl p-4">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert" class="mc-notice mt-4 p-4">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="mt-8 grid gap-8 md:grid-cols-2">
        <section class="mc-surface rounded-2xl p-8">
            <h2 class="text-2xl font-bold">Oficina MotorClub</h2>
            <p class="my-5"><strong class="text-4xl">R$ 99</strong> / mês fictício</p>
            <ul class="list-disc space-y-2 pl-5"><li>Perfil da oficina e portfólio</li><li>Pedidos de orçamento</li><li>Assinatura mensal simulada</li></ul>
            <p class="mt-5">1. Inicie o checkout. 2. Escolha aprovação ou recusa. 3. Teste as próximas cobranças e o cancelamento.</p>
            @guest
                <a class="mc-link-button mt-6 inline-block" href="{{ route('login') }}">Entrar com Google para testar</a>
            @else
                @if(!$workshop)<a class="mc-link-button mt-6 inline-block" href="{{ route('workshops.edit') }}">Cadastrar minha oficina</a>@endif
            @endguest
        </section>
        <section class="mc-surface rounded-2xl p-8">
            <h2 class="text-2xl font-bold">Minha assinatura simulada</h2>
            <p class="mt-4 text-xl font-semibold">{{ $subscription?->label() ?? 'Não contratada' }}</p>
            @if($subscription?->period_end)<p class="mt-3">{{ $subscription->cancel_at_period_end ? 'Cancelamento programado' : 'Fim do período' }}: {{ $subscription->period_end->timezone('America/Sao_Paulo')->format('d/m/Y') }}.</p>@endif
            @if($workshop)
                @php
                    $state = $subscription?->status ?? 'none';
                    $buttons = match(true) {
                        in_array($state, ['none', 'canceled', 'refunded']) => ['checkout' => 'Iniciar checkout simulado'],
                        in_array($state, ['pending', 'past_due']) => ['approve' => 'Simular pagamento aprovado', 'decline' => 'Simular pagamento recusado', 'cancel' => 'Cancelar contratação'],
                        $state === 'active' && $subscription->cancel_at_period_end => ['resume' => 'Manter assinatura', 'end_period' => 'Simular chegada do fim do período', 'cancel' => 'Cancelar agora', 'refund' => 'Estornar último pagamento'],
                        $state === 'active' => ['renew' => 'Simular renovação aprovada', 'fail' => 'Simular renovação recusada', 'schedule_cancel' => 'Cancelar no fim do período', 'cancel' => 'Cancelar agora', 'refund' => 'Estornar último pagamento'],
                        default => [],
                    };
                @endphp
                <div class="mt-6 flex flex-col items-start gap-3">
                    @foreach($buttons as $action => $label)
                        <form method="POST" action="{{ route('billing.mock') }}">@csrf<input type="hidden" name="action" value="{{ $action }}"><input type="hidden" name="request_id" value="{{ (string) Illuminate\Support\Str::uuid() }}"><button type="submit" class="mc-link-button">{{ $label }}</button></form>
                    @endforeach
                </div>
            @endif
            <p class="mc-muted mt-5 text-sm">Os botões de renovação e fim do período avançam o cenário imediatamente. Cancelar não estorna pagamentos anteriores; o estorno possui uma ação própria e encerra esta assinatura simulada.</p>
        </section>
    </div>
    <section class="mc-surface mt-8 rounded-2xl p-6">
        <h2 class="text-2xl font-bold">Histórico de testes</h2>
        <p class="mc-muted mt-2">Registros fictícios. Não são recibos ou notas fiscais.</p>
        <div class="mt-5 space-y-4">
            @forelse($events as $event)
                <article class="flex flex-wrap justify-between gap-3 border-b border-zinc-300 pb-4"><div><p class="font-semibold">{{ $labels[$event->action] }}</p><p class="mc-muted text-sm">Simulação #{{ $event->id }} · {{ Carbon\Carbon::parse($event->created_at, 'UTC')->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</p></div><p>{{ $event->amount ? 'R$ '.number_format($event->amount / 100, 2, ',', '.') : 'Sem movimentação' }}</p></article>
            @empty
                <p>Seu histórico aparecerá após iniciar a primeira simulação.</p>
            @endforelse
        </div>
        @if($events instanceof Illuminate\Contracts\Pagination\Paginator)<div class="mt-5">{{ $events->links() }}</div>@endif
    </section>
</x-layouts.app>
