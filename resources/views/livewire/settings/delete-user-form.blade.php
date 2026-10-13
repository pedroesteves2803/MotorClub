<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';
    public string $confirmation = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        if (Auth::user()->google_id) {
            if ((int) session('google_authenticated_at', 0) < now()->subMinutes(10)->timestamp) {
                $this->addError('confirmation', 'Saia e entre novamente com Google antes de excluir sua conta.');
                return;
            }
            $this->validate(['confirmation' => ['required', 'in:EXCLUIR']]);
        } else {
            $this->validate(['password' => ['required', 'string', 'current_password']]);
        }

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Excluir conta') }}</flux:heading>
        <flux:subheading>{{ __('Exclua sua conta e todos os seus dados') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('Excluir conta') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Tem certeza de que deseja excluir sua conta?') }}</flux:heading>

                <flux:subheading>
                    Sua conta e seus dados serão excluídos permanentemente. Confirme abaixo para continuar.
                </flux:subheading>
            </div>

            @if (auth()->user()->google_id)
                <p>Para confirmar, digite EXCLUIR. A exclusão é permanente.</p>
                <flux:input wire:model="confirmation" label="Confirmação" />
            @else
            <flux:input wire:model="password" id="password" label="{{ __('Senha') }}" type="password" name="password" />
            @endif

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Excluir conta') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
