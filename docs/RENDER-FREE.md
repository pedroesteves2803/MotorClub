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

Nesta primeira publicação, as fotos ficam no armazenamento privado do contêiner. Elas não são públicas, mas o sistema de arquivos do Render gratuito não é persistente: uma recriação do serviço pode apagá-las. Antes de usar fotos como dado definitivo, configure um bucket privado S3/R2, mantendo `PRIVATE_FILESYSTEM_DISK=s3` e os segredos correspondentes.

## 3. Validar a publicação

Depois do primeiro deploy, abra a URL `onrender.com` e valide cadastro, login, garagem, envio de foto, visualização de preview e download autorizado no Shots. O comando de início aplica `php artisan migrate --force` antes de iniciar o Apache.

O plano gratuito do Render pode suspender o serviço quando não há acessos e o Aiven gratuito tem limites de capacidade. Ambos são adequados para demonstração e piloto, não para tráfego contínuo.
