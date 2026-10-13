<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Entre no MotorClub" description="Use sua conta Google para entrar ou se cadastrar." />
    @error('google')
        <p role="alert" class="rounded-lg border border-red-300 p-4 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    @if(app()->environment('local') && filled(config('services.google.redirect')) && request()->getHost() !== parse_url(config('services.google.redirect'), PHP_URL_HOST))
    <p class="mc-notice rounded-xl p-5">Esta é uma prévia na rede local. Você pode explorar carros, projetos, oficinas e eventos. O acesso com Google está disponível no computador que hospeda o site.</p><a class="mc-text-link" href="{{ route('community') }}">Explorar a comunidade →</a>
    @else
    <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-3 rounded-lg border border-zinc-300 bg-white px-5 py-3 font-semibold text-zinc-900 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-offset-4">
        Continuar com Google
    </a>
    <p class="text-center text-sm mc-muted">Primeiro acesso? Sua conta será criada automaticamente. Você não precisa criar uma senha.</p>
    @endif
</div>
