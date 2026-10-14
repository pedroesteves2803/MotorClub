<x-filament-panels::page>
    <div class="space-y-6">
        <section class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[.03] sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[.16em] text-primary-600 dark:text-primary-400">Visão protegida do ambiente</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">Tudo o que mantém o Autoverse funcionando</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">Verifique banco, armazenamento, servidor e integrações em um só lugar. Chaves, senhas e tokens nunca são exibidos.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 dark:text-gray-400">Atualizado em {{ $checkedAt }}</span>
                <x-filament::button type="button" wire:click="refreshChecks" wire:loading.attr="disabled">Atualizar</x-filament::button>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([['label' => 'Operacionais', 'value' => $summary['ok'] ?? 0, 'class' => 'text-success-600 dark:text-success-400'], ['label' => 'Para revisar', 'value' => $summary['warning'] ?? 0, 'class' => 'text-warning-600 dark:text-warning-400'], ['label' => 'Com falha', 'value' => $summary['error'] ?? 0, 'class' => 'text-danger-600 dark:text-danger-400']] as $stat)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[.03]">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold {{ $stat['class'] }}">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($checks as $check)
                @php
                    $tone = match ($check['status']) {
                        'ok' => ['badge' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400', 'dot' => 'bg-success-500'],
                        'warning' => ['badge' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400', 'dot' => 'bg-warning-500'],
                        'error' => ['badge' => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400', 'dot' => 'bg-danger-500'],
                        default => ['badge' => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-white/10 dark:text-gray-300', 'dot' => 'bg-gray-400'],
                    };
                @endphp
                <article class="flex min-h-56 flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[.03]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $tone['dot'] }}" aria-hidden="true"></span>
                            <h3 class="font-semibold text-gray-950 dark:text-white">{{ $check['label'] }}</h3>
                        </div>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $tone['badge'] }}">{{ $check['status_label'] }}</span>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $check['detail'] }}</p>
                    <dl class="mt-auto grid gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
                        @foreach ($check['meta'] as $label => $value)
                            <div class="flex items-start justify-between gap-4 text-xs">
                                <dt class="text-gray-500 dark:text-gray-500">{{ $label }}</dt>
                                <dd class="text-right font-medium text-gray-800 dark:text-gray-200">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </article>
            @endforeach
        </div>

        <aside class="rounded-2xl border border-primary-200 bg-primary-50/70 p-5 text-sm leading-6 text-primary-950 dark:border-primary-400/20 dark:bg-primary-400/10 dark:text-primary-100">
            <strong>Privacidade do diagnóstico</strong>
            <p class="mt-1">Esta tela informa apenas presença de configuração, driver e resposta dos serviços. Valores de ambiente, senhas, tokens e chaves permanecem ocultos.</p>
        </aside>
    </div>
</x-filament-panels::page>
