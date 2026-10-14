# Auditoria de segurança e preparação para produção

**Data:** 14/10/2026  
**Escopo:** aplicação Laravel/Livewire/Filament, configuração de deploy e controles de acesso do Autoverse.  
**Referência:** controles aplicáveis do OWASP ASVS 5.0, com prioridade para o nível L1.

Este documento registra evidências do código e dos testes executados. Ele não é uma declaração de segurança absoluta. Controles de infraestrutura que dependem do provedor precisam ser confirmados no ambiente publicado.

## Diagnóstico

- PHP `~8.4`, Laravel `^13`, Filament `^4`, Livewire Flux `^2`, Volt `^1.6`, Socialite `^5.31` e Stripe `^22` estão declarados no `composer.json`.
- O inventário local contém 167 rotas. O login Google usa o guard `web`, a sessão da aplicação e regeneração de sessão após autenticação. O painel Filament usa o mesmo model `User` e o mesmo guard.
- As integrações encontradas são Google OAuth, Stripe Webhook/Checkout, MySQL, armazenamento compatível com S3/R2 e envio de e-mail configurável.
- A suíte existente usa Pest e cobre autenticação, Filament, autorização por proprietário, uploads, QR Code, cobrança, páginas legais e erros.

## Classificação dos controles

| Controle | Estado | Evidência |
| --- | --- | --- |
| Chaves, senhas e tokens fora do frontend | Validado sem alteração | Não há `.env` real rastreado; buscas encontraram apenas placeholders de teste. Valores de produção não são exibidos neste relatório. |
| `.env` e arquivos sensíveis | Validado sem alteração | Apenas `.env.example` e `.env.staging.example` são rastreados; o DocumentRoot do Apache é `public/`. |
| Autenticação OAuth | Validado sem alteração | Callback exige estado válido, e-mail Google verificado e identidade Google compatível; a sessão é regenerada. |
| Logout e sessão | Validado sem alteração | Logout encerra o guard, invalida a sessão e regenera o token CSRF. |
| Autorização do Filament | Corrigido anteriormente e coberto | `User::canAccessPanel()` exige papel administrativo; recursos e páginas têm verificações próprias. Usuário comum recebe 403. |
| IDOR/BOLA e isolamento entre usuários | Validado sem alteração | Policies, consultas por proprietário e regras `exists` vinculadas ao usuário protegem veículos, projetos, mídias, Shots e pedidos. |
| CSRF e webhooks | Validado sem alteração | CSRF permanece ativo; somente o webhook Stripe é exceção e exige assinatura válida e janela de tolerância. |
| Validação e mass assignment | Validado sem alteração | Form Requests/regras de entrada, `$fillable` e consultas parametrizadas são usados nos fluxos auditados. |
| XSS e HTML legal | Validado sem alteração | Blade escapa valores; conteúdo jurídico passa pelo `LegalContentSanitizer`, que remove scripts, eventos e esquemas de URL inseguros. |
| Rate limiting | Validado sem alteração | OAuth, feedback, cookies, QR Code, uploads e endpoints de alto custo têm limites ou controles de repetição. |
| Uploads | Validado sem alteração | Imagens são validadas, redimensionadas/recodificadas quando aplicável, recebem nomes UUID, ficam privadas e têm metadados GPS removidos no processamento de veículos. |
| Arquivos privados | Validado sem alteração | Downloads passam por autorização e não usam URLs públicas permanentes para originais protegidos. |
| Headers de segurança | Corrigido | `SecurityHeaders` mantém `nosniff`, clickjacking, referrer, permissions e HSTS em HTTPS; foi acrescentado CSP em modo `Report-Only`. |
| Erros de produção | Validado sem alteração | 403, 404, 419, 422, 429, 500 e 503 usam páginas branded sem stack trace. |
| Banco e TLS | Pendente de confirmação operacional | O código usa consultas parametrizadas e o deploy suporta CA TLS do MySQL; permissões mínimas, backup e restauração precisam ser confirmados no provedor. |
| CORS e APIs | Validado sem alteração | Não há configuração CORS permissiva nem API pública sem autenticação identificada; as integrações web usam rotas específicas. |
| Privacidade e consentimento | Corrigido | O cookie de consentimento agora é `HttpOnly`, `SameSite=Lax` e `Secure` em HTTPS/produção. Dados de localização e perfis privados permanecem protegidos. |
| Dependências PHP | Validado | `composer audit --locked` retornou `No security vulnerability advisories found.` |
| Dependências JavaScript | Não verificável no ambiente atual | O host não possui `node/npm` e o daemon Docker não estava disponível para executar `npm audit` com o `package-lock.json`. |
| Storage e continuidade | Pendente | O Render Free usa disco local efêmero. Fotos existentes podem retornar 404 após recriação; é necessário S3/R2 privado, teste de reinício e reupload dos arquivos ausentes. |
| Backups e restauração | Pendente | `STORAGE_PERSISTENT` e `BACKUPS_VERIFIED` permanecem controles operacionais que precisam ser configurados e testados no provedor. |

