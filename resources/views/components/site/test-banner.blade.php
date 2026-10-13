@if(config('production.test_mode'))
<div role="status" class="bg-amber-100 px-6 py-3 text-center text-sm font-semibold text-zinc-900">Ambiente de testes · Não realize pagamentos. Dados de demonstração não representam clientes reais. <a class="underline" href="{{ route('testing.guide') }}">Como testar</a></div>
@endif
