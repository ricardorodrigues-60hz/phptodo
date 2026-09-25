Função do auth.js
O arquivo js/auth.js gerencia as interações da interface de autenticação no navegador:

Captura de formulários: Intercepta o envio dos formulários de Login e Cadastro (handleLogin e handleRegister) para evitar o recarregamento padrão da página (e.preventDefault()).
Alternância de telas: Controla a exibição dos cards de login e registro via função toggleAuthBox().
Encerrar sessão: Executa a função handleLogout() ao clicar no botão "Sair".
O que é o Cliente Fetch para Autenticação?
O Fetch (window.fetch) é uma API nativa do JavaScript usada para fazer requisições HTTP assíncronas (AJAX) entre o navegador e o servidor.

No contexto da autenticação:

Em vez de enviar o formulário HTML de forma tradicional (que recarrega a tela), o auth.js coleta os campos (email/usuário e senha) e dispara uma requisição POST com payload JSON para o endpoint backend:
POST api.php?action=login
POST api.php?action=register
O servidor processa os dados, valida as credenciais, cria a sessão PHP ($_SESSION) e envia de volta uma resposta em formato JSON ({"success": true}).
Ao receber o JSON de sucesso no navegador, o auth.js redireciona o usuário para index.php usando window.location.href. Se houver erro, exibe uma mensagem sem perder o estado da página.
