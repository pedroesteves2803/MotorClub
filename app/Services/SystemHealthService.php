<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Builds a safe, read-only snapshot of the services used by the application.
 *
 * The snapshot intentionally exposes configuration state and connection health,
 * never the contents of secrets, tokens, connection strings, or exception text.
 */
final class SystemHealthService
{
    /**
     * @return array{checked_at: string, summary: array<string, int>, checks: array<int, array<string, mixed>>}
     */
    public function snapshot(): array
    {
        $checks = [
            $this->applicationCheck(),
            $this->serverCheck(),
            $this->databaseCheck(),
            $this->storageCheck(),
            $this->cacheCheck(),
            $this->queueCheck(),
            $this->mailCheck(),
            $this->googleCheck(),
            $this->supabaseCheck(),
            $this->stripeCheck(),
        ];

        $summary = [
            'ok' => collect($checks)->where('status', 'ok')->count(),
            'warning' => collect($checks)->where('status', 'warning')->count(),
            'error' => collect($checks)->where('status', 'error')->count(),
        ];

        return [
            'checked_at' => now()->format('d/m/Y H:i:s'),
            'summary' => $summary,
            'checks' => $checks,
        ];
    }

    /** @return array<string, mixed> */
    private function applicationCheck(): array
    {
        $url = (string) config('app.url');
        $host = parse_url($url, PHP_URL_HOST);
        $keyConfigured = filled(config('app.key'));
        $urlConfigured = filled($host);

        return $this->item(
            'application',
            'Aplicação',
            $keyConfigured && $urlConfigured ? 'ok' : 'warning',
            $keyConfigured && $urlConfigured
                ? 'URL pública e chave da aplicação configuradas.'
                : 'Revise a URL pública e a chave da aplicação antes de publicar.',
            [
                'URL' => $host ?: 'Não configurada',
                'Chave da aplicação' => $keyConfigured ? 'Configurada' : 'Ausente',
            ],
        );
    }

    /** @return array<string, mixed> */
    private function serverCheck(): array
    {
        $debug = (bool) config('app.debug');

        return $this->item(
            'server',
            'Servidor',
            $debug && app()->environment('production') ? 'warning' : 'ok',
            $debug && app()->environment('production')
                ? 'O modo de depuração está ativo em produção.'
                : 'Runtime disponível para atender a aplicação.',
            [
                'PHP' => PHP_VERSION,
                'Laravel' => app()->version(),
                'Ambiente' => (string) config('app.env'),
                'Depuração' => $debug ? 'Ativa' : 'Desativada',
            ],
        );
    }

