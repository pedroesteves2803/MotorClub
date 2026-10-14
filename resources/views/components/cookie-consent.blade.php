@if(config('legal.optional_cookies_available') && !request()->cookie('autoverse_cookie_consent_id'))
<aside class="mc-cookie-banner" role="dialog" aria-labelledby="cookie-title" data-cookie-banner>
    <div><h2 id="cookie-title" class="text-lg font-bold">Sua privacidade importa</h2><p class="mt-2 text-sm mc-muted">Utilizamos cookies necessários para o funcionamento do Autoverse. Com sua permissão, também podemos utilizar cookies opcionais para melhorar sua experiência e entender como a plataforma é utilizada.</p></div>
    <div class="mt-4 flex flex-wrap gap-3"><form method="POST" action="{{ route('cookies.preferences') }}">@csrf<button class="mc-link-button" name="analytics" value="1">Aceitar todos</button></form><form method="POST" action="{{ route('cookies.preferences') }}">@csrf<button class="mc-text-link" name="analytics" value="0">Rejeitar opcionais</button></form><a class="mc-text-link" href="{{ route('cookies.settings') }}">Personalizar</a></div>
</aside>
@endif
