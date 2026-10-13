# Publicar para testes controlados

Este ambiente é separado da produção e da base local. Não habilita cobrança. A senha de acesso da homologação é adicional ao login Google e deve ser compartilhada apenas com os avaliadores. Sem a senha configurada o ambiente retorna 503; credenciais incorretas recebem 401. Use sempre HTTPS. O cabeçalho de não indexação não substitui essa proteção.

## Preparação do servidor
1. Configure um subdomínio com DNS apontando para um servidor com Docker. As portas 80 e 443 precisam estar disponíveis; não execute produção e homologação no mesmo servidor com este Compose sem adaptar o proxy.
2. Copie `.env.staging.example` para `.env.staging` e restrinja permissões com `chmod 600 .env.staging`.
3. Substitua domínio, URLs e credenciais. Gere três senhas distintas e aleatórias: acesso de avaliadores, banco e usuário root do banco. Não use senhas pessoais. Gere uma chave com `php artisan key:generate --show` e preencha APP_KEY; preserve-a para todas as atualizações.
4. Configure um cliente OAuth Google para o subdomínio e cadastre os avaliadores na tela de consentimento quando estiver em modo de teste. Não cole os segredos em conversas ou no repositório.
5. Execute:

```sh
docker compose --env-file .env.staging -p motorclub-staging -f compose.production.yaml build
docker compose --env-file .env.staging -p motorclub-staging -f compose.production.yaml up -d
docker compose --env-file .env.staging -p motorclub-staging -f compose.production.yaml exec app php artisan migrate --force
```

Não rode seeders nem importe a base pessoal. As contas de teste criam os próprios dados. Volumes levam o prefixo motorclub-staging, separados dos de produção. Não execute `down -v` se quiser preservar registros e fotos.

Execute também `docker compose --env-file .env.staging -p motorclub-staging -f compose.production.yaml exec app php artisan motorclub:production-check --staging`. Ele exige senha de acesso com pelo menos 16 caracteres e aponta configurações pendentes, sem imprimir segredos.

## Aceite funcional
No site, abra `/como-testar`. Execute o roteiro com duas contas Google controladas por você e uma janela anônima. A faixa de testes deve aparecer em todas as páginas com cabeçalho. Verifique acesso restrito antes de compartilhar o endereço, inclusive `/up`. Confirme fotos e registros após reiniciar os contêineres.

## Cópia e recuperação
Siga o procedimento de backup do DEPLOY.md também na homologação. Antes de usar dados pessoais reais, restaure uma cópia em ambiente separado, confira contagens e abra fotos privadas com a conta correta. A presença de um volume não substitui backup externo.

## Limites atuais
O build Docker foi concluído e um contêiner temporário validou oito rotas, acesso restrito e requisitos PHP, usando a base isolada de testes. Domínio e hospedagem ainda não foram contratados. O login Google de homologação e o HTTPS precisam ser validados no domínio definitivo. A suíte automatizada valida fluxos e permissões, mas não certifica disponibilidade ou carga em um servidor ainda inexistente. Não há pagamentos reais ou assinatura automática. Não distribuir o ambiente como produto comercial concluído.

## Executar backup criptografado
O script `scripts/backup.sh` usa o Compose da homologação por padrão. Defina `BACKUP_PASSPHRASE` em um gerenciador de segredos ou leia-a no terminal sem eco; não escreva a senha literal no histórico. Execute `bash scripts/backup.sh`. Ele exporta MySQL e fotos, criptografa o pacote e remove temporários. A chave APP_KEY e a senha de backup devem ser guardadas separadamente. Copie o arquivo para um destino externo com controle de acesso; a cópia externa e o agendamento ainda dependem da infraestrutura escolhida.

Para produção, use `COMPOSE_PROJECT_NAME=motorclub-production APP_ENV_FILE=.env.production`. Durante o primeiro ensaio, não permita alterações de fotos enquanto a cópia é feita: banco e arquivos são capturados em etapas distintas.

Para recuperar, em diretório isolado, execute `openssl enc -d -aes-256-cbc -pbkdf2 -iter 200000 -in ARQUIVO.enc -out recuperacao.tar.gz -pass env:BACKUP_PASSPHRASE`. Extraia o pacote e importe `database.sql` em uma base vazia isolada; extraia `photos.tar.gz` no volume privado dessa instalação. Não faça a primeira restauração sobre o ambiente original. Valide dados e fotos antes de substituir qualquer ambiente. O teste automatizado `python3 scripts/test-backup.py` confere empacotamento e recuperação com dados artificiais; não substitui esse ensaio com MySQL e volumes reais.