    /** @return array<string, mixed> */
    private function databaseCheck(): array
    {
        $connection = (string) config('database.default');
        $driver = (string) config("database.connections.{$connection}.driver", $connection);
        $startedAt = hrtime(true);

        try {
            DB::connection($connection)->select('select 1');
            $elapsedMs = round((hrtime(true) - $startedAt) / 1_000_000, 1);

            return $this->item('database', 'Banco de dados', 'ok', 'A conexão respondeu a uma consulta de saúde.', [
                'Conexão' => $connection ?: 'Padrão',
                'Driver' => $driver ?: 'Não informado',
                'Resposta' => $elapsedMs.' ms',
            ]);
        } catch (Throwable) {
            return $this->item('database', 'Banco de dados', 'error', 'Não foi possível abrir a conexão configurada.', [
                'Conexão' => $connection ?: 'Padrão',
                'Driver' => $driver ?: 'Não informado',
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function storageCheck(): array
    {
        $disk = (string) (config('filesystems.private_disk') ?: config('filesystems.default'));
        $driver = (string) config("filesystems.disks.{$disk}.driver");

        try {
            Storage::disk($disk);

            return $this->item('storage', 'Armazenamento privado', filled($disk) && filled($driver) ? 'ok' : 'warning', filled($disk) && filled($driver)
                ? 'O disco privado está disponível para arquivos e fotos.'
                : 'Configure um disco privado para manter os arquivos persistentes.', [
                'Disco' => $disk ?: 'Não configurado',
                'Driver' => $driver ?: 'Não informado',
            ]);
        } catch (Throwable) {
            return $this->item('storage', 'Armazenamento privado', 'error', 'O disco configurado não pôde ser inicializado.', [
                'Disco' => $disk ?: 'Não configurado',
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function cacheCheck(): array
    {
        $driver = (string) config('cache.default');

        return $this->item('cache', 'Cache', filled($driver) ? 'ok' : 'warning', filled($driver)
            ? 'Driver de cache configurado.'
            : 'Nenhum driver de cache foi configurado.', ['Driver' => $driver ?: 'Não configurado']);
    }

    /** @return array<string, mixed> */
    private function queueCheck(): array
    {
        $driver = (string) config('queue.default');

        return $this->item('queue', 'Fila de tarefas', filled($driver) ? 'ok' : 'warning', filled($driver)
            ? 'Driver de fila configurado.'
            : 'Nenhum driver de fila foi configurado.', ['Driver' => $driver ?: 'Não configurado']);
    }

    /** @return array<string, mixed> */
    private function mailCheck(): array
    {
        $driver = (string) config('mail.default');
        $from = (string) config('mail.from.address');

        return $this->item('mail', 'E-mail transacional', filled($driver) && filled($from) ? 'ok' : 'warning', filled($driver) && filled($from)
            ? 'Canal e remetente configurados.'
            : 'Configure o canal e o remetente antes de depender de e-mails.', [
            'Canal' => $driver ?: 'Não configurado',
            'Remetente' => filled($from) ? 'Configurado' : 'Ausente',
        ]);
    }

    /** @return array<string, mixed> */
    private function googleCheck(): array
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirect = config('services.google.redirect');
        $redirectHost = parse_url((string) $redirect, PHP_URL_HOST);
        $configured = filled($clientId) && filled($clientSecret) && filled($redirect);

        return $this->item('google', 'Google OAuth', $configured ? 'ok' : 'warning', $configured
            ? 'Login Google configurado sem expor credenciais.'
            : 'A integração Google está incompleta ou ausente.', [
            'Client ID' => filled($clientId) ? 'Configurado' : 'Ausente',
            'Client secret' => filled($clientSecret) ? 'Configurado' : 'Ausente',
            'Callback' => $redirectHost ?: 'Não configurado',
        ]);
    }

    /** @return array<string, mixed> */
    private function supabaseCheck(): array
    {
        $url = config('services.supabase.url');
        $anonKey = config('services.supabase.anon_key') ?: config('services.supabase.key');
        $serviceKey = config('services.supabase.service_key');
        $host = parse_url((string) $url, PHP_URL_HOST);
        $configured = filled($url) && (filled($anonKey) || filled($serviceKey));

        return $this->item('supabase', 'Supabase', $configured ? 'ok' : 'warning', $configured
            ? 'Projeto e chave configurados. Os valores permanecem ocultos.'
            : 'Supabase não está configurado neste ambiente.', [
            'Projeto' => $host ?: 'Não configurado',
            'Chave pública' => filled($anonKey) ? 'Configurada' : 'Ausente',
            'Chave de serviço' => filled($serviceKey) ? 'Configurada' : 'Ausente',
        ]);
    }

    /** @return array<string, mixed> */
    private function stripeCheck(): array
    {
        $key = config('services.stripe.key');
        $secret = config('services.stripe.secret');
        $configured = filled($key) && filled($secret);

        return $this->item('stripe', 'Pagamentos', $configured ? 'ok' : 'info', $configured
            ? 'Chaves de pagamento configuradas. Os valores permanecem ocultos.'
            : 'Nenhuma integração de pagamentos está configurada.', [
            'Chave pública' => filled($key) ? 'Configurada' : 'Ausente',
            'Chave privada' => filled($secret) ? 'Configurada' : 'Ausente',
        ]);
    }

    /** @param array<string, string> $meta */
    private function item(string $key, string $label, string $status, string $detail, array $meta): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'status_label' => match ($status) {
                'ok' => 'Operacional',
                'warning' => 'Revisar',
                'error' => 'Falha',
                default => 'Não utilizado',
            },
            'detail' => $detail,
            'meta' => $meta,
        ];
    }
}
