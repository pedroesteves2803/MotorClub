# Publicação gratuita (Render + Aiven + Cloudflare R2)

Esta configuração publica a aplicação no Render, mantém o MySQL no Aiven e guarda as fotos privadas no Cloudflare R2. Nenhuma credencial é versionada: os campos marcados como segredo em `render.yaml` são preenchidos no painel do Render.

## 1. Criar os serviços

1. No Aiven, crie um serviço MySQL gratuito e copie `host`, `port`, `database`, `user` e `password` da tela **Connection information**. Habilite o acesso público do Render conforme a lista de IPs permitidos do Aiven.
2. No Cloudflare R2, crie um bucket privado. Em **Manage R2 API Tokens**, crie um token com leitura e escrita apenas para esse bucket. Guarde `Access Key ID`, `Secret Access Key`, o nome do bucket e o endpoint S3 da conta.
3. No Render, crie um **Blueprint** a partir deste repositório. O arquivo `render.yaml` cria o serviço web gratuito e preenche as configurações não secretas.

## 2. Informar variáveis no Render

Na criação do Blueprint, informe os valores secretos pedidos:

- `APP_KEY`: gere uma vez com `php artisan key:generate --show` e preserve o resultado em todos os deploys;
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: dados do Aiven;
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT`: dados do R2. O endpoint é `https://<account-id>.r2.cloudflarestorage.com`;
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`: só se o login Google estiver habilitado. Use a URL final do Render seguida de `/auth/google/callback`;
- `MAIL_FROM_ADDRESS` e `SUPPORT_EMAIL`: endereços reais de contato. O envio de e-mails fica em log até um provedor SMTP ser configurado.

O bucket deve permanecer privado. Fotos e originais são entregues pelas rotas autenticadas do Laravel, sem URL pública do R2.

## 3. Validar a publicação

Depois do primeiro deploy, abra a URL `onrender.com` e valide cadastro, login, garagem, envio de foto, visualização de preview e download autorizado no Shots. O comando de início aplica `php artisan migrate --force` antes de iniciar o Apache.

O plano gratuito do Render pode suspender o serviço quando não há acessos e o Aiven gratuito tem limites de capacidade. Ambos são adequados para demonstração e piloto, não para tráfego contínuo.
