# MotorClub

MVP local de uma comunidade automotiva, com Laravel 13, PHP 8.4, Livewire 4, Tailwind 4, MySQL 8.4 e Pest. Interface em português do Brasil.

## O que funciona

- Login e cadastro com Google; edição e exclusão de conta.
- Garagem com veículos públicos ou privados, fotos e histórico privado de manutenção.
- Projetos com peças, serviços, etapas, fotos, custo opcional e busca por marca/modelo.
- Perfis públicos opcionais e comunidade pesquisável.
- Oficinas com cidade, especialidades, WhatsApp opcional e portfólio autorizado pelas duas partes.
- Pedidos privados de orçamento, resposta da oficina, valor proposto e cancelamento pelo solicitante.
- Eventos com cadastro, edição, exclusão e filtros por cidade/estado. Horário de Brasília.
- Tema claro/escuro com contraste verificado e dados fictícios identificados.

O MVP está disponível para testes locais. Não há pagamentos, notificações externas de orçamento ou publicação na internet. O roteiro está em [docs/TESTAR.md](docs/TESTAR.md), os resultados em [docs/STATUS.md](docs/STATUS.md) e a preparação de produção em [docs/DEPLOY.md](docs/DEPLOY.md).

## Instalação local

Requisitos: PHP 8.4 com GD, PDO MySQL/SQLite e extensões Laravel; Composer 2; Node 24; Docker Desktop.

```sh
cp .env.example .env
composer install
npm ci
php artisan key:generate
docker compose up -d --wait mysql
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8015
```

Configure o Google OAuth em `.env` conforme [docs/GOOGLE-AUTH.md](docs/GOOGLE-AUTH.md). Nunca envie `.env`, tokens ou dados privados para Git. Neste Mac, execute `php84` no lugar de `php`; Node fica disponível com `nvm use`. O MySQL fica em Docker, na porta 33067 limitada ao computador local; o PHP executa no host.

Para preencher os módulos com exemplos, apenas em ambiente local/testing:

```sh
php artisan db:seed --class=MvpDemoSeeder
```

O seeder é repetível e só atualiza contas demonstrativas identificadas por `is_demo`; preserva contas reais. Fotos já anexadas são mantidas. Não executar em produção. As fontes das imagens demonstrativas estão em [docs/DEMO-CREATORS.md](docs/DEMO-CREATORS.md).

## Verificação

```sh
composer test
composer lint
npm run build
composer validate --strict
```

Pest utiliza SQLite em memória por padrão. Para verificar MySQL use **exclusivamente o banco isolado de testes**:

```sh
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=33067 DB_DATABASE=motorclub_test DB_USERNAME=motorclub DB_PASSWORD=local-only-change-me php vendor/bin/pest
```

Nunca aponte os testes para o banco da aplicação: `RefreshDatabase` recria tabelas. CI de MySQL e formatação está preparado em `.github/workflows`; depende de conectar um repositório remoto.

Fotos são reencodadas em WebP sem EXIF/GPS, armazenadas em disco privado e acessadas por endpoints autorizados. Limite de 12 por veículo/projeto, 5 MB e 4096 × 4096 px. O histórico de veículos permanece privado mesmo com o carro público; os custos de projetos só aparecem publicamente quando o proprietário autoriza. Alterar um projeto reinicia a aprovação de sua oficina.

## Convenções

Toda nova funcionalidade inclui testes de comportamento, validação e isolamento entre contas. Alterações de persistência são verificadas em SQLite e MySQL. Interface, documentação e comunicação são em português do Brasil.
