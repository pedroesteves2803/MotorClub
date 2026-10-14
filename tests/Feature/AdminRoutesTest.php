<?php

use App\Models\User;

it('usa o painel em admin e compartilha o guard da aplicação', function () {
    $admin = User::factory()->create(['admin_role' => 'super_admin']);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();

    expect(auth('web')->id())->toBe($admin->id);
});

it('redireciona visitantes ao login principal e preserva o destino', function () {
    $this->get('/admin')
        ->assertRedirect(route('login'))
        ->assertSessionHas('url.intended', url('/admin'));

    $this->get('/admin/login')->assertNotFound();
});

it('nega o painel e seus recursos a usuários comuns', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
    $this->actingAs($user)->get('/admin/usuarios')->assertForbidden();
    $this->actingAs($user)->get('/admin/funcionalidades/create')->assertForbidden();
});

it('mantém a rota administrativa antiga como redirecionamento', function () {
    $this->actingAs(User::factory()->create(['admin_role' => 'super_admin']))
        ->get('/central-mc-gestao')
        ->assertRedirect('/admin');
});

it('encerra a sessão compartilhada pelo logout do painel', function () {
    $this->actingAs(User::factory()->create(['admin_role' => 'super_admin']))
        ->post('/admin/logout')
        ->assertRedirect('/admin');

    $this->assertGuest();
    $this->get('/admin')->assertRedirect(route('login'));
});

it('não permite que o perfil altere a própria função administrativa', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profiles.update'), [
            'bio' => 'Perfil atualizado',
            'city' => 'Curitiba',
            'profile_public' => true,
            'admin_role' => 'super_admin',
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->admin_role)->toBe('user');
});

it('exibe o link de administração apenas para usuários autorizados', function () {
    $this->actingAs(User::factory()->create(['admin_role' => 'super_admin']))
        ->get('/')
        ->assertSee('Administração')
        ->assertSee(route('filament.admin.pages.dashboard'), false);

    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertDontSee('Administração');
});
