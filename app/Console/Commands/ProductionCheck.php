<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProductionCheck extends Command
{
    protected $signature = 'motorclub:production-check {--staging : Verificar homologação restrita}';

    protected $description = 'Verifica requisitos mínimos para publicação, sem revelar segredos.';

    public function handle(): int
    {
        $staging = (bool) $this->option('staging');
        $url = rtrim(config('app.url'), '/');
        $checks = [
            'Ambiente correto' => app()->environment($staging ? 'staging' : 'production'),
            'Acesso restrito na homologação' => ! $staging || (filled(config('production.staging_user')) && strlen((string) config('production.staging_password')) >= 16),
            'Depuração desativada' => ! config('app.debug'),
            'Chave da aplicação configurada' => filled(config('app.key')),
            'Endereço público com HTTPS' => str_starts_with($url, 'https://') && ! in_array(parse_url($url, PHP_URL_HOST), ['localhost', '127.0.0.1']),
            'Cookies seguros' => (bool) config('session.secure'),
            'Sessões criptografadas' => (bool) config('session.encrypt'),
            'Modo de testes coerente com o ambiente' => (bool) config('production.test_mode') === $staging,
            'Demonstração desativada' => ! config('production.demo_enabled'),
            'Google configurado para este domínio' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret')) && config('services.google.redirect') === $url.'/auth/google/callback',
            'Contato de suporte configurado' => (bool) filter_var(config('production.support_email'), FILTER_VALIDATE_EMAIL),
            'Persistência das fotos confirmada pelo operador' => (bool) config('production.storage_persistent'),
            'Restauração de backup confirmada pelo operador' => (bool) config('production.backups_verified'),
        ];
        try {
            DB::select('SELECT 1');
            $checks['Banco acessível'] = true;
            $checks['Base sem contas fictícias'] = ! DB::table('users')->where('is_demo', true)->exists();
        } catch (\Throwable) {
            $checks['Banco acessível e migrado'] = false;
        }
        foreach ($checks as $label => $ok) {
            $this->line(($ok ? '[OK] ' : '[PENDENTE] ').$label);
        }
        $this->warn('Esta verificação não substitui teste externo de HTTPS, OAuth, restauração e revisão das páginas legais.');

        return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
    }
}
