@if(config('legal.optional_cookies_available') && !request()->cookie('autoverse_cookie_consent_id'))
<aside class="mc-cookie-banner" role="dialog" aria-labelledby="cookie-title" data-cookie-banner>
    <div><h2 id="cookie-title" class="text-lg font-bold">Sua privacidade importa</h2><p class="mt-2 text-sm mc-muted">Utilizamos cookies necessários para o funcionamento do Autoverse. Com sua autorização, também podemos utilizar cookies opcionais para melhorar sua experiência.</p></div>
    <div class="mt-4 flex flex-wrap items-center gap-3"><form method="POST" action="{{ route('cookies.preferences') }}">@csrf<input type="hidden" name="analytics" value="1"><input type="hidden" name="personalization" value="1"><button class="mc-link-button">Aceitar opcionais</button></form><form method="POST" action="{{ route('cookies.preferences') }}">@csrf<button class="mc-text-link">Rejeitar opcionais</button></form><a class="mc-text-link" href="{{ route('legal.privacy.short') }}#cookies">Política de Privacidade</a></div>
</aside>
@endif
