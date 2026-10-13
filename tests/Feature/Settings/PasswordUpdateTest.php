<?php

use App\Models\User;

it('redirects the old password settings to profile', function () {
    $this->actingAs(User::factory()->create())->get('/settings/password')->assertRedirect('/configuracoes/perfil');
});
