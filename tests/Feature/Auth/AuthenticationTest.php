<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Livewire\Volt\Volt;

beforeEach(function () {
    config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'test-secret', 'services.google.redirect' => 'http://localhost/auth/google/callback']);
});

function googleIdentity(bool $verified = true): GoogleUser
{
    return (new GoogleUser)->setRaw(['email_verified' => $verified])->map(['id' => 'google-123', 'name' => 'Pessoa Teste', 'email' => 'pessoa@example.com']);
}

it('offers Google without a password form', function () {
    $this->get('/login')->assertOk()->assertSee('Continuar com Google')->assertDontSee('wire:submit="login"', false);
});

it('creates a verified account at the first Google login', function () {
    Socialite::shouldReceive('driver->user')->once()->andReturn(googleIdentity());
    $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));
    $user = User::sole();
    expect($user->google_id)->toBe('google-123');
    expect($user->hasVerifiedEmail())->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

it('reuses the account identified by Google', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);
    Socialite::shouldReceive('driver->user')->once()->andReturn(googleIdentity());
    $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    expect(User::count())->toBe(1);
});

it('does not link an existing email automatically', function () {
    User::factory()->create(['email' => 'pessoa@example.com']);
    Socialite::shouldReceive('driver->user')->once()->andReturn(googleIdentity());
    $this->get('/auth/google/callback')->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->assertGuest();
    expect(User::sole()->google_id)->toBeNull();
});

it('rejects unverified Google emails', function () {
    Socialite::shouldReceive('driver->user')->once()->andReturn(googleIdentity(false));
    $this->get('/auth/google/callback')->assertSessionHasErrors('google');
    $this->assertGuest();
    expect(User::count())->toBe(0);
});

it('rejects invalid OAuth state', function () {
    Socialite::shouldReceive('driver->user')->once()->andThrow(new InvalidStateException);
    $this->get('/auth/google/callback')->assertSessionHasErrors('google');
    $this->assertGuest();
});

it('handles cancellation without creating an account', function () {
    Socialite::shouldReceive('driver')->never();
    $this->get('/auth/google/callback?error=access_denied')->assertRedirect(route('login'))->assertSessionHasErrors('google');
    expect(User::count())->toBe(0);
});

it('handles missing configuration', function () {
    config(['services.google.client_id' => null]);
    $this->get('/auth/google')->assertRedirect(route('login'))->assertSessionHasErrors('google');
});

it('redirects to Google using session state', function () {
    $response = $this->get('/auth/google');
    $response->assertRedirect();
    expect($response->headers->get('Location'))->toStartWith('https://accounts.google.com/');
    $response->assertSessionHas('state');
});

it('allows logout', function () {
    $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

it('keeps the Google account email immutable in profile updates', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);
    $this->actingAs($user);
    Volt::test('settings.profile')->set('name', 'Novo nome')->set('email', 'outro@example.com')->call('updateProfileInformation')->assertHasNoErrors();
    expect($user->fresh()->email)->toBe($user->email);
});

it('requires recent Google login before account deletion', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);
    $this->actingAs($user);
    Volt::test('settings.delete-user-form')->set('confirmation', 'EXCLUIR')->call('deleteUser')->assertHasErrors('confirmation');
    expect($user->fresh())->not->toBeNull();
});

it('deletes a Google account with recent login and explicit confirmation', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);
    $this->actingAs($user)->withSession(['google_authenticated_at' => now()->timestamp]);
    Volt::test('settings.delete-user-form')->set('confirmation', 'EXCLUIR')->call('deleteUser')->assertHasNoErrors();
    expect($user->fresh())->toBeNull();
    $this->assertGuest();
});
