<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteAdmin extends Command
{
    protected $signature = 'motorclub:promote-admin {email : E-mail da conta existente} {role=super_admin : super_admin ou moderator}';

    protected $description = 'Concede uma função administrativa a uma conta existente.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $role = $this->argument('role');
        if (! in_array($role, ['super_admin', 'moderator'], true)) {
            $this->error('A função deve ser super_admin ou moderator.');

            return self::FAILURE;
        }

        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('Nenhuma conta encontrada com este e-mail.');

            return self::FAILURE;
        }

        $user->update(['admin_role' => $role]);
        $this->info("{$user->email} agora é {$role}.");

        return self::SUCCESS;
    }
}
