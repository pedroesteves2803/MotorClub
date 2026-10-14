# Autoverse — Fases 2 a 7

Implementação vertical das fases de evolução visual, revista, identidade pública, descoberta, integração com Shots e operação.

## Entregas

### Fase 2 — Evolução visual

- `EvolutionService` projeta modificações, peças e registros de manutenção em etapas ordenadas.
- Conteúdo privado é filtrado na página pública; o proprietário pode revisar o histórico completo.
- A página funciona como narrativa estática, com fotos públicas e fallback sem JavaScript.
- Rotas públicas e autenticadas: `/veiculos/{slug}/evolucao` e `/garagem/{id}/evolucao`.

### Fase 3 — Revista digital

- `MagazineService` persiste cor, tema, estilo editorial e capa por veículo.
- `ProjectMagazinePresenter` continua sendo a única fonte dos capítulos: projeto, ficha, evolução, modificações, peças e fotos.
- Leitura pública, leitura vertical e personalização autenticada estão disponíveis.
- Cores e temas são validados antes de salvar.

### Fase 4 — Identidade, compartilhamento e QR Code

- Slug estável com histórico e redirecionamento 301.
- Página de compartilhamento com copiar link, Web Share API, WhatsApp, Telegram, Facebook, X e e-mail.
- QR Code SVG público e downloads PNG/SVG autenticados apontam para a URL pública sem ID técnico.
- Eventos de QR e compartilhamento armazenam somente hashes de agente e IP.

### Fase 5 — Descoberta

- Exploradores públicos de projetos e peças com filtros combináveis por texto, marca, modelo e tipo de veículo.
- Apenas projetos, modificações e peças publicados entram nas consultas.
- A interface identifica dados declarados pelo proprietário e não infere compatibilidade técnica.

### Fase 6 — Shots

- Fotografia gratuita licenciada pode ser associada ao veículo preservando licença e crédito.
- Fotos comerciais exigem `ShotDownloadGrant` aprovado para o comprador e veículo.
- O fotógrafo não pode associar o próprio asset, e originais continuam no disk privado.
- Assets revogados deixam de aparecer na revista pública.

### Fase 7 — Operação

- `ShareEvent` e `ShareEventLogger` suportam métricas agregadas sem armazenar IP ou agente em claro.
- QR público possui cache HTTP de uma hora; páginas públicas continuam `private, no-store` enquanto a projeção não tiver invalidação distribuída.
- Comandos idempotentes para backfill, slugs, consistência pública, mídia, limpeza e versão do cache:

```bash
php84 artisan autoverse:backfill-vehicle-types --dry-run
php84 artisan autoverse:generate-slugs --dry-run
php84 artisan autoverse:validate-public-vehicles
php84 artisan autoverse:rebuild-media --dry-run
php84 artisan autoverse:cleanup-orphaned-media
php84 artisan autoverse:rebuild-public-cache
```

## Migração

- `2026_10_13_050000_create_magazine_sharing_and_shot_assets.php` foi aplicada no MySQL local.
- A migração é aditiva e não move arquivos existentes.

## Validação realizada

- Pest: **169 testes passando / 1.063 asserções**.
- Pint: passou em modo de verificação.
- Vite: build de produção concluído.
- `git diff --check`: sem erros.
- Comandos de consistência no MySQL: 0 slugs pendentes, 0 veículos públicos inconsistentes e 0 mídias órfãs.
- Capturas autenticadas repetidas em Shots, comunidade, perfil e aparência com MySQL ativo.

## Limite deliberado

A Fase 8 permanece futura: não foram adicionados pagamentos, comissão, revista premium ou serviços comerciais. O módulo Shots existente continua sendo a fonte de autorização e licenciamento.
