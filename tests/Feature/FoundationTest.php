<?php

use App\Models\User;

it('requires email verification before entering the dashboard', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('apresenta a proposta da V1', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Seu carro merece mais que')
        ->assertSee('uma publicação.')
        ->assertSee('Seu universo')
        ->assertSee('Explorar a comunidade');
});
