# Autoverse — Fase 0: auditoria técnica e preparação

Data: 13/10/2026

## Decisão recomendada

Começar a Fase 1 com migrações aditivas sobre o domínio atual. O projeto já possui uma base funcional para carros, projetos, modificações, peças, registros de veículo, fotografias privadas e Shots. Não há motivo para reescrever o domínio ou criar uma entidade genérica de “Photo” antes de validar os novos fluxos.

A primeira entrega deve suportar **carro e moto**, preservar as telas e URLs atuais e introduzir apenas as fundações que desbloqueiam o restante do plano: tipo de veículo, slug público estável, histórico de slug, cadastro adaptativo e separação explícita entre manutenção e modificação.

## Mapa da arquitetura atual

| Camada | Implementação encontrada | Reuso para o plano |
| --- | --- | --- |
| Aplicação | Laravel, PHP 8.4, Vite | Preservar o monólito modular. |
| Interface | Blade, Livewire Volt, Alpine.js, Tailwind/Flux | Reusar componentes públicos, settings, garagem e uploader. |
| Domínio | Eloquent Models em `app/Models` | Estender `Vehicle`, `Project`, `ProjectModification`, `ProjectPart`, `VehicleEntry` e `MediaPhoto`. |
| Autenticação | Google OAuth, verificação de e-mail, sessão Laravel | Manter; não criar autenticação paralela para a Fase 1. |
| Administração | Filament em `/central-mc-gestao` | Reusar para tipos, flags e moderação quando necessário. |
| Arquivos | Disk `local` privado por padrão; configuração S3 disponível | Manter originais fora do acesso público direto. |
| Processamento | `VehicleImages` e `ShotImages` | Generalizar somente quando houver segundo consumidor real. |
| Assíncrono | Queue `database`; job `SendShotDelivery` | Validar worker antes de adicionar jobs de imagem. |
| Cache | Cache `database`; uso atual em widget Filament | Não introduzir Redis sem necessidade. |
| Integrações | Shots, Stripe e gerador de relatório fake | Manter limites de licença e usar o Shots existente. |
| Testes | Pest/PHPUnit, SQLite em memória, 158 testes passando | Adicionar regressões para cada migração e manter a suíte rápida. |

## Inventário de entidades

### Núcleo de produto

- `User`: proprietário, perfil público, conta de fotógrafo, papel administrativo e dados de autenticação.
- `Vehicle`: carro atual do sistema, com marca, modelo, acabamento, ano, motor, quilometragem, localização, visibilidade e proprietário.
- `Project`: projeto único por veículo na validação atual (`vehicle_id` possui índice, e a regra de unicidade é aplicada no request).
- `ProjectModification`: alteração com categoria, status, datas, ordenação e visibilidade.
- `ProjectPart`: peça ligada a uma modificação, com nome, marca, modelo, status e visibilidade.
- `VehicleEntry`: registro de manutenção ou histórico com categoria, data, peças, serviços, custo e visibilidade.
- `VehiclePhoto` e `ProjectPhoto`: estruturas de fotografia específicas para veículo e projeto.
- `MediaPhoto`: relação polimórfica para fotos de projeto, modificação e peça, com caminho, tipo, ordem, capa e visibilidade.

### Shots e módulos adjacentes

- `PhotographerProfile`, `ShotGallery`, `ShotPhoto`, `ShotPurchaseRequest`, `ShotDownloadGrant`, `ShotReport` e `ShotPaymentEvent` formam um módulo próprio com autoria, licença, preview protegido, compra e moderação.
- `CarEvent`, `Workshop`, `QuoteRequest`, billing mock/Stripe e `ProjectReport` existem, mas não são pré-requisitos da primeira migração de veículos.

## Inventário do banco atual

O MySQL local está migrado na porta 3306. As tabelas encontradas são:

`users`, `vehicles`, `vehicle_entries`, `vehicle_photos`, `projects`, `project_modifications`, `project_parts`, `project_photos`, `media_photos`, `photographer_profiles`, `shot_galleries`, `shot_photos`, `shot_purchase_requests`, `shot_download_grants`, `shot_reports`, `shot_payment_events`, `car_events`, `workshops`, `quote_requests`, `billing_accounts`, `mock_subscriptions`, `mock_billing_events`, `project_reports`, `feature_settings`, `sessions`, `jobs`, `failed_jobs`, `job_batches`, `cache`, `cache_locks`, `password_reset_tokens` e `migrations`.

Relacionamentos relevantes já protegidos por foreign key:

