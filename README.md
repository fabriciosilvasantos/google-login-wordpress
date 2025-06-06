# Google Login Plugin para WordPress

Este plugin permite que os usuários façam login no WordPress usando suas contas do Google.

## Autor
Fabrício Silva

## Versão
0.1

## Requisitos Mínimos do Sistema

### Requisitos do Servidor
- PHP 7.4 ou superior
- MySQL 5.6 ou superior
- Servidor web Apache 2.4+ ou Nginx 1.14+
- SSL ativo (HTTPS) - Obrigatório para OAuth
- Extensões PHP necessárias:
  - cURL
  - JSON
  - OpenSSL
  - mbstring

### Requisitos do WordPress
- WordPress 5.0 ou superior
- Tema compatível com WordPress 5.0+
- Permissões de escrita na pasta wp-content/plugins
- Permissões de escrita no banco de dados

### Requisitos do Navegador
- Navegador moderno com suporte a JavaScript
- Cookies habilitados
- JavaScript habilitado
- Suporte a HTTPS

### Requisitos de Conta
- Conta no Google Cloud Console
- Projeto configurado no Google Cloud Console
- API do Google+ API ativada
- Credenciais OAuth 2.0 configuradas

## Instalação

1. Faça o upload do plugin para a pasta `/wp-content/plugins/` do seu WordPress
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Vá para Configurações > Google Login
4. Configure as credenciais do Google OAuth

## Configuração do Google OAuth

1. Acesse o [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um novo projeto
3. Ative a API do Google+ API
4. Vá para Credenciais > Criar Credenciais > ID do Cliente OAuth
5. Configure as URIs de redirecionamento autorizadas:
   - `https://seusite.com/wp-admin/admin-ajax.php?action=google_login`
6. Copie o Client ID e Client Secret
7. Cole as credenciais nas configurações do plugin

## Uso

1. O botão "Login com Google" aparecerá automaticamente na página de login do WordPress
2. Os usuários podem clicar no botão para fazer login com suas contas do Google
3. Novos usuários serão criados automaticamente no WordPress
4. Usuários existentes serão vinculados às suas contas do Google

## Segurança

- Todas as requisições são validadas com nonces
- Dados são sanitizados antes de serem salvos
- Tokens são armazenados de forma segura
- Suporte a HTTPS

## Suporte

Para suporte, por favor abra uma issue no GitHub ou entre em contato através do email de suporte.

## Licença

Este plugin é licenciado sob a GPL v2 ou posterior. 