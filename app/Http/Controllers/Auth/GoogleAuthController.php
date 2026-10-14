<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    private function configured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->configured()) {
            return $this->redirectToLogin()->withErrors(['google' => 'Não foi possível iniciar o acesso com Google. Tente novamente mais tarde.']);
        }

        if (app()->environment('local') && $request->getHost() !== parse_url(config('services.google.redirect'), PHP_URL_HOST)) {
            return $this->redirectToLogin()->withErrors(['google' => 'Esta é uma prévia na rede local. Para entrar com Google, abra o Autoverse no computador que hospeda o site.']);
        }

        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->configured() || $request->has('error')) {
            return $this->redirectToLogin()->withErrors(['google' => 'O acesso com Google não foi concluído. Tente novamente.']);
        }

        try {
            // Socialite validates the session-bound OAuth state. Never use stateless here.
            $identity = Socialite::driver('google')->user();
        } catch (Throwable) {
            // Provider exceptions may contain tokens; do not log their response body.
            return $this->redirectToLogin()->withErrors(['google' => 'Não foi possível validar o acesso. Inicie novamente pelo botão Google.']);
        }

        $email = Str::lower((string) $identity->getEmail());
        if (! $identity->getId() || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ($identity->user['email_verified'] ?? $identity->user['verified_email'] ?? false) !== true) {
            return $this->redirectToLogin()->withErrors(['google' => 'Use uma conta Google com e-mail verificado.']);
        }

        $googleId = (string) $identity->getId();
        $user = User::where('google_id', $googleId)->first();

        if (! $user) {
            // Matching email alone is not sufficient to link an existing local account.
            if (User::where('email', $email)->exists()) {
                return $this->redirectToLogin()->withErrors(['google' => 'Já existe uma conta com esse e-mail. Solicite suporte para vincular o Google com segurança.']);
            }

            $user = new User;
            $user->name = Str::limit($identity->getName() ?: 'Membro Autoverse', 255, '');
            $user->email = $email;
            $user->google_id = (string) $identity->getId();
            $user->email_verified_at = now();
            // No usable local password is issued to Google accounts.
            $user->password = Str::random(64);
            try {
                $user->save();
            } catch (UniqueConstraintViolationException) {
                return $this->redirectToLogin()->withErrors(['google' => 'O cadastro foi atualizado em outra tentativa. Inicie o acesso novamente.']);
            }
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('google_authenticated_at', now()->timestamp);

        return redirect()->intended(route('dashboard'));
    }

    private function redirectToLogin(): RedirectResponse
    {
        return redirect()->to(route('login'));
    }
}
