# MotorClub — PRD inicial

Versão 0.1 • 07/10/2026 • decisões propostas, sujeitas ao piloto.

## Problema e tese

Entusiastas encontram referências dispersas e têm dificuldade para entender o que foi feito em um carro semelhante e quem executou. Oficinas precisam de contatos com contexto suficiente para avaliar um serviço. O produto conecta **projeto → modificações e peças → profissional autorizado → pedido de orçamento**.

Foco inicial: Golf MK7, TSI e GTI, em uma região com oficinas parceiras. A região ainda deve ser escolhida pela capacidade de recrutar oferta; não presumimos uma cidade ou parceiros existentes. A expansão depende de evidência. MotorClub é nome provisório, sem pesquisa de marca concluída.

## Análise crítica e concorrência

Pesquisa exploratória em páginas públicas consultadas em 07/10/2026. As descrições abaixo são declarações dos próprios fornecedores, não auditorias nem prova de participação de mercado.

| Referência | Oferta observada | Implicação para MotorClub |
|---|---|---|
| [Car10](https://cotacao.car10.com.br/campanha) e [portal de oficinas](https://oficina.car10.com.br/) | Orçamentos a partir de fotos e conexão com oficinas próximas | Pedir orçamento sozinho não diferencia o produto |
| [Instauto](https://www.instauto.com.br/) | Busca regional de oficinas, avaliações, pedidos de orçamento e agendamento | Diretório e filtros de localização são funcionalidades básicas |
| [Mecanizou / Car10](https://institucional.mecanizou.com/car10) | Conexão entre oficinas e fornecedores de autopeças | Não competir inicialmente em estoque, catálogo ou logística |

Diferencial proposto, ainda não validado: referência estruturada por veículo/versão/motor e objetivo de modificação; histórico com autoria; associação do serviço à oficina mediante autorização; pedido que carrega contexto do projeto. A pesquisa não demonstra ausência de concorrentes equivalentes. Comunidades e redes sociais são substitutos a investigar em entrevistas, sem presumir suas limitações.

Risco principal: falta simultânea de projetos úteis e oficinas responsivas. Recrutar manualmente 3–5 oficinas e 15–20 donos antes de ampliar aquisição. Evitar feed genérico, likes e seguidores. Qualidade e retorno dos contatos importam mais que volume de cadastro. Não prometer ganho de potência, segurança, legalidade ou compatibilidade com base em relato de usuário.

## Objetivo e experimento

Piloto proposto de 30 dias após estabilização, com até 30 entusiastas convidados e 3–5 oficinas. Números são metas de experimento, não usuários existentes ou previsões.

- Ativação: ao menos 50% dos convidados cadastrados criam veículo e um projeto em 7 dias.
- Utilidade: ao menos 10 solicitações legítimas, de 5 usuários distintos, com veículo, objetivo e oficina identificados.
- Resposta: ao menos 70% dos pedidos recebem atualização da oficina em dois dias úteis.
- Retenção: ao menos 30% dos ativados retornam para atualizar projeto ou acompanhar pedido em 30 dias.
- Monetização: entrevistar todas as oficinas após contatos reais; buscar ao menos 3 declarações concretas de disposição de pagamento, registrando faixa e condições, sem cobrar.

Medir denominadores, janela, duplicatas e amostra; metas pequenas orientam entrevistas, não demonstram adequação ao mercado. Registrar eventos internos mínimos (cadastro, primeiro veículo/projeto, solicitação, resposta), sem texto de mensagens nem dados pessoais em analytics. Se resposta falhar, ajustar oferta e operação antes de tráfego pago.

## Escopo priorizado

| Prioridade | Entrega | Critério de aceite |
|---|---|---|
| P0 | Cadastro, login, saída, recuperação e verificação de e-mail | Sessão protegida; tokens válidos e expirados testados; envio real verificado em homologação |
| P0 | Perfil e privacidade | Nome público separado de contato; privado por padrão; e-mail nunca público |
| P0 | Garagem | Dono cria/edita/exclui veículo; marca, modelo, versão, ano e motor; terceiro não altera nem vê privados |
| P0 | Projetos e histórico | Projeto vinculado a veículo próprio; entradas de manutenção/modificação, peças e fotos; custo opcional e divulgação separada |
| P0 | Descoberta e compartilhamento | Filtros de marca/modelo e categoria, paginação; apenas publicados e veículos públicos aparecem |
| P0 | Oficinas | Perfil com cidade/UF, especialidades e WhatsApp comercial; revisão manual antes de publicação |
| P0 | Orçamentos | Usuário verificado envia contexto à oficina selecionada; apenas solicitante e oficina leem; estados e primeira resposta registrados |
| P0 | Autoria/portfólio | Oficina aceita associação antes de aparecer como executora; revogação remove a referência pública |
| P0 | Operação segura | Moderação manual, canal de suporte, exclusão/exportação assistidas, backups restaurados e imagens privadas protegidas |
| P1 | Eventos simples | Operador cadastra, altera e cancela; público filtra região; data, fuso, organizador e categoria |
| P1 | Aperfeiçoamento | Melhorias de busca e onboarding conforme entrevistas |
| Fora | Cobrança, checkout, catálogo de compatibilidade, chat, avaliações, app nativo, recomendação por IA | Reavaliar apenas após validação |

Eventos permanecem no MVP ampliado, mas não bloqueiam o primeiro experimento do fluxo de orçamento. Integração WhatsApp significa link externo; envio automatizado e API não fazem parte do escopo.

## Regras de negócio

Uma conta pode ser entusiasta e responsável por oficina; evitar papéis mutuamente exclusivos. Administração nunca é selecionável no cadastro. Veículo privado impede exposição dos projetos e suas imagens, mesmo com projeto marcado público. Projeto só publica com título, veículo e ao menos uma entrada útil. Valores são BRL decimal, opcionais, não negativos e privados por padrão. Publicação de preço exige decisão explícita do autor.

Orçamento é solicitação, não oferta vinculante ou reserva. Estados: enviado → em análise → respondido → encerrado; solicitante pode cancelar antes do encerramento. Resposta interna mínima registra resumo e data; WhatsApp é complementar, pois clique não prova resposta nem venda. Oficina só recebe contatos explicitamente compartilhados. Não distribuir pedidos automaticamente.

Mudança de privacidade deve invalidar caches e impedir acesso direto a mídia. URLs assinadas têm expiração curta; não garantem revogação de cópias já baixadas. Não coletar placa, chassi, documento ou endereço residencial no piloto. Remover metadados de localização das imagens antes da publicação. Conteúdo de terceiros necessita autorização e mecanismo de denúncia.

## Requisitos não funcionais e riscos

Interface em português, mobile first, navegação por teclado, rótulos e erros acessíveis. Paginação padrão 20 itens. Medir p95 de páginas principais em homologação; meta inicial menor que 800 ms no servidor, com carga de 20 usuários concorrentes, sem tratar como SLA.

CSRF, limitação de tentativas, validação de uploads JPEG/PNG/WebP (5 MB e limites de dimensões), escape de conteúdo e Policies obrigatórias. Banco MySQL, backups e armazenamento de objetos privado em produção. Sem dependência do disco efêmero do deploy.

Preparar inventário de dados, finalidade, retenção, canal do titular e procedimento de exclusão antes do convite público. As medidas aqui são requisitos de produto; não equivalem a certificação jurídica de LGPD. Prazos definitivos e textos legais precisam ser definidos com o responsável pelo negócio. Nunca usar usuários ou avaliações fictícias como prova social.

## Decisões pendentes, sem impedir a fundação

Região do piloto; responsável pela moderação e suporte; recrutamento de oficinas; domínio; identidade definitiva; provedor de e-mail e orçamento de infraestrutura. Nenhuma contratação está autorizada por este documento.
