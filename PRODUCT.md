# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Entusiastas de carros, proprietários que documentam suas garagens e fotógrafos automotivos que publicam galerias para uma comunidade lusófona.

## Product Purpose

Autoverse organiza a vida automotiva em um único espaço: cada pessoa pode apresentar veículos, acompanhar projetos, descobrir a comunidade e comprar ou solicitar fotografias autorizadas. Sucesso significa tornar a história de um carro clara, confiável e fácil de continuar.

## Positioning

A garagem é o registro contínuo do carro, conectado a projetos, pessoas e fotografias protegidas. O produto une memória pessoal e descoberta pública sem transformar dados privados em conteúdo público.

## Operating Context

A experiência é usada no navegador em sessões curtas de descoberta e em sessões recorrentes de manutenção da garagem. Há visitantes públicos, usuários autenticados, fotógrafos e administração em `/central-mc-gestao`.

## Capabilities and Constraints

A aplicação é Laravel com Blade, Livewire, Flux, Tailwind e Filament. Rotas, autenticação, verificação, permissões, feature flags, privacidade, armazenamento privado, pagamentos simulados, downloads protegidos, webhooks e regras de negócio existentes devem permanecer intactos. O front-end deve continuar em português do Brasil, funcionar em desktop, tablet e mobile, e oferecer temas claro, escuro e sistema.

## Brand Commitments

O nome Autoverse e a assinatura “Seu carro. Sua história. Seu universo.” permanecem. A nova direção deve usar azul como cor de ação sobre preto/grafite e neutros, linhas finas inspiradas em faróis de um hatch esportivo, fotografia automotiva cinematográfica e uma linguagem editorial precisa. Laranja e cópias que indiquem “teste”, “demonstração” ou configuração incompleta não fazem parte da experiência pública.

## Evidence on Hand

As telas e fluxos existentes no repositório são a fonte de conteúdo e comportamento. Há imagens de demonstração em `public/images/demo/` e o símbolo de marca em `public/autoverse-mark.svg`; não devem ser inventados depoimentos, métricas, clientes ou especificações técnicas.

## Product Principles

- O carro é o protagonista; a interface cria espaço para sua história.
- Dados privados ficam claramente separados da publicação pública.
- Cada ação importante precisa de contexto, estado e retorno visível.
- Descoberta e manutenção compartilham a mesma linguagem visual, com densidade adequada a cada tarefa.
- Conteúdo real da aplicação vence decoração genérica.

## Accessibility & Inclusion

A experiência deve manter navegação por teclado, foco visível, contraste de pelo menos 4.5:1 para texto normal, alvos confortáveis em toque, textos alternativos para imagens e suporte a `prefers-reduced-motion`.
