-- Seed Data for Testing

-- Password is 'senha123'
INSERT INTO users (id, name, lastname, username, email, password) VALUES
(1, 'Usuário', 'Teste', 'testuser', 'teste@exemplo.com', '$2y$10$eO0V4hSgC.R4WdJk7P/K0.Y44c4cK/2S2H5470yW/g518M7h6gYpG')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO todos (id, user_id, todo_name, todo_description, state) VALUES
(1, 1, 'Definir arquitetura do projeto', 'Estruturar diretórios e padrão MVC em PHP POO', 'done'),
(2, 1, 'Implementar autenticação de usuários', 'Criar controllers, views e sessões seguras', 'doing'),
(3, 1, 'Criar Kanban de tarefas', 'Interface drag and drop para mover cards entre estados', 'todo'),
(4, 1, 'Integrar cronômetro Pomodoro', 'Widget flutuante com timer de 25 min trabalho e 5 min descanso', 'draft'),
(5, 1, 'Tarefa antiga para descarte', 'Esta tarefa deve ir para a lixeira', 'trash')
ON DUPLICATE KEY UPDATE id=id;
