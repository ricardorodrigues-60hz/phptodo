Side Question: qual a funcao do functions.php,  o que sao helpers globais

## Função do `functions.php`

O arquivo `functions.php` funciona como o repositório central de utilitários e funções auxiliares da aplicação. Ele centraliza rotinas repetitivas que precisam ser reutilizadas em diferentes partes do código (como em `auth.php`, `todos.php`, `pomodoro.php` e `api.php`), evitando a duplicação de lógica.

Entre as responsabilidades do `functions.php` no projeto estão:
- Execução padronizada de consultas SQL com PDO (`query()`).
- Formatação e emissão de respostas JSON da API (`jsonResponse()`).
- Sanitização de dados de entrada para prevenção de XSS (`sanitize()`).
- Verificação do estado de autenticação e ID do usuário (`isLoggedIn()`, `getCurrentUserId()`).
- Geração e validação de tokens CSRF (`generateCsrfToken()`, `validateCsrfToken()`).

---

## O que são Helpers Globais?

**Helpers Globais** (ou funções auxiliares globais) são funções genéricas carregadas no escopo global da aplicação que executam tarefas utilitárias e pontuais. 

### Principais características:
1. **Reutilização**: Podem ser chamadas em qualquer arquivo do projeto após a inclusão (`require_once 'functions.php'`).
2. **Independência de Estado**: Geralmente recebem parâmetros de entrada, processam o dado e retornam um resultado sem modificar estruturas complexas.
3. **Desacoplamento**: Reduzem a complexidade dos arquivos principais, concentrando tratamentos como validações, segurança e formatações em um único lugar.
