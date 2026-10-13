<?php

it('publica metadados essenciais da home em pt-BR', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<html lang="pt-BR">', false)
        ->assertSee('name="description"', false)
        ->assertSee('name="robots" content="index,follow"', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('name="twitter:card"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('"@type":"WebSite"', false);
});

it('serve robots.txt com áreas privadas bloqueadas e sitemap', function () {
    $response = $this->get(route('seo.robots'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /configuracoes')
        ->assertSee('Disallow: /shots/fotos/*/download')
        ->assertSee('Sitemap: '.route('seo.sitemap'));
});

it('serve sitemap XML com páginas públicas estáveis', function () {
    $response = $this->get(route('seo.sitemap'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
        ->assertSee('<loc>'.route('home').'</loc>', false)
        ->assertDontSee('/configuracoes', false)
        ->assertDontSee('/shots/fotos/1/download', false);
});
