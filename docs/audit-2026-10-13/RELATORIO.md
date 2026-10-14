# Autoverse — auditoria de UX e interface

Data da revisão: 13/10/2026

## Escopo e evidências

A revisão percorreu a home, autenticação, garagem, cadastro e edição de carro, detalhes do carro, projetos, modificações e peças, revista do projeto, Shots, comunidade, dashboard, configurações, cabeçalho, rodapé e alternância de tema.

Foram usados Product Design Audit, Impeccable, Emil Design Engineering, frontend-design-premium, Figma e Canva. O mapa de navegação foi criado no FigJam para o time **Pedro Esteves's Team**:

[Abrir mapa de navegação da Garagem no FigJam](https://www.figma.com/board/v60AdliBwl6Mc7uGrqEfI1?utm_source=chatgpt&utm_content=edit_in_figjam&oai_id=v1%2Fqwm6hM5ghhLZeL6EGp1LJEwN979eXzQY7JkN1R8AfqHA3480KA0kjE&request_id=337cee7d-bb51-472e-a591-4439e443a5c4)

No Canva foram localizadas seis propostas de logo da Autoverse. Não havia Brand Kit disponível, então nenhuma identidade foi sobrescrita. O launcher de contexto do Impeccable não pôde ser executado por uma restrição de permissão; a análise continuou com `PRODUCT.md`, `DESIGN.md`, o código e os testes existentes.

As capturas iniciais foram feitas em um banco SQLite isolado, porque o MySQL configurado no projeto ainda não estava disponível. Em uma segunda rodada, o MySQL local foi iniciado na porta 3306, as migrations foram aplicadas e as telas autenticadas foram repetidas com dados fictícios persistidos no banco real do projeto.

Na segunda rodada, a verificação visual foi feita também em viewport móvel: gestão do Shots, perfil e aparência de configurações foram abertos autenticados após o reinício do servidor, e as rotas públicas de Shots e comunidade responderam com dados persistidos. A rota local temporária usada para a entrada de auditoria foi removida e confirmou `404` depois da coleta.

## Jornada revisada

1. **Home — antes e depois.** A coluna decorativa com “Autoverse / assinatura”, “Design em movimento”, “Sem excesso” e “Animação SVG” desviava a atenção do produto e deixava a introdução com peso visual excessivo. Ela foi removida. O hero agora apresenta o que a Autoverse faz, com CTA e blocos de valor visíveis.

   Antes: [home-before.jpg](home-before.jpg) · Depois: [home-after.jpg](home-after.jpg)

2. **Autenticação.** A estrutura e os estados foram mantidos. O texto promocional genérico “Seu próximo capítulo começa aqui” foi removido da tela simples. O OAuth real foi repetido em produção: o botão Google redirecionou para a conta Google com `redirect_uri` do domínio publicado e o callback retornou à garagem autenticada. Nenhuma credencial foi digitada pelo agente.

3. **Minha garagem.** O cabeçalho agora explica a função da tela e usa “Adicionar carro” como ação principal. A lista de veículos ficou mais direta.

   Evidência: [garage.jpg](garage.jpg)

4. **Novo carro e projeto.** O formulário continua funcional, mas é uma jornada longa. O cabeçalho passou a explicar que o projeto e as fotos podem ser adicionados depois; a etapa falsa “Etapa 1 de 3” foi retirada. A seção de fotos explica a diferença entre galeria do carro e imagens de modificações.

   Evidência: [car-form.jpg](car-form.jpg)

5. **Detalhes do carro.** A hierarquia “Fotos / Projetos / Histórico” permanece clara. O diário do carro conecta galeria, manutenção e modificações sem misturar os contextos.

   Evidência: [car-details.jpg](car-details.jpg)

6. **Projeto, modificações e peças.** O editor passou a usar “Criar projeto” e “Editar projeto”, com a relação entre resumo, modificações e peças explícita. As fotos de modificações ficam associadas ao contexto correto. A revista usa “Revista do projeto” em vez de “Autoverse Magazine · Project Edition”.

   Evidência: [project.jpg](project.jpg)

7. **Shots e comunidade.** Os títulos e campos foram revisados para explicar a tarefa (“Fotografia automotiva”, busca com labels acessíveis). Com MySQL ativo, `/shots` foi verificado com três galerias públicas e prévias com marca-d’água; `/meus-shots` foi verificado autenticado com as três galerias do fotógrafo; `/comunidade` foi verificado com perfis de demonstração e um veículo público do usuário de auditoria.

8. **Dashboard, configurações e rodapé.** O dashboard agora usa “Sua conta” e aponta para configurações. As telas autenticadas de perfil e aparência foram verificadas com o usuário de auditoria; os estados “Claro”, “Escuro” e “Sistema” aparecem juntos e o formulário de perfil mantém foco e hierarquia. O rodapé deixou de exibir “Farol: tema claro / escuro”, que não tinha relação com sua função. O controle de tema continua disponível no cabeçalho, com texto de leitor de tela direto: “Ativar tema claro” ou “Ativar tema escuro”.

## Achados priorizados

| Prioridade | Achado | Tratamento |
| --- | --- | --- |
| P1 | Home tinha uma coluna conceitual que competia com a tarefa principal e continha texto sem função de produto. | Removida; hero e blocos de valor foram simplificados. |
| P1 | Rodapé descrevia o tema como “farol”, sem relação com o componente. | Removido; o tema ficou no controle de cabeçalho. |
| P1 | Animações de revelação podiam esconder conteúdo até o scroll e adicionar movimento sem benefício. | Observer global removido; imagens mantêm lazy loading e decodificação assíncrona. |
| P2 | Formulário de carro e editor de projeto são densos em uma única página. | Microcopy e agrupamento foram esclarecidos; uma divisão em etapas reais pode ser uma próxima iteração. |
| P2 | Alguns cabeçalhos públicos ainda usam linguagem editorial (“Uma garagem. Muitas histórias.”). | Mantidos quando funcionam como posicionamento da comunidade; revisar em teste de conteúdo futuro. |
| P3 | A primeira rodada não tinha banco persistente para validar os dados autenticados. | Resolvido na segunda rodada com MySQL local, migrations e fixtures de auditoria. |

## Antes e depois

| Área | Antes | Depois |
| --- | --- | --- |
| Home | Hero grande com painel “Design em movimento” e texto decorativo. | Introdução focada no produto, com CTA e valor legíveis. |
| Rodapé | “Farol: tema claro / escuro”. | Apenas marca e navegação pertinente. |
| Tema | Metáfora “farol aceso/apagado” no texto acessível. | Ação explícita: ativar tema claro ou escuro. |
| Garagem | “Mais que um carro. Parte de você.” e “Adicionar carro + projeto”. | “Minha garagem” e “Adicionar carro”. |
| Projeto | “Seu projeto”, “Uma nova história” e inglês na revista. | “Criar/Editar projeto” e “Revista do projeto”. |
| Movimento | Reveal global baseado em IntersectionObserver. | Conteúdo renderizado normalmente; imagens continuam otimizadas. |

## Validação técnica

- `npm ci` concluído.
- `npm run build` concluído com o Node.js empacotado do workspace; bundle Vite gerado sem erros.
- `php84 vendor/bin/pest --compact` concluído sem falhas.
- `git diff --check` concluído sem whitespace inválido.
- `audit_project.py --mode report --no-write` concluído com zero findings, warnings ou errors.
- Home verificada em viewport móvel de 390×844 e ultrawide de 3440×1440, sem overflow horizontal.
- MySQL local verificado na porta 3306; migrations e `ShotsDemoSeeder` executados com sucesso.
- Capturas autenticadas repetidas em `/meus-shots`, `/configuracoes/perfil` e `/configuracoes/aparencia`; `/shots` e `/comunidade` verificados com dados persistidos.

As pendências de OAuth em produção e densidade mobile foram fechadas na rodada complementar abaixo.

## Rodada complementar em produção

Em 13/10/2026, a auditoria visual foi repetida no ambiente publicado em `/`, `/login`, `/projetos`, `/explorar` e `/shots`. A home preservou o SVG do carro, o alinhamento central do palco e o movimento de ponteiro; a autenticação manteve o CTA Google e a hierarquia editorial; projetos, comunidade e Shots carregaram seus estados vazios sem overflow horizontal.

O fluxo OAuth foi executado diretamente em produção a partir de `/login`. O provedor exibiu o domínio publicado como destino, o callback concluiu a sessão e a página autenticada “Minha garagem” foi carregada com os atalhos de garagem, Shots, explorar e perfil. O teste usou a sessão Google já disponível no navegador, sem digitar ou registrar credenciais.

O formulário combinado de carro e projeto foi revisado em 390×844. A leitura, a ordem das três seções, os controles de arquivo e os alvos de toque permaneceram intactos; no mobile, o card passou a usar `1rem` de padding, os grupos passaram de `1.25rem/1.75rem` para `1rem/1.5rem` e os formulários públicos receberam a mesma compactação de ritmo. Os valores de desktop continuam preservados a partir dos breakpoints `sm`/`md`.

Foram aplicados três ajustes de interface após essa rodada:

- páginas públicas de projetos agora sinalizam **Explorar** no cabeçalho; **Garagem** permanece reservado à área pessoal e a `?mine=1`;
- estados vazios de projetos e Shots ganharam explicação curta e CTA de continuação;
- o rótulo acessível do painel visual foi reduzido para “Ilustração do carro”, removendo a linguagem decorativa antiga sem retirar o SVG.

As capturas desta rodada foram inspecionadas diretamente no navegador de produção. O conjunto local de antes/depois permanece em `docs/audit-2026-10-13/`; rotas autenticadas continuam limitadas pelo OAuth externo no ambiente publicado, embora tenham sido verificadas anteriormente com fixtures persistidas no MySQL local.

O commit `6b52fee` foi publicado no Render e ficou **Deploy succeeded | Live**. O build Vite e o health check `/up` passaram no deploy; a versão publicada mantém `/`, `/projetos` e `/shots` respondendo com HTTP 200.
