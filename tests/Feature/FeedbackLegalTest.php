<?php

use App\Models\CookieConsent;
use App\Models\Feedback;
use App\Models\LegalDocument;
use App\Models\TermsAcceptance;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

it('publica os documentos institucionais como documentos vigentes', function () {
    foreach (['termos-de-uso', 'politica-de-privacidade', 'politica-de-cookies', 'diretrizes-da-comunidade', 'ajuda-e-contato'] as $path) {
        $this->get('/'.$path)->assertOk()->assertSee('Autoverse')->assertDontSee('Minuta')->assertDontSee('Revisão pendente');
    }
    $this->get('/configuracoes-de-cookies')->assertOk()->assertSee('Necessários');
});

it('sanitiza HTML de documentos legais publicados', function () {
    LegalDocument::create([
        'slug' => 'termos',
        'title' => 'Termos revisados',
        'version' => '2026.11',
        'content' => '<p>Conteúdo seguro</p><script>alert(1)</script><a href="javascript:alert(2)" onclick="alert(3)">Link</a>',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get('/termos-de-uso')
        ->assertOk()
        ->assertSee('Conteúdo seguro')
        ->assertDontSee('alert(1)', false)
        ->assertDontSee('javascript:', false)
        ->assertDontSee('onclick=', false);
});

it('recebe feedback de visitante com proteção de anexo', function () {
    Storage::fake('local');
    $response = $this->post('/feedback', [
        'category' => 'bug',
        'title' => 'A página demorou para responder',
        'description' => 'A tela ficou sem resposta ao abrir a garagem.',
        'email' => 'visitante@example.com',
    ]);

    $response->assertRedirect(route('feedback.create'));
    expect(Feedback::sole()->status)->toBe('received')->and(Feedback::sole()->user_id)->toBeNull();
});

it('salva preferências de cookies e permite revogar opções', function () {
    $response = $this->post('/configuracoes-de-cookies', ['analytics' => 1, 'personalization' => 1]);
    $response->assertRedirect()->assertCookie('autoverse_cookie_consent_id');
    expect(CookieConsent::sole()->analytics)->toBeTrue();

    $consentCookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'autoverse_cookie_consent_id');
    $response = $this->withCookie('autoverse_cookie_consent_id', $consentCookie->getValue())
        ->post('/configuracoes-de-cookies', []);
    $response->assertRedirect();
    expect(CookieConsent::count())->toBe(2)->and(CookieConsent::latest('id')->first()->analytics)->toBeFalse();
});

it('exige a aceitação dos termos antes de iniciar o Google', function () {
    config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'test-secret', 'services.google.redirect' => 'http://localhost/auth/google/callback']);
    $this->post('/auth/google/start')->assertSessionHasErrors('terms_accepted');
    $this->post('/auth/google/start', ['terms_accepted' => 1])->assertRedirect();
});

it('registra termos e diretrizes aceitos no primeiro acesso Google', function () {
    config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'test-secret', 'services.google.redirect' => 'http://localhost/auth/google/callback']);
    $google = (new GoogleUser)->setRaw(['email_verified' => true])->map(['id' => 'google-new', 'name' => 'Pessoa Nova', 'email' => 'nova@example.com']);
    Socialite::shouldReceive('driver->user')->once()->andReturn($google);

    $this->withSession(['terms_consent' => ['version' => config('legal.terms_version'), 'community_version' => config('legal.community_version')]])
        ->get('/auth/google/callback')->assertRedirect(route('dashboard'));

    $user = User::where('email', 'nova@example.com')->sole();
    expect(TermsAcceptance::where('user_id', $user->id)->pluck('document_type')->all())->toEqualCanonicalizing(['terms', 'community']);
});
