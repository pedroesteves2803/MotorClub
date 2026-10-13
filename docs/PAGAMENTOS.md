# Assinaturas das oficinas — Stripe em modo de testes

Implementado em 12/10/2026: plano mensal de referência de R$ 99,00, checkout hospedado, status da assinatura e portal para atualizar método de pagamento/cancelar. Serviços automotivos são pagos diretamente à oficina. O uso do site continua liberado na homologação, inclusive quando o pagamento simulado falha. Esta entrega não habilita cobrança comercial nem limita recursos por plano.

## Configurar a conta de testes
1. Criar uma conta Stripe e usar o ambiente de testes/sandbox.
2. Criar produto “Oficina MotorClub” com preço recorrente mensal de R$ 99,00, BRL. Não usar preço por uso, anual ou com valor diferente. O servidor confere valor, moeda, intervalo e modo de teste antes do checkout.
3. Configurar `STRIPE_SECRET=sk_test_...`, `STRIPE_PRICE_ID=price_...` e `STRIPE_WEBHOOK_SECRET=whsec_...` no arquivo de ambiente privado. Não enviar segredos no chat, não versionar e não usar chaves sk_live.
4. Habilitar o portal de clientes Stripe em teste, permitindo cancelar assinatura e atualizar cartão. Não habilitar troca para outros produtos/preços nesta versão.
5. Cadastrar o endpoint HTTPS `/stripe/webhook` para eventos `customer.subscription.created`, `customer.subscription.updated` e `customer.subscription.deleted`. O endpoint dispensa a senha geral da homologação e CSRF porque exige a assinatura criptográfica Stripe, com tolerância de cinco minutos. Não excluir outras rotas dessas proteções.
6. Para desenvolver localmente, usar a CLI oficial: `stripe listen --events customer.subscription.created,customer.subscription.updated,customer.subscription.deleted --forward-to http://127.0.0.1:8015/stripe/webhook`. Usar o segredo whsec informado pela CLI nesse ambiente; ele difere do endpoint do painel. Após mudar configuração, executar `php artisan config:clear` (ou reconstruir o cache no servidor).
7. Rodar `php artisan migrate --force`, entrar com Google, cadastrar uma oficina e abrir `/plano-oficina`.

## Testar de ponta a ponta
- Checkout aprovado: usar cartão de teste 4242 4242 4242 4242, validade futura e CVC de teste. Nunca usar dados de cartão real.
- Recusa: usar cartão de teste 4000 0000 0000 0002.
- Verificar atualização para “Ativa em teste” após entrega do webhook. O parâmetro de retorno do navegador não concede ativação.
- Reabrir checkout pendente: reutiliza a mesma sessão; assinatura ativa leva ao gerenciamento, sem nova assinatura.
- No portal, testar cancelamento no fim do período e conferir a data na aplicação. Usar relógios de teste Stripe para exercitar renovação e inadimplência, seguindo documentação oficial.
- Reenviar eventos: o servidor consulta a assinatura atual para evitar regressão por eventos repetidos/fora de ordem. Uma tentativa de contratação anterior não substitui a atual.
- Desligar temporariamente a entrega no ambiente isolado e reenviar: erros de sincronização retornam 503 para nova tentativa.

## Limites e operação
A integração está testada com respostas de API e assinaturas de webhook simuladas. O ensaio real no sandbox exige credenciais que ainda não foram fornecidas. Apenas registrar a assinatura no banco não comprova um pagamento. Não mudar STRIPE_PRICE_ID durante checkouts pendentes. Excluir conta no MotorClub não cancela automaticamente uma assinatura de teste na Stripe; cancelar pelo portal antes de excluir a conta. Isso precisa de tratamento adicional antes de qualquer lançamento comercial.

Credenciais e eventos reais estão bloqueados em código. Para cobrança real serão necessários revisão comercial/legal, fluxo de exclusão com cancelamento, conciliação operacional, definição do acesso por plano, configuração de recuperação de pagamentos e testes no provedor. Não basta trocar a chave por uma chave live.

Referências oficiais:
- https://docs.stripe.com/api/checkout/sessions/create
- https://docs.stripe.com/webhooks/signature
- https://docs.stripe.com/customer-management
- https://docs.stripe.com/testing
- https://stripe.com/br/pricing

## Simulador completo sem Stripe
Ativado localmente com `BILLING_DRIVER=mock` e `TEST_MODE=true`. Acesse `/plano-oficina`, entre com Google e cadastre uma oficina. Clique em iniciar checkout, escolha aprovação ou recusa e exercite renovação, recusa de renovação, recuperação, cancelamento imediato/agendado, desistência do cancelamento, chegada do fim do período e estorno. Nenhum dado bancário é solicitado e nenhuma API de pagamento é chamada.

O histórico fictício é persistido em tabelas separadas dos registros Stripe. O valor é controlado pelo servidor, o reenvio do mesmo formulário não duplica eventos e cada usuário vê apenas sua própria simulação. O estorno simula a devolução do último pagamento e encerra o plano; cancelamento comum não devolve pagamentos. A renovação e o fim do período são acionados manualmente pelos botões para não precisar esperar um mês. Para testar novamente após cancelamento/estorno, inicie um novo checkout; o histórico anterior é preservado.

Disponível somente nos ambientes local, testing e staging com modo de testes ligado. Em produção as ações são bloqueadas. Para voltar à integração Stripe, configure `BILLING_DRIVER=stripe` e as credenciais de teste; as simulações locais não são convertidas em assinaturas do provedor.
