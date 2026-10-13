# Roteiro de teste do MVP

## Acessos

- No computador que hospeda o site: http://127.0.0.1:8015
- Para mostrar as páginas públicas na mesma rede: http://10.3.6.109:8016

O IP pode mudar ao trocar de rede. O computador e o Docker precisam permanecer ligados. O login Google usa o retorno local da porta 8015; em outro dispositivo a prévia permite navegação pública, sem autenticação. Login em outros dispositivos exige domínio HTTPS e retorno OAuth desse domínio.

## Uma conta de entusiasta

1. Entre com Google. No primeiro acesso a conta é criada automaticamente.
2. Em Minha garagem, adicione um carro privado, envie uma foto e registre uma manutenção com custo.
3. Edite o carro e torne-o público. Abra o link em uma janela sem login: fotos e ficha aparecem, histórico privado não.
4. Em Meus projetos, crie um projeto para esse carro. Adicione peças, serviços e fotos. Publique e escolha se o custo será público.
5. Em Meu perfil, preencha cidade/apresentação e habilite perfil público. Confira a página pública e a busca na Comunidade.
6. Encontre uma oficina e envie um pedido de orçamento. Acompanhe em Orçamentos. O pedido fica na caixa interna; nenhum WhatsApp ou e-mail é enviado automaticamente.
7. Cadastre um evento, busque por cidade/estado e depois edite ou exclua seu próprio evento.
8. Volte o carro para privado. Sua página pública, projetos e imagens deixam de ser acessíveis por outros visitantes.

## Duas contas para testar a oficina

1. Na segunda conta Google, abra Meu perfil → Minha oficina; cadastre e publique a oficina.
2. Na primeira conta, crie/edite um projeto escolhendo essa oficina.
3. Na segunda conta, autorize o vínculo em Minha oficina. O projeto aparece no portfólio quando projeto e veículo estão públicos.
4. Revogue a autorização: o projeto sai do portfólio. Alterações no projeto também exigem nova autorização.
5. Envie um orçamento da primeira conta. A segunda responde com texto e valor opcional. A primeira vê a resposta e pode cancelar o pedido.
6. Uma terceira conta não deve ver esse orçamento nem editar carros, eventos ou projetos das outras contas.

## Demonstração

Há oito perfis/carros fictícios, oito projetos, três oficinas e três eventos de exemplo. As garagens inspiradas em criadores reais estão marcadas como não oficiais, com fontes das fotos e fichas simuladas. As oficinas fictícias não respondem nem prestam serviços reais. Para testar uma resposta de orçamento use a segunda conta acima.

Não se criam contas Google falsas nem atalhos que contornem a autenticação. As fotos demonstrativas anteriores são mantidas; esta entrega não gerou novas imagens.
