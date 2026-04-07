# Planejamento do Projeto (TODO)

## ✅ Tarefas Concluídas
- [x] **Análise do Código:** Estrutura geral do plugin, padrões Singleton, hooks e configurações do WordPress avaliadas.
- [x] **Correção de Erros Fatais:** Removidas chamadas AJAX inexistentes (`wp_ajax_google_login` e `wp_ajax_nopriv_google_login`) para a função não implementada `handle_ajax_login` no arquivo `google-login.php`.
- [x] **Tratamento de Erros:** Substituído o uso de `wp_die()` (tela branca) por redirecionamentos de erro mais amigáveis (usando `wp_login_url()` com um parâmetro `?error`), integrando com o script existente `script.js` para alertar os usuários.
- [x] **Redirecionamento Pós-login Inteligente:**
  - Preservar intenção original (`redirect_to`) armazenando-a com segurança (JSON + Base64) no parâmetro `state` do Google OAuth.
  - Usuários que são administradores (`manage_options`) agora são redirecionados de volta ao painel de administração (`admin_url()`).
  - Outros usuários continuam sendo redirecionados para a página inicial (`home_url()`).

## ⏳ Tarefas Pendentes
- [ ] **Testes em Ambiente Real:**
  - Configurar um Client ID e Client Secret válidos no Google Cloud Console.
  - Simular o fluxo completo de autenticação OAuth2 (autorização aceita e recusada).
  - Verificar a criação de usuários e vinculação de emails na tabela `wp_users`.
- [ ] **Funcionalidade de Relacionamento de Contas:**
  - Adicionar opção para o usuário desvincular sua conta do Google na página do perfil.
  - Evitar duplicidade de emails e lidar com nomes de usuários longos caso o nome extraído do Google falhe na criação.
- [ ] **Aprimoramento Visual:**
  - Melhorar o layout do botão de login (`.google-login-button`) na tela de login padrão do WordPress com CSS para ficar mais aderente a um visual moderno (atualmente tem um estilo básico).
- [ ] **Suporte a Múltiplos Provedores (Opção Futura):**
  - Refatorar a estrutura da classe para suportar nativamente (além do Google) logins via Facebook ou GitHub caso necessário.