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
        $request->session()->forget('google_login_context');

        return $this->redirectToGoogle($request, false);
    }

    public function adminRedirect(Request $request): RedirectResponse
    {
        $request->session()->put('google_login_context', 'admin');

        return $this->redirectToGoogle($request, true);
    }

    private function redirectToGoogle(Request $request, bool $admin): RedirectResponse
    {
        if (! $this->configured()) {
            return $this->redirectToLogin($admin)->withErrors(['google' => 'Não foi possível iniciar o acesso com Google. Tente novamente mais tarde.']);
        }

        if (app()->environment('local') && $request->getHost() !== parse_url(config('services.google.redirect'), PHP_URL_HOST)) {
            return $this->redirectToLogin($admin)->withErrors(['google' => 'Esta é uma prévia na rede local. Para entrar com Google, abra o Autoverse no computador que hospeda o site.']);
        }

        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $admin = $request->session()->pull('google_login_context') === 'admin';

        if (! $this->configured() || $request->has('error')) {
            return $this->redirectToLogin($admin)->withErrors(['google' => 'O acesso com Google não foi concluído. Tente novamente.']);
        }

        try {
            // Socialite validates the session-bound OAuth state. Never use stateless here.
            $identity = Socialite::driver('google')->user();
        } catch (Throwable) {
            // Provider exceptions may contain tokens; do not log their response body.
            return $this->redirectToLogin($admin)->withErrors(['google' => 'Não foi possível validar o acesso. Inicie novamente pelo botão Google.']);
        }

        $email = Str::lower((string) $identity->getEmail());
        if (! $identity->getId() || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ($identity->user['email_verified'] ?? $identity->user['verified_email'] ?? false) !== true) {
            return $this->redirectToLogin($admin)->withErrors(['google' => 'Use uma conta Google com e-mail verificado.']);
        }

        $googleId = (string) $identity->getId();
        $user = User::where('google_id', $googleId)->first();

        if ($admin) {
            if (! $user && ! in_array($email, config('motorclub.admin_emails', []), true)) {
                return $this->redirectToLogin(true)->withErrors(['google' => 'Esta conta Google não está autorizada para o painel administrativo.']);
            }

            if (! $user) {
                $user = User::where('email', $email)->first();

                if ($user && filled($user->google_id) && $user->google_id !== $googleId) {
                    return $this->redirectToLogin(true)->withErrors(['google' => 'A conta administrativa já está vinculada a outro acesso Google.']);
                }
            }

            if (! $user) {
                $user = new User;
                $user->name = Str::limit($identity->getName() ?: 'Administrador Autoverse', 255, '');
                $user->email = $email;
                $user->password = Str::random(64);
                $user->admin_role = 'super_admin';
            } elseif (Str::lower((string) $user->email) !== $email) {
                return $this->redirectToLogin(true)->withErrors(['google' => 'A conta Google não corresponde ao administrador autorizado.']);
            }

            $user->google_id = $googleId;
            $user->email_verified_at ??= now();
            $user->save();

            if (! $user->isSuperAdmin() && ! in_array($user->admin_role, ['super_admin', 'moderator'], true)) {
                return $this->redirectToLogin(true)->withErrors(['google' => 'Esta conta Google não está autorizada para o painel administrativo.']);
            }

            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->put('google_authenticated_at', now()->timestamp);

            return redirect()->intended(route('filament.admin.pages.dashboard'));
        }

        if (! $user) {
            // Matching email alone is not sufficient to link an existing local account.
            if (User::where('email', $email)->exists()) {
                return $this->redirectToLogin(false)->withErrors(['google' => 'Já existe uma conta com esse e-mail. Solicite suporte para vincular o Google com segurança.']);
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
                return $this->redirectToLogin(false)->withErrors(['google' => 'O cadastro foi atualizado em outra tentativa. Inicie o acesso novamente.']);
            }
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('google_authenticated_at', now()->timestamp);

        return redirect()->intended(route('dashboard'));
    }

    private function redirectToLogin(bool $admin): RedirectResponse
    {
        return redirect()->to($admin ? route('filament.admin.auth.login') : route('login'));
    }
}
