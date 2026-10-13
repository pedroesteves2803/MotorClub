<?php

it('impede indexação em testes sem exibir um aviso público', function () {
    config(['production.test_mode' => true]);
    $this->get('/')->assertOk()->assertDontSee('Ambiente de testes')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    $this->get('/login')->assertOk()->assertDontSee('Ambiente de testes');
    $this->get('/como-testar')->assertOk()->assertSee('Seu roteiro de teste');
});

it('não exibe roteiro nem aviso fora do modo de testes', function () {
    config(['production.test_mode' => false]);
    $this->get('/')->assertOk()->assertDontSee('Ambiente de testes')->assertHeaderMissing('X-Robots-Tag');
    $this->get('/como-testar')->assertNotFound();
});

it('fecha homologação sem senha e exige credencial correta quando configurada', function () {
    app()->instance('env', 'staging');
    config(['production.staging_password' => null]);
    $this->get('/')->assertStatus(503);
    config(['production.staging_user' => 'avaliador', 'production.staging_password' => 'senha-exclusiva-de-teste']);
    $this->get('/')->assertUnauthorized();
    $this->withServerVariables(['PHP_AUTH_USER' => 'avaliador', 'PHP_AUTH_PW' => 'errada'])->get('/')->assertUnauthorized();
    $this->withServerVariables(['PHP_AUTH_USER' => 'avaliador', 'PHP_AUTH_PW' => 'senha-exclusiva-de-teste'])->get('/')->assertOk();
});