- `users → vehicles`, `projects`, `shot_galleries`, `photographer_profiles`, `workshops` e billing.
- `vehicles → projects`, `vehicle_entries`, `vehicle_photos` e solicitações de Shots.
- `projects → project_modifications`, `project_parts` indiretamente, `project_photos`, `media_photos` e relatórios.
- `shot_galleries → shot_photos`; compras, grants, reports e eventos preservam o encadeamento do asset.

Índices importantes já existentes incluem proprietário de veículo, marca/modelo/visibilidade, ordem de modificações e peças, visibilidade do histórico, polimorfismo de `media_photos`, UUID de Shots e unicidade de projeto por veículo aplicada no domínio.

Baseline do banco local usado na auditoria: 1 usuário demo, 0 veículos, 0 projetos, 0 modificações, 0 peças, 0 fotos de veículo/projeto e 3 galerias públicas com 3 fotos de Shots. Isso é fixture local, não volume de produção.

## Funcionalidades reutilizáveis

- `Garage\Index` e `Garage\Details` já cobrem lista, cadastro, edição, fotos, histórico e navegação do veículo.
- `ProjectController` já cobre projeto, modificações, peças, fotos e revista/projeção.
- `VehiclePolicy` e `ProjectPolicy` já isolam proprietário e conteúdo público.
- `PhotoUploader`, `VehicleImages`, `ShotImages` e o disk privado já resolvem parte importante de upload e proteção.
- `ProjectMagazinePresenter` já demonstra a revista como projeção dos dados do projeto.
- `FeatureSetting` permite desligar módulos sem remover código.
- Testes existentes cobrem autenticação, autorização, garagem, projetos, Shots, SEO, segurança, billing e flags.

## Gaps e incompatibilidades para o plano alvo

| Área | Estado atual | Implicação |
| --- | --- | --- |
| Tipo de veículo | Não existe `vehicle_types` nem coluna de tipo. | Adicionar tipo nullable/default antes de tornar obrigatório. |
| Campos comuns | `brand`, `model`, `trim`, `model_year` e `engine` são obrigatórios. | Cadastro de moto precisa de validação condicional sem quebrar registros existentes. |
| Atributos específicos | Não existe `vehicle_attributes`. | Começar com campos estruturados mínimos; adiar EAV até haver um caso que não caiba no schema. |
| Slug público | Veículo público usa rota numérica `veiculos/{vehicle}`. | Criar slug e histórico com redirecionamento permanente; manter a rota numérica legada. |
| Manutenção | `vehicle_entries` mistura histórico, manutenção, peças e serviços. | Introduzir uma camada de classificação e consulta antes de criar `maintenance_records`. |
| Fotografia | Há três estruturas (`vehicle_photos`, `project_photos`, `media_photos`). | Não migrar arquivos na Fase 1; criar um contrato comum de leitura e preservar tabelas. |
| Antes/depois | Modificação não tem campos de foto antes/depois; usa `media_photos`. | Derivar por `kind`/ordem e só criar colunas quando a interface exigir sem ambiguidade. |
| Revista | Existe presenter e telas, mas não `magazine_settings`. | Adicionar settings somente junto à personalização da Fase 3. |
| QR Code | Não há serviço ou rota. | Adiar para Fase 4 e escolher biblioteca após verificar necessidade real. |
| Filas | Queue database configurada, mas só há um job de entrega. | Não prometer processamento assíncrono de imagens antes de validar worker. |
| Policies | Existem Vehicle/Project/User/FeatureSetting; não há policies dedicadas para fotos, peças, modificações ou revista. | Cobrir autorização no controller/service durante a Fase 1 e extrair policies quando o fluxo estabilizar. |

## Riscos de migração e compatibilidade

1. **Campos obrigatórios atuais:** adicionar tipo com default `car` ou backfill antes de constraint; nunca tornar novos campos obrigatórios na mesma migration que os cria.
2. **URLs legadas:** manter `veiculos/{id}` e adicionar resolução por slug; registrar redirecionamento somente quando o slug novo estiver persistido.
3. **Fotos espalhadas:** não mover arquivos nem duplicar registros na Fase 1; usar adaptador de leitura para veículo/projeto/modificação/peça.
4. **Regra de projeto único:** a unicidade hoje depende do request, não de índice único no banco. Antes de criar um constraint, auditar duplicidades em staging.
5. **Dados privados:** `visibility` do veículo, `is_public` de projeto/entradas/modificações/peças e policies devem ser avaliados juntos; não confiar apenas na Blade.
6. **Arquivos órfãos:** exclusões usam callbacks de modelo que podem falhar se o arquivo não existir ou o disk mudar. Qualquer generalização de fotos deve ser transacional e idempotente.
7. **Dados de Shots:** licença, preview e original têm regras próprias. A Fase 1 não deve importar assets nem alterar o modelo de compra.

