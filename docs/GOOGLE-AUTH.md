# Login e cadastro com Google

Implementado via Laravel Socialite: primeiro acesso cria conta; seguintes identificam pelo ID imutável do Google. E-mail precisa estar verificado. Não armazenamos tokens Google. OAuth state permanece vinculado à sessão. E-mail existente não vincula contas automaticamente: requer futuro fluxo de vinculação autenticada/suporte.

## Configuração necessária

No Google Cloud Console, configure a tela de consentimento e crie um cliente OAuth do tipo **Aplicativo da Web**. Em modo de teste, inclua os e-mails dos usuários de teste. Cadastre exatamente a URI autorizada:

`http://127.0.0.1:8015/auth/google/callback`

Configure no `.env` local (não no Git ou chat):

```
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8015/auth/google/callback
```

Execute `php artisan config:clear` com PHP 8.4. Em produção use o domínio HTTPS real e a URI correspondente, com secrets no Laravel Cloud. Apenas escopos básicos de identidade, nome e e-mail são usados. Sem permissões Gmail/Drive.

Abra `/login` em navegador normal (Chrome/Safari) para testar; Google pode restringir autenticação em navegadores incorporados. `/register` direciona ao mesmo fluxo. Sem credenciais, o aplicativo mostra mensagem de configuração pendente, sem simular autenticação.

Contas antigas com senha não são migradas automaticamente; recuperação local foi retirada da navegação e rotas públicas. Contas Google usam e-mail somente leitura e exclusão com confirmação explícita após acesso Google recente (10 minutos). Para renovar essa confirmação, sair e entrar novamente.

Referências: https://laravel.com/docs/13.x/socialite e https://developers.google.com/identity/openid-connect/openid-connect . Testes automatizados usam respostas simuladas do provedor; validação real depende das credenciais.
