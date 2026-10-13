# Estado da entrega — 08/10/2026

## Reposicionamento de comunidade e MotorClub AI — 12/10/2026

- Navegação reposicionada para Garagem, Explorar e Projetos; oficinas e eventos permanecem acessíveis como módulos complementares. Plano comercial e pagamento simulado foram retirados da navegação pública, sem remover código ou rotas.
- Relatórios técnicos demonstrativos versionados por projeto: geração, revisão manual, aprovação e publicação opcional. Cada relatório começa privado e só aparece publicamente se o projeto também for público.
- `MOTORCLUB_AI_PROVIDER=fake` é o padrão. O gerador usa templates locais, registra lacunas e separa dados cadastrados de orientação geral; não chama API, não pesquisa fontes e não inventa custos ou especificações.
- Contrato intercambiável em `AIReportGeneratorInterface`, com instruções reutilizáveis em `docs/skills/motorclub-automotive-project-report.md` para futuro provedor real ou local.
- Suíte SQLite aprovada: 118 testes / 816 assertions. Pint aprovado. Build frontend não executado nesta sessão porque o executável `npm` não estava disponível no shell.

## MVP local implementado

- Google OAuth para entrar e cadastrar; conta e exclusão com confirmação.
- Garagem: CRUD de veículos, visibilidade, fotos e histórico privado de manutenção/modificações.
- Projetos: CRUD, fotos, peças/serviços, etapa, custo opcional, publicação condicionada à visibilidade do veículo e busca por título/marca/modelo.
- Oficinas: cadastro/edit/exclusão, especialidades, localização, contato opcional por WhatsApp e portfólio com autorização revogável. Editar um projeto reinicia sua autorização.
- Orçamentos: solicitação, caixa privada das duas partes, resposta/valor da oficina, recusa e cancelamento pelo solicitante. Nenhuma mensagem externa é enviada.
- Eventos: CRUD, agenda futura, busca por cidade/estado e conversão entre horário de Brasília e UTC no banco.
- Perfis públicos opcionais, descoberta de membros reais e demonstrativos, sem expor e-mail ou histórico.
- Layout editorial compartilhado, tema claro/escuro selecionável no rodapé, contraste mínimo de 4,5:1 nos pares de cores de texto testados, validação e erros em português.
- Demonstração: oito perfis/carros, oito projetos, três oficinas e três eventos, além das garagens não oficiais de criadores já existentes. Dados fictícios identificados; sem novas imagens geradas nesta entrega.

## Verificação

Pest com PHP 8.4: suíte de 86 testes / 555 verificações em SQLite e MySQL isolado. Inclui autenticação simulada do Google, isolamento entre contas, privacidade de veículos/projetos/imagens/perfis, custo privado, autorização de oficina, orçamento, evento, exclusão de arquivos, demo repetível e contraste.

Build Vite, compilação de views, Pint e `composer validate --strict` verificados. Revisão no navegador: catálogos de projetos, oficinas e eventos; busca por modelo; temas claro/escuro; agenda em largura de celular de 390 px sem rolagem horizontal. Captura em `mvp-projetos.jpg`.

OAuth real estava configurado na etapa anterior. Esta rodada verifica as regras de autenticação automaticamente com respostas simuladas; não representa nova autenticação pessoal ponta a ponta com Google. O roteiro manual completo para duas contas está em [TESTAR.md](TESTAR.md).

## Execução e limites

Migrations aplicadas ao MySQL local; Docker reiniciado e servidores locais ativos em 8015/8016. Dados da conta real preservados pelo seeder. Fotos continuam em armazenamento local privado.

- Acesso local com Google: http://127.0.0.1:8015
- Prévia pública na rede atual: http://10.3.6.109:8016

O segundo endereço não autentica outros dispositivos: o retorno OAuth está configurado para o computador local. A rede deve permitir comunicação entre dispositivos e o computador precisa permanecer ligado.

Não publicado na internet; sem domínio/Cloud ou GitHub remoto. Publicação externa e armazenamento remoto durável continuam dependentes do ambiente escolhido, conforme [DEPLOY.md](DEPLOY.md). Não há pagamentos ou notificações externas. Oficinas fictícias não respondem; use duas contas reais para testar a troca de orçamento.

## Atualização de dados públicos — 09/10/2026

Referências verificáveis incluídas nas onze garagens demonstrativas, com distinção entre dados do modelo e do exemplar. Duas oficinas reais e dois eventos anunciados aparecem nos catálogos, com fontes e data de consulta, sem criar contas de terceiros. Testes adicionais verificam filtros, expiração de eventos, isolamento da área pessoal e ausência de históricos inventados nas referências dos criadores.

Validação final da atualização: **89 testes passaram, 647 verificações**, tanto em SQLite quanto em MySQL isolado. Build Vite e Pint aprovados. Catálogo de oficinas conferido no navegador; captura em `dados-publicos-reais.jpg`. O Docker e o servidor local foram reativados.