## Correções implementadas nesta auditoria

1. Criado `config/security.php` com política CSP explícita e opção `CSP_REPORT_ONLY` (padrão `true`).
2. Adicionado `Content-Security-Policy-Report-Only` ao middleware `SecurityHeaders`, sem quebrar Livewire, Flux ou Filament antes da análise dos relatórios.
3. Endurecido o cookie `autoverse_cookie_consent_id` no `LegalController` com `Secure` em produção/HTTPS, `HttpOnly` e `SameSite=Lax`.
4. Acrescentados testes para o header CSP e os atributos do cookie.
5. Atualizado o teste da página 404 para o texto da página de erro branded vigente.
6. Documentadas `CSP_REPORT_ONLY` e `CSP_REPORT_URI` no `.env.example`.

Nenhuma dependência foi atualizada nesta etapa e nenhum dado de produção foi apagado.

## Testes e evidências

- `./vendor/bin/pint`: passou.
- `php artisan view:cache`: passou.
- `php artisan test`: **191 passed, 1.216 assertions**.
- O conjunto focado de segurança, produção, rotas administrativas e módulos também passou antes da execução completa.
- `composer audit --no-interaction --locked`: sem advisories conhecidos.
- `git diff --check`: sem erros de whitespace.
- Em produção, a inspeção anterior confirmou HTTPS, HSTS de um ano, `nosniff`, `SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy` e cookies de sessão Secure/HttpOnly/SameSite. O header CSP desta auditoria precisa ser confirmado após o deploy.

## Configuração necessária no provedor

1. Configurar `PRIVATE_FILESYSTEM_DISK=s3` e credenciais S3/R2 como segredos do Render; não colocar esses valores no repositório.
2. Fazer upload e download autenticado de uma foto, reiniciar o serviço e repetir o teste para confirmar persistência. Reenviar fotos que já retornam 404, pois caminhos no banco não recuperam arquivos apagados do disco efêmero.
3. Definir `SUPPORT_EMAIL`, `STORAGE_PERSISTENT=true` somente após o teste de persistência e `BACKUPS_VERIFIED=true` somente após um teste real de restauração.
4. Manter `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true` e `DEMO_ENABLED=false` em produção.
5. Após coletar relatórios CSP sem violações legítimas, revisar a lista de origens e mudar `CSP_REPORT_ONLY=false` em uma publicação controlada.
6. Executar `npm audit --omit=dev --audit-level=moderate` em CI ou em uma máquina com Node disponível.

## Riscos residuais

- A persistência atual de imagens no Render Free é o risco operacional mais concreto e explica respostas 404 para arquivos que continuam referenciados no banco.
- CSP está em observação, não em bloqueio, até que Livewire/Filament e origens externas sejam validados.
- Backups, restauração, permissões do MySQL, rotação de segredos e OAuth externo exigem validação no ambiente real.
- A auditoria não incluiu teste de carga nem exploração destrutiva em produção.

## Checklist antes da publicação

- [x] `APP_DEBUG=false` e páginas de erro sem stack trace.
- [x] Autorização de `/admin` e recursos administrativos no backend.
- [x] Sessões, CSRF, cookies e logout cobertos.
- [x] Uploads e acesso a arquivos privados cobertos.
- [x] Headers de segurança e CSP Report-Only implementados.
- [x] Suite automatizada verde.
- [x] Auditoria Composer verde.
- [ ] `npm audit` executado em ambiente com Node.
- [ ] Storage privado persistente e restauração testados no Render.
- [ ] Backups e contato de suporte configurados.
- [ ] CSP aplicada após revisão dos relatórios.
