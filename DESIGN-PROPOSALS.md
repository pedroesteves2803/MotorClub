# Autoverse — auditoria da Home e propostas de direção

## Escopo desta entrega

Esta etapa é somente Product Design: leitura da Home publicada, inspeção do HTML/CSS atual, análise das referências automotivas e exploração de três direções. Nenhuma tela do Laravel foi alterada nesta etapa.

## Auditoria da Home atual

### Pontos fortes

- A hierarquia do hero é clara e a mensagem principal aparece imediatamente.
- A base usa Space Grotesk + Instrument Sans, azul sobre grafite e uma marca linear coerente.
- Há skip link, headings semânticos, `focus-visible` e suporte a `prefers-reduced-motion`.
- O menu mobile tem estado explícito e os CTAs apontam para Garagem e Shots.
- O layout usa poucos componentes e tem boa margem para evolução sem quebrar o backend.

### Problemas por gravidade

**Alta — experiência de descoberta incompleta**

- A Home termina depois de três textos de capacidade e um CTA de comunidade.
- Não há garagens, projetos, fotografias ou fotógrafos reais em destaque para iniciar exploração.
- O usuário entende o posicionamento, mas não vê a prova visual do produto nem o próximo passo de descoberta.

**Alta — identidade automotiva ainda abstrata**

- O hero usa círculos e o símbolo da marca, sem fotografia cinematográfica ou detalhe de carro.
- A composição parece um editorial tipográfico; falta textura, escala e evidência do universo automotivo.

**Média — responsividade e navegação**

- No mobile, o primeiro viewport é dominado pelo texto e o painel-manifesto começa abaixo da dobra.
- O menu colapsado não oferece indicadores de rota ou uma transição visual demonstrável.
- A navegação não comunica a diferença entre descobrir, registrar e comprar Shots.

**Média — conversão e estados**

- Os dois CTAs do hero têm o mesmo peso narrativo, embora tenham objetivos diferentes.
- Não há prova de estado vazio, carregamento ou conteúdo recomendado na Home.

**Média — SEO público**

- O layout tem title e viewport, mas não encontrei canonical, Open Graph, JSON-LD, sitemap ou robots dedicado.
- As rotas públicas precisam de metadados dinâmicos e regras claras de indexação para conteúdo público versus privado.

**Baixa — consistência de conteúdo**

- A Home usa “Shots” como produto, mas o benefício de fotógrafo, galeria e compra só aparece no texto; falta uma amostra concreta.
- O rodapé e o seletor de tema ainda têm pouco peso de marca para uma experiência premium.

## O que absorvi das referências

- **Lamborghini:** navegação por universos de produto, linguagem de configuração e presença de modelos como protagonistas.
- **Porsche Mission S:** narrativa em capítulos, blocos editoriais com muito respiro e interface centrada no motorista.
- **Ferrari e Bugatti:** serão tratadas como referências de fotografia, proporção e apresentação de veículo; os ambientes não ficaram totalmente acessíveis nesta auditoria automatizada.

## Três direções visuais

### A — Blue Hour / Pit-lane editorial *(recomendada)*

**Ideia:** Autoverse como uma revista digital de cultura automotiva ao anoitecer.

- Fundo grafite quase preto, azul elétrico apenas para sinalizar ação e foco.
- Hero com recorte de farol ou frente de veículo em grande escala, texto sobreposto e uma coluna de manifesto.
- Home em capítulos: “Garagens em destaque”, “Projetos em andamento”, “Shots da semana” e convite para registrar o próprio carro.
- Tipografia Space Grotesk para títulos e Instrument Sans para leitura; números de edição e etiquetas pequenas para orientar a leitura.
- Movimento: entrada por máscara de imagem, título em linhas, troca de destaque com fade/translate curto e hover mínimo.

**Vantagens:** aproveita a base atual, reforça a marca azul e entrega personalidade sem virar um site de fabricante.

**Risco:** exige fotografia forte e uma curadoria mínima de conteúdo para não parecer apenas um hero bonito.

### B — Silverline / Gallery atelier

**Ideia:** Um ateliê editorial claro, com fotografias de carros tratadas como obras de portfólio.

- Fundo marfim frio e grafite em blocos alternados; azul profundo como assinatura.
- Hero dividido em imagem vertical + manifesto, com uma faixa horizontal de metadados do carro.
- Garagens e projetos apresentados em mosaico assimétrico; Shots como galeria de fotógrafo, com autoria explícita.
- Movimento: revelação por recorte, scroll suave entre capítulos e estados de seleção de foto com borda azul.

**Vantagens:** excelente legibilidade, sensação premium e forte diferenciação de um dashboard.

**Risco:** o modo escuro precisa ser desenhado com cuidado para não parecer uma inversão automática.

### C — Night drive / Signal grid

**Ideia:** A experiência como um painel noturno de navegação por sinais, inspirado em estrada e telemetria.

- Preto profundo, azul cobalto e cinzas frios; linhas finas, coordenadas e pequenos marcadores de percurso.
- Hero com imagem full-bleed, gradiente de legibilidade controlado e uma barra de “próximo capítulo”.
- Explorar vira o eixo principal; Garagem, Projetos e Shots aparecem como rotas do mesmo mapa editorial.
- Movimento: progressão de capítulos, indicadores de scroll e transições de rota com transform/opacity.

**Vantagens:** personalidade forte, boa memória visual e excelente espaço para microinterações.

**Risco:** pode ficar tecnológico demais e reduzir a sensação de comunidade se os sinais visuais dominarem as histórias.

## Próximo passo após sua escolha

Vou criar no Figma a Home escolhida em 1440 px, 768 px e 390 px, com tokens, componentes, Auto Layout, estados de interação e protótipo da navegação principal. A implementação no Laravel só começa depois da sua aprovação visual.

