# Publicação gratuita (Render + Aiven + Cloudflare R2)

Esta configuração publica a aplicação no Render e mantém o MySQL no Aiven. Nenhuma credencial é versionada: os campos marcados como segredo em `render.yaml` são preenchidos no painel do Render.

## 1. Criar os serviços

1. No Aiven, crie um serviço MySQL gratuito e copie `host`, `port`, `database`, `user` e `password` da tela **Connection information**. Baixe também o certificado da CA.
2. No Render, crie um **Blueprint** a partir deste repositório. O arquivo `render.yaml` cria o serviço web gratuito e preenche as configurações não secretas.

## 2. Informar variáveis no Render

Na criação do Blueprint, informe os valores secretos pedidos:

- `APP_KEY`: gere uma vez com `php artisan key:generate --show` e preserve o resultado em todos os deploys;
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: dados do Aiven;
- `DB_SSL_CA_B64`: o conteúdo do certificado da CA do Aiven codificado em Base64. O contêiner o grava somente em `/tmp` e configura o PDO para validar o TLS;
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`: só se o login Google estiver habilitado. Use a URL final do Render seguida de `/auth/google/callback`;
- `MAIL_FROM_ADDRESS` e `SUPPORT_EMAIL`: endereços reais de contato. O envio de e-mails fica em log até um provedor SMTP ser configurado.

O armazenamento local do Render gratuito é efêmero: uma recriação do serviço pode apagar as fotos mesmo que o banco continue com os caminhos salvos. Para fotos reais, crie um bucket privado S3/R2 e configure no Render `PRIVATE_FILESYSTEM_DISK=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ENDPOINT` e, opcionalmente, `AWS_URL`. No Cloudflare R2, o endpoint tem o formato `https://<account-id>.r2.cloudflarestorage.com`; mantenha `AWS_USE_PATH_STYLE_ENDPOINT=false`. Depois faça um upload e um download autorizado e reinicie o serviço para confirmar a persistência antes de divulgar o endereço.

## 3. Validar a publicação

Depois do primeiro deploy, abra a URL `onrender.com` e valide cadastro, login, garagem, envio de foto, visualização de preview e download autorizado no Shots. O comando de início aplica `php artisan migrate --force` antes de iniciar o Apache.

O plano gratuito do Render pode suspender o serviço quando não há acessos e o Aiven gratuito tem limites de capacidade. Ambos são adequados para demonstração e piloto, não para tráfego contínuo.
