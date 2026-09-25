# Todo List + Pomodoro Timer

Sistema de gerenciamento de tarefas em estilo Kanban integrado a um cronometro Pomodoro, desenvolvido em PHP procedural, MySQL e JavaScript vanilla.

## Sumario

- [Funcionalidades](#funcionalidades)
- [Pre-requisitos](#pre-requisitos)
- [Instalacao e Setup](#instalacao-e-setup)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [API REST](#api-rest)
- [Modelo de Dados](#modelo-de-dados)
- [Seguranca](#seguranca)
- [Testes](#testes)
- [Troubleshooting](#troubleshooting)
- [Licenca](#licenca)

---

## Funcionalidades

- Autenticacao de usuarios (registro, login com hash bcrypt, controle de sessao).
- Quadro Kanban (Rascunho, A Fazer, Fazendo, Concluido, Lixeira) com Drag & Drop (SortableJS).
- Cronometro Pomodoro (25 min trabalho / 5 min descanso) com notificacoes no navegador.
- Vinculo de tarefas ao timer de foco e contagem de estatisticas.
- API REST com respostas em formato JSON.

---

## Pre-requisitos

- PHP 7.4+ (`pdo_mysql`)
- MySQL 5.7+ / MariaDB
- Servidor Web (Apache/Nginx) ou Docker Compose

---

## Instalacao e Setup

### Execucao via Docker (Recomendado)

```bash
docker compose up -d --build
docker compose exec app ./vendor/bin/phpunit -c tests/phpunit.xml --testdox
```
Acesse em: `http://localhost:8080`

### Setup Manual

```sql
CREATE DATABASE phptodo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'phptodo_user'@'localhost' IDENTIFIED BY 'phptodo_password';
GRANT ALL PRIVILEGES ON phptodo_db.* TO 'phptodo_user'@'localhost';
```

Importe o schema: `mysql -u phptodo_user -pphptodo_password phptodo_db < sql/schema.sql`

Ajuste as credenciais no arquivo `config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'phptodo_user');
define('DB_PASS', 'phptodo_password');
define('DB_NAME', 'phptodo_db');
```

---

## Estrutura do Projeto

```
phptodo/
├── config.php          # Conexao PDO e constantes
├── functions.php       # Helpers de consulta SQL e JSON
├── auth.php            # Registro, login e sessao
├── todos.php           # CRUD e estados Kanban
├── pomodoro.php        # Timer de foco e estatisticas
├── api.php             # Roteador central da API
├── index.php           # Dashboard Kanban + Pomodoro
├── login.php           # Interface de acesso e registro
├── css/style.css       # Estilos CSS
├── js/                 # Cliente HTTP e scripts de interatividade (auth, todos, pomodoro)
└── sql/schema.sql      # Estrutura do banco de dados
```

---

## API REST

Requisicoes `POST` exigem o header `Content-Type: application/json`.

### Autenticacao

- **`POST /api.php?action=register`**
  - Req: `{"name":"Ana","lastname":"Silva","username":"ana","email":"ana@exemplo.com","password":"123"}`
  - Res: `{"success":true,"data":{"id":1,"username":"ana"}}`

- **`POST /api.php?action=login`**
  - Req: `{"login":"ana","password":"123"}`
  - Res: `{"success":true,"data":{"id":1,"username":"ana"}}`

- **`GET /api.php?action=logout`**
  - Res: `{"success":true,"message":"Logout realizado."}`

### Tarefas

- **`GET /api.php?action=list_todos`** (filtro opcional: `&state=todo`)
  - Res: `{"success":true,"data":[{"id":1,"todo_name":"Tarefa 1","state":"todo"}]}`

- **`POST /api.php?action=create_todo`**
  - Req: `{"todo_name":"Estudar PHP","todo_description":"Revisar PDO","state":"todo"}`
  - Res: `{"success":true,"data":{"id":2,"todo_name":"Estudar PHP"}}`

- **`POST /api.php?action=change_state`**
  - Req: `{"id":2,"state":"doing"}`
  - Res: `{"success":true,"data":{"id":2,"state":"doing"}}`

- **`POST /api.php?action=delete_todo`**
  - Req: `{"id":2}`
  - Res: `{"success":true,"data":{"id":2,"state":"trash"}}`

- **`POST /api.php?action=empty_trash`**
  - Res: `{"success":true,"deleted_count":1}`

### Pomodoro

- **`POST /api.php?action=start_pomodoro`**
  - Req: `{"todo_id":1}`
  - Res: `{"success":true,"data":{"id":10,"completed":0}}`

- **`POST /api.php?action=complete_pomodoro`**
  - Req: `{"session_id":10}`
  - Res: `{"success":true,"data":{"id":10,"completed":1}}`

- **`GET /api.php?action=pomodoro_stats`**
  - Res: `{"success":true,"data":{"completed_sessions":3,"total_focus_minutes":75}}`

---

## Modelo de Dados

- **`users`**: `id`, `name`, `lastname`, `username` (UNIQUE), `email` (UNIQUE), `password` (hash).
- **`todos`**: `id`, `user_id` (FK), `todo_name`, `todo_description`, `state` (`draft`, `todo`, `doing`, `done`, `trash`).
- **`pomodoro_sessions`**: `id`, `user_id` (FK), `todo_id` (FK nullable), `duration_minutes`, `completed`, `started_at`, `ended_at`.

---

## Seguranca

- Consultas via PDO Prepared Statements (prevencao de SQL Injection).
- Hashing de senhas com `password_hash()` (Bcrypt).
- Cookies de sessao com flag `HttpOnly` e `session_regenerate_id()`.
- Sanitizacao com `strip_tags()` e `htmlspecialchars()` (prevencao de XSS).
- Isolamento de dados por `user_id`.

---

## Testes

### Execucao dos Testes Unitarios

```bash
docker compose exec app ./vendor/bin/phpunit -c tests/phpunit.xml --testdox
```

### Exemplo de Requisicao cURL

```bash
curl -i -c cookie.txt -b cookie.txt -H "Content-Type: application/json" \
  -X POST "http://localhost:8080/api.php?action=login" \
  -d '{"login":"ana","password":"123"}'
```

---

## Troubleshooting

- **Erro de Conexao DB**: Verifique se os containers estao ativos (`docker compose ps`) e confirme as credenciais em `config.php`.
- **Permissao de Arquivos**: Execute `chmod -R 755 .`.
- **404 no Servidor Web**: Garanta que o Apache aponta o `DocumentRoot` para a raiz do projeto e que o modulo `mod_rewrite` esta ativado.

---

## Licenca

Projeto licenciado sob a licenca **MIT**.