## Preparação de produção — 09/10/2026
- Cabeçalhos de segurança e opção para retirar demonstrações da comunidade.
- Comando `motorclub:production-check` verifica configuração e recusa base com usuários fictícios; não altera dados.
- Dockerfile com build de assets, Apache/PHP 8.4, Compose com HTTPS via Caddy e volumes privados persistentes. Não inclui credenciais nem fotos de demonstração na imagem final.
- 93 testes / 661 assertions aprovados em SQLite e MySQL isolado; build frontend e auditorias de dependências de produção aprovados (sem avisos conhecidos).
- Compose validado sintaticamente. Build Docker tentado, mas falhou ao consultar imagens no Docker Hub por `context deadline exceeded`; a imagem final e o fluxo HTTPS ainda não foram testados. CI inclui build da imagem.
- Plano comercial e orçamento de até R$ 200/mês em `LANCAMENTO-ENXUTO.md`.
- Não publicado: faltam domínio, servidor, configuração Google definitiva, identidade/contato para documentos legais, backup externo com restauração comprovada. Não existe cobrança automática.

## Homologação restrita — 09/10/2026
- Aviso de testes no site e no login, roteiro `/como-testar`, não indexação de respostas e autenticação adicional obrigatória em APP_ENV=staging. Sem senha, acesso fechado.
- Modelo `.env.staging.example`, Compose com seleção de arquivo de ambiente e guia `HOMOLOGACAO.md`.
- `motorclub:production-check --staging` valida configuração específica, inclusive senha de pelo menos 16 caracteres. Produção exige TEST_MODE=false.
- Script de backup criptografado e teste isolado de recuperação e tratamento de falhas. Agendamento, destino externo e ensaio real de recuperação continuam dependentes da hospedagem.
- 97 testes / 679 assertions aprovados em SQLite. Suíte de 96 testes aprovada no MySQL antes do último cenário; os oito cenários finais de homologação/prontidão passaram depois no MySQL. Build frontend e Pint aprovados.
- Revisão de seis páginas públicas a 390 px: sem overflow horizontal ou imagens quebradas detectadas nessa checagem. Captura do roteiro: homologacao.png.
- Impedimento anterior resolvido: imagem Docker construída com sucesso. Contêiner temporário conectado somente a motorclub_test respondeu 401 sem credencial e 200 com credencial em /, /login, /como-testar, /comunidade, /oficinas, /projetos, /eventos e /up. Composer check-platform-reqs aprovado. Contêiner de ensaio removido.
- Publicação externa ainda não realizada: depende de domínio/servidor e OAuth definitivo. Não há cobrança real; não declarar operação comercial pronta ou teste de carga/backup real concluído.

## Assinaturas Stripe em teste — 12/10/2026
- Plano para oficinas em `/plano-oficina`, checkout hospedado, portal de gerenciamento/cancelamento e status sincronizado por webhook autenticado com SDK oficial Stripe.
- Apenas sk_test e eventos livemode=false; nenhuma cobrança real habilitada. Credenciais ainda não configuradas. Checkout valida plano mensal em BRL de R$ 99,00 e reutiliza sessão pendente.
- Usuário só acessa seu próprio cliente de cobrança. Retorno do navegador não ativa assinatura. Eventos repetidos/fora de ordem consultam estado atual da Stripe; falha retorna 503 para reentrega.
- Biblioteca stripe/stripe-php v22 instalada; migração aditiva aplicada no banco local. Build e Pint aprovados. 109 testes / 734 assertions passaram em SQLite e MySQL isolado, com API Stripe simulada. A página foi conferida no navegador.
- Falta configurar conta de teste, preço, portal e webhook e executar teste real de ponta a ponta no sandbox. Instruções: PAGAMENTOS.md. Uso dos recursos continua livre na homologação; não há pagamentos por serviços automotivos.

## Pagamento totalmente simulado — 12/10/2026
- BILLING_DRIVER=mock ativado no ambiente local. Sem credenciais ou chamadas Stripe e sem dados bancários.
- Checkout, aprovação/recusa, recuperação, renovação aprovada/recusada, cancelamento imediato/agendado, reversão de cancelamento, fim de período e estorno com histórico fictício persistido.
- Tabelas mock isoladas; alterações limitadas à conta autenticada, valor fixado no servidor, reenvio idempotente, transições inválidas rejeitadas. Ações indisponíveis em produção.
- Migração aditiva aplicada. 114 testes / 778 assertions passaram em SQLite e MySQL isolado. Build frontend aprovado; página verificada no navegador em modo visitante. Fluxos autenticados verificados nos testes automatizados.

## Visual moderno com Tailwind — 12/10/2026
- Identidade compartilhada atualizada para azul, fundos com gradiente discreto, cartões arredondados, novos botões, campos e tipografia. Aplicada aos layouts públicos, área autenticada e login.
- Cabeçalho fixo translúcido e menu responsivo com estado aria-expanded, acionamento por botão e fechamento por Escape.
- Animação de entrada, transições de cartões, botões e imagens; preferência de redução de movimento desativa animações e transformações de hover.
- Build aprovado; suíte de 114 testes passou, seguida dos testes de identidade/contraste incluindo novo cenário de navegação (3 testes / 70 assertions). Sete páginas conferidas em largura 390 px sem overflow horizontal. Menu expandido validado no navegador. Desktop revisado a 1440 px. Captura: visual-moderno.png.
