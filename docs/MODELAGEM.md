# Modelagem inicial — proposta

Somente tabelas de infraestrutura e usuários do starter kit estão migradas nesta etapa. Criar as demais migrations por incremento, junto das regras e testes. IDs internos bigint; slug público único para perfis, veículos, projetos e oficinas. Datas UTC, apresentação America/Sao_Paulo. Valores decimal(12,2) BRL, nunca float.

| Tabela | Campos principais e relações |
|---|---|
| users | id, name, email unique, email_verified_at, password, timestamps |
| profiles | user_id unique FK, slug unique, display_name, bio nullable, visibility default private |
| vehicles | id, user_id FK, slug unique, brand, model, generation nullable, trim, model_year, engine, visibility default private |
| projects | id, vehicle_id FK, slug unique, title, goal, status draft/published/archived, visibility private/public, published_at nullable |
| project_entries | id, project_id FK, category maintenance/modification, title, description, occurred_on, cost nullable, cost_visible default false |
| entry_parts | id, project_entry_id FK, manufacturer nullable, name, part_number nullable, notes; compatibilidade descrita como relato, sem inferência automática |
| workshops | id, owner_id FK users, slug unique, name, description, city, state, commercial_whatsapp, status pending/approved/suspended |
| specialties | id, name unique; catálogo editorial mínimo |
| specialty_workshop | workshop_id FK, specialty_id FK; unique composto |
| workshop_attributions | id, project_entry_id FK, workshop_id FK, status pending/accepted/rejected/revoked, requested_at, decided_at, revoked_at; unique composto |
| quote_requests | id, requester_id FK users, workshop_id FK, vehicle_id FK, project_id nullable FK, goal, shared_contact, contact_shared_at, status, responded_at nullable, closed_at nullable |
| quote_updates | id, quote_request_id FK, actor_id nullable FK users, previous_status, status, response nullable, created_at |
| vehicle_photos / project_photos | id, vehicle_id/project_id FK, uploader_id FK, disk, path unique, mime, size, width, height, position, processing_status |
| events | id, created_by FK users, title, description, starts_at, timezone, venue, city, state, category, organizer, status draft/published/cancelled |

Cardinalidades: usuário 1:N veículos; veículo 1:N projetos; projeto 1:N entradas; entrada 1:N peças; usuário 1:N oficinas; oficina N:N especialidades; entrada N:N oficinas via autorização; usuário e oficina 1:N solicitações; solicitação 1:N atualizações. Perfil 1:1 usuário. Portfólio é consulta às associações aceitas em projetos públicos, sem duplicar conteúdo.

## Integridade e consulta

- Índices: vehicles(brand,model,visibility), projects(status,visibility,published_at), workshops(state,city,status), quote_requests(workshop_id,status,created_at), events(state,city,starts_at). Confirmar planos de consulta com volume real antes de adicionar mecanismos de busca.
- Projeto deriva dono do veículo, evitando user_id redundante. Serviço de solicitação confirma que projeto pertence ao veículo e veículo ao solicitante; FK isolada não garante isso.
- Serviços transacionais governam estados e atualizações. Policies validam propriedade; conta autenticada por si só não autoriza leitura.
- Restrição de valores não negativos e enumeração na validação; adicionar CHECKs MySQL quando migrations forem implementadas. Ano validado com limite explícito, sem fazer prova de compatibilidade técnica.
- Exclusão de veículo exige confirmação e remove projetos/entradas/peças relacionadas; imagens excluídas por Job após commit, com retentativas. Orçamentos precisam política de retenção definida antes de liberar exclusão em produção; não aplicar cascata silenciosa a registros comerciais.
- Mídia usa chaves aleatórias e bucket privado. Leitura autorizada antes de gerar URL temporária; para revogação imediata, servir por endpoint autenticado. Upload em quarentena até processamento/remoção de EXIF.
- Revogar associação retira atribuição e portfólio, preserva histórico privado de decisão. Nunca revela projeto privado à oficina via portfólio.
- Seeders do produto devem conter apenas catálogos verificáveis; demonstrações explicitamente fictícias só em local/testes.
