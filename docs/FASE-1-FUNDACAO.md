# Autoverse — Fase 1: fundação de veículos

Implementação vertical da primeira fase do plano técnico, mantendo compatibilidade com o MVP existente.

## Entregas

- `vehicles.vehicle_type` com os valores `car` e `motorcycle`, incluindo seleção no cadastro e edição da garagem.
- `vehicles.slug` único, gerado a partir do nome público ou da identidade do veículo.
- `vehicle_slug_histories` para redirects permanentes quando um slug for alterado.
- URL pública amigável em `/veiculos/{slug}`.
- Rota numérica existente em `/veiculos/{id}` preservada como compatibilidade.
- Canonical, breadcrumb e JSON-LD da página pública apontando para o slug.
- Links de comunidade, perfis e sitemap usando o slug público.

## Critérios verificados

- Moto pode ser cadastrada pelo fluxo Livewire atual.
- Veículos com nomes iguais recebem sufixo incremental (`-2`, `-3`, ...).
- Slug antigo redireciona com HTTP 301 para o slug atual.
- Veículo privado continua retornando 404 em ambas as rotas públicas.
- Testes automatizados: 162 passaram, 1.037 asserções.
- Build Vite: concluído com sucesso.
- Migração aplicada no MySQL local em 13/10/2026.

## Limites desta fase

O formulário ainda compartilha os mesmos campos base para carros e motos. A especialização mais profunda de atributos, manutenção separada de `vehicle_entries`, QR code, busca e a consolidação das três estruturas de fotos permanecem para as fases seguintes.
