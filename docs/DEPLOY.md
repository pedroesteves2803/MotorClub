# Preparação para publicação

O MVP foi implementado e testado localmente. Não existe deploy na internet nem repositório GitHub remoto conectado. A publicação exige escolher a conta/destino e a infraestrutura; não foi contratado serviço pago.

## Repositório e build

Enviar código e lockfiles ao repositório escolhido. Nunca enviar `.env`, logs, banco ou fotos privadas. Os workflows existentes executam Pest com PHP 8.4/MySQL 8.4 e Pint.

```sh
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

No Laravel Cloud, configurar PHP 8.4 com GD, MySQL persistente, domínio HTTPS e secrets. Não executar `migrate:fresh`, seeders demonstrativos ou `key:generate` em cada deploy.

## Configuração necessária

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` HTTPS, `APP_KEY` estável, `APP_LOCALE=pt_BR`.
- Credenciais exclusivas de MySQL, sessões/cache em banco e cookies seguros.
- Cliente Google com retorno `https://DOMINIO/auth/google/callback`, política de usuários/consentimento pronta para o público escolhido. Validar o login real nesse domínio.
- Armazenamento **privado e persistente** para `storage/app/private`. A implementação atual usa o disco local, que não pode ser efêmero. Para infraestrutura com disco efêmero, ainda é necessário instalar/configurar o adapter S3 e adaptar as referências ao disco nas fotos, exclusões e testes. Não usar `storage:link` para fotos privadas.
- Backup do banco e dos arquivos, com teste de restauração, antes de receber dados reais no ambiente remoto.

Pedidos de orçamento ficam na caixa interna, sem envio de mensagens externas. Não há cobrança ou processamento de pagamentos. Para o piloto público, revisar política de privacidade, suporte e moderação, e retirar/autorizar imagens de terceiros e perfis demonstrativos antes de divulgar.

## Homologação

Executar a suíte, build e o [roteiro de teste](TESTAR.md) com duas contas. Verificar retorno Google, fotos, privacidade, HTTPS e permissões após deploy. `/up` indica que a aplicação responde; não substitui teste de banco e armazenamento. Não declarar o deploy pronto sem essa verificação.

Rollback: redeploy da versão anterior e migrations compatíveis. Evitar rollback destrutivo; recuperação de banco e arquivos requer backup verificado.

## Alternativa econômica: servidor único com Docker

Arquivos: `Dockerfile`, `compose.production.yaml` e `deploy/`. A imagem usa PHP/Apache; Caddy termina HTTPS e MySQL fica sem porta pública. Fotos privadas e banco usam volumes persistentes. Imagens de demonstração não entram na imagem final. Não use `artisan serve` em produção. Esta alternativa ainda precisa ser validada no servidor contratado; não é uma publicação concluída.

1. Contratar domínio e servidor Linux com Docker dentro do orçamento, configurar DNS para o servidor e liberar somente SSH restrito e portas 80/443. Manter sistema atualizado.
2. Criar `.env.production` a partir de `.env.example`, com permissões `600`. Configurar `DOMAIN`, `APP_URL=https://seu-dominio`, `APP_ENV=production`, `APP_DEBUG=false`, `TEST_MODE=false`, `DEMO_ENABLED=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `LOG_LEVEL=warning`, `DB_DATABASE=motorclub`, `DB_USERNAME=motorclub`, senhas aleatórias diferentes para `DB_PASSWORD` e `MYSQL_ROOT_PASSWORD`, `SUPPORT_EMAIL` real. Gerar e preservar `APP_KEY` (não trocar a cada deploy). Definir `STORAGE_PERSISTENT=true` somente após conferir os volumes.
3. Criar credencial Google para o domínio real com callback `https://seu-dominio/auth/google/callback`. Configurar consentimento e publicação do OAuth, domínio e páginas legais. Nunca publicar credenciais no Git.
4. Executar `docker compose --env-file .env.production -f compose.production.yaml build` e depois `up -d` com os mesmos argumentos. Executar `exec app php artisan migrate --force` e `exec app php artisan motorclub:production-check`. Não executar seeders. Usar base nova: não copiar a base local de demonstração.
5. Validar pelo domínio HTTPS: login/logout, dois usuários com isolamento de fotos e orçamentos, cadastro de oficina, projeto público, pedido/resposta de orçamento, exclusão de conta. Reiniciar os contêineres e confirmar que fotos e registros persistem.
6. Fazer backup diário consistente com `mysqldump --single-transaction` e cópia do volume de fotos, criptografados em destino externo ao servidor. Guardar também a chave da aplicação de forma segura. Reter sete diários e quatro semanais. Não incluir dumps em Git nem em diretório público. Restaurar banco e fotos em ambiente isolado e conferir login, registros e imagens; só então definir `BACKUPS_VERIFIED=true`. Objetivo inicial: perda máxima de 24 horas de dados; medir o tempo de recuperação no ensaio.
7. Antes de abrir cadastro público: publicar termos e aviso de privacidade com identidade do responsável, contato, finalidades, retenção e canal de direitos; revisar uso de imagens e moderação. Textos genéricos não comprovam conformidade legal. Definir rotina para denúncias e incidentes.

Atualizações: backup, build da nova imagem, migrações compatíveis, recriar aplicação e teste funcional. Guardar a imagem anterior para rollback; migrations destrutivas exigem plano de restauração. Nunca usar `down -v` no servidor. O MVP não usa jobs assíncronos de negócio; se adicionados, configurar worker e monitoramento. Monitorar `/up` externamente e verificar banco, espaço em disco, backups e logs. `/up` isoladamente não comprova saúde do banco.

O comando de verificação falha se houver contas fictícias na base. Ele não apaga dados e não certifica segurança, capacidade, disponibilidade ou cobrança. Não marcar etapas como concluídas sem executá-las no ambiente real.
