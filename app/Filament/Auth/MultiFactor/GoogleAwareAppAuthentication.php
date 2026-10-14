<?php

namespace App\Filament\Auth\MultiFactor;

use App\Filament\Auth\MultiFactor\Actions\SetUpAppAuthenticationAction;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\Actions\DisableAppAuthenticationAction;
use Filament\Auth\MultiFactor\App\Actions\RegenerateAppAuthenticationRecoveryCodesAction;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;

/**
 * Keeps Filament's app-authentication provider while allowing OAuth-only users
 * to enroll after a recent Google login.
 */
class GoogleAwareAppAuthentication extends AppAuthentication
{
    /**
     * @return array<Action>
     */
    public function getActions(): array
    {
        $user = Filament::auth()->user();

        return [
            SetUpAppAuthenticationAction::make($this)
                ->hidden(fn (): bool => $this->isEnabled($user)),
            RegenerateAppAuthenticationRecoveryCodesAction::make($this)
                ->visible(fn (): bool => $this->isEnabled($user) && $this->isRecoverable() && $this->canRegenerateRecoveryCodes()),
            DisableAppAuthenticationAction::make($this)
                ->visible(fn (): bool => $this->isEnabled($user)),
        ];
    }
}