## Plano de compatibilidade para a Fase 1

### Migration A — tipos de veículo

- Criar `vehicle_types` somente se a administração dinâmica for necessária; caso contrário, começar com enum/string controlada.
- Adicionar `vehicle_type` ou `vehicle_type_id` nullable/default `car` em `vehicles`.
- Backfill todos os registros existentes para `car`.
- Adicionar índice somente após confirmar as consultas de exploração.

### Migration B — identidade pública

- Adicionar `slug` nullable e `slug_updated_at` em `vehicles`.
- Criar `vehicle_slug_histories` com índice único em slug histórico.
- Gerar slugs idempotentes usando nome público ou marca/modelo/trim.
- Resolver colisões com sufixo legível, nunca com ID exposto como primeira opção.
- Manter a rota numérica atual e adicionar redirect para slug quando a página estiver pública.

### Migration C — cadastro adaptativo

- Manter colunas atuais.
- Validar carro e moto em Form Request/Livewire com regras condicionais.
- Evitar EAV na primeira entrega; usar `generation`, `color`, `engine` e `mileage` já existentes.
- Adicionar novos campos como nullable e documentar os que são públicos.

### Migration D — histórico e fotos

- Não criar `maintenance_records` imediatamente.
- Classificar `vehicle_entries.category` e expor manutenção separadamente na consulta.
- Criar contrato de leitura para as três estruturas de fotos.
- Só consolidar storage depois de medir duplicação e dependência dos caminhos atuais.

## Plano da Fase 1

### Escopo

- Cadastro de carro e moto.
- Tipo e campos condicionais.
- Slug público estável e histórico.
- Compatibilidade da rota numérica.
- Galeria do veículo sem mistura com projeto/modificação/peça.
- Separação de manutenção na navegação e nos filtros.
- Regressões de autorização e privacidade.

### Fora do escopo

- QR Code, imagens sociais e compartilhamento avançado.
- Nova biblioteca de QR ou processamento pesado em fila.
- Exploração de peças como catálogo.
- Integração de assets licenciados do Shots.
- Reconhecimento de placa/pessoa.
- Soft delete de veículos sem decisão de retenção.

### Componentes de implementação

- `VehicleTypeSelector` dentro do formulário existente de garagem.
- Regras condicionais no `Garage\Index`/request correspondente.
- `VehicleSlugService` isolado para normalização, unicidade e histórico.
- `PublicVehicleResolver` ou extensão de `PublicReferences` para slug atual/antigo.
- Adaptador de galeria que leia `VehiclePhoto`, `ProjectPhoto` e `MediaPhoto` sem duplicar arquivos.
- Estados Blade para privado, slug antigo, veículo inexistente e sem fotos.

### Testes de aceite

- Veículo existente continua acessível pela rota atual.
- Usuário cria carro e moto com campos pertinentes.
- Campos irrelevantes não bloqueiam o cadastro.
- Usuário não edita veículo de outra conta.
- Slug atual é único, normalizado e sem dados privados.
- Slug antigo retorna redirect permanente.
- Alterar nome não invalida o slug publicado.
- Veículo privado nunca aparece em rota pública, metadado ou listagem.
- Fotos continuam no contexto correto e exclusões não removem asset de outra entidade.
- `prefers-reduced-motion`, teclado e mobile permanecem funcionais.

## Baseline de validação

- `php84 vendor/bin/pest --compact`: **158 testes passando / 1024 assertions**.
- `npm run build`: concluído sem erro.
- `git diff --check`: concluído sem erro.
- `audit_project.py --mode report --no-write`: 0 findings, 0 warnings, 0 errors.
- `php84 artisan route:list --json`: 147 rotas; módulo Shots e painel Filament presentes.
- MySQL local: migrations aplicadas; todas as migrations estão com status `Ran`.
- Auditoria de duplicidade de `projects.vehicle_id`: nenhum registro duplicado no banco local.

## Próximo passo seguro

Antes de criar a primeira migration da Fase 1, executar uma consulta de duplicidade para `projects.vehicle_id`, confirmar a estratégia de tipo (`string` controlada ou `vehicle_types`) e revisar em staging a resolução de URLs públicas. Com essas três decisões registradas, a Migration A pode ser implementada sem alterar os arquivos físicos nem quebrar o fluxo atual.
