<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Entre na Autoverse" description="Use sua conta Google para entrar ou se cadastrar." />
    @error('google')
        <p role="alert" class="rounded-lg border border-red-300 p-4 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    @if(app()->environment('local') && filled(config('services.google.redirect')) && request()->getHost() !== parse_url(config('services.google.redirect'), PHP_URL_HOST))
    <p class="mc-notice rounded-xl p-5">Esta é uma prévia na rede local. Você pode explorar carros, projetos, oficinas e eventos. O acesso com Google está disponível no computador que hospeda o site.</p><a class="mc-text-link" href="{{ route('community') }}">Explorar a comunidade →</a>
    @else
    <form method="POST" action="{{ route('auth.google.start') }}" class="space-y-4">
        @csrf
        <label class="flex items-start gap-3 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            <input type="checkbox" name="terms_accepted" value="1" required class="mt-1 rounded border-zinc-300">
            <span>Li e concordo com os <a class="underline" href="{{ route('legal.terms') }}" target="_blank" rel="noopener">Termos de Uso</a> e o <a class="underline" href="{{ route('legal.regulation') }}" target="_blank" rel="noopener">Regulamento da Comunidade</a> do Autoverse.<br><span class="mc-muted">Seus dados serão tratados conforme nossa <a class="underline" href="{{ route('legal.privacy.short') }}" target="_blank" rel="noopener">Política de Privacidade</a>.</span></span>
        </label>
        <button type="submit" class="flex w-full items-center justify-center gap-3 rounded-lg border border-zinc-300 bg-white px-5 py-3 font-semibold text-zinc-900 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-offset-4">
            Continuar com Google
        </button>
    </form>
    <p class="text-center text-sm mc-muted">Primeiro acesso? Sua conta será criada automaticamente. Você não precisa criar uma senha.</p>
    @endif
</div>
