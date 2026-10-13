# Plano incremental e arquitetura

## Organização

Monólito Laravel, um deploy e um MySQL. Módulos lógicos: Identity, Garage, Projects, Workshops, Quotes e Events. Conforme cada módulo nascer, usar app/Livewire/<Modulo>, app/Actions/<Modulo> e app/Policies; manter Models, factories e migrations convencionais. Não criar pacotes internos, repositórios genéricos ou event bus.

Livewire valida formulários; Form Requests em endpoints HTTP de upload quando necessários. Policies centralizam autorização; Actions/Services somente para transações/regras reutilizadas. Jobs para processamento de imagens e notificações após commit. Fila inicialmente em banco; scheduler e workers no Cloud. Rotas web com sessão; API pública não necessária.

## Incrementos com saída verificável

1. **Fundação (esta entrega):** Laravel 13/PHP 8.4, Livewire/Tailwind, autenticação base, MySQL local, Pest, CI e documentação. Aceite: instalação, build, rotas e testes na versão alvo.
2. **Identidade e privacidade:** traduções completas, perfil público opt-in, revisão visual responsiva, textos de consentimento e suporte. Testar e-mail privado e alteração de visibilidade.
3. **Garagem mínima:** migration/model/factory/Policy e formulário/lista de veículos. Testar CRUD e isolamento entre dois usuários; executar migrations no MySQL.
4. **Mídia privada:** upload, limites, processamento, EXIF, armazenamento e remoção. Testar arquivo malformado, acesso por terceiro, falha e retentativa.
5. **Projeto útil:** rascunho, entradas, peças e custos opcionais. Testar propriedade, validação e transação; dono mantém informações após erro.
6. **Descoberta:** publicação, busca e compartilhamento. Testar herança de privacidade, custo oculto, paginação e despublicação.
7. **Oficina e portfólio:** perfil, revisão operacional e associações aceitas/revogadas. Testar que usuário não se torna administrador e oficina não pode se atribuir trabalho unilateralmente.
8. **Orçamento ponta a ponta:** solicitação contextual, caixa de entrada e resposta. Testar acesso cruzado, transições, contato autorizado e idempotência de notificações.
9. **Eventos editorial:** CRUD restrito e busca regional, cancelamento e fuso. Pode ocorrer após início do piloto restrito, antes do MVP ampliado.
10. **Homologação:** teste de jornada com duas contas e oficina, e-mail real, restauração de backup, fila, monitoramento, acessibilidade e revisão de privacidade.
11. **Piloto:** conectar GitHub/Cloud, configurar ambientes e domínio, publicar após recursos aprovados; convidar parceiros reais. Medir metas do PRD semanalmente; não implementar cobrança.

Cada incremento termina com código + migration/factory quando necessário + testes Pest de comportamento + documentação de status. Pequenos PRs para main com CI obrigatório. Sem estimativas de calendário antes de medir o primeiro incremento funcional.

## Testes

Pest cobre fluxos de autenticação e comportamento de cada módulo. MySQL no teste de integração/CI; SQLite em memória apenas como opção rápida. Testes negativos de autorização e privacidade são obrigatórios a cada módulo. Browser QA no lançamento cobre formulários, dispositivos pequenos e navegação por teclado. Build não substitui verificação visual.

## Referências técnicas

- [Starter kits oficiais](https://laravel.com/docs/13.x/starter-kits)
- [Migração para Laravel 13](https://laravel.com/docs/13.x/upgrade)
- [Deployments no Laravel Cloud](https://cloud.laravel.com/docs/deployments)

Consultadas em 07/10/2026. A base distribuída do starter kit veio com Laravel 12 e foi ajustada explicitamente para 13, com lockfile resolvido para PHP 8.4.
