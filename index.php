<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/todos.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = getCurrentUserId();
$allTodos = getTodos($userId);

$todosByState = [
    'draft' => [],
    'todo' => [],
    'doing' => [],
    'done' => [],
    'trash' => []
];

foreach ($allTodos as $t) {
    if (isset($todosByState[$t['state']])) {
        $todosByState[$t['state']][] = $t;
    }
}
$currentUser = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TodoPomodoro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body>
    <div id="toast-container" class="toast-container"></div>

    <header class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="navbar-brand">
                <span class="brand-name">Todo<strong>Pomodoro</strong></span>
            </a>
            <div class="user-menu">
                <span>Ola, <strong><?= htmlspecialchars($currentUser['name']) ?></strong></span>
                <button onclick="handleLogout()" class="btn btn-sm btn-outline">Sair</button>
            </div>
        </div>
    </header>

    <main class="main-content container">
        <div class="dashboard-header">
            <div class="dashboard-title-group">
                <h2>Quadro Kanban</h2>
                <p>Gerenciamento de tarefas e produtividade</p>
            </div>
            <button class="btn btn-primary" onclick="openCreateModal()">+ Nova Tarefa</button>
        </div>

        <div class="kanban-board">
            <?php
            $columns = [
                'draft' => 'Rascunho',
                'todo' => 'A Fazer',
                'doing' => 'Fazendo',
                'done' => 'Concluido',
                'trash' => 'Lixeira'
            ];
            foreach ($columns as $stateKey => $stateName):
            ?>
                <div class="kanban-column <?= $stateKey === 'trash' ? 'column-trash' : '' ?>" data-state="<?= $stateKey ?>">
                    <div class="column-header">
                        <h3><?= $stateName ?> <span class="badge" id="count-<?= $stateKey ?>"><?= count($todosByState[$stateKey]) ?></span></h3>
                        <?php if ($stateKey === 'trash' && count($todosByState['trash']) > 0): ?>
                            <button class="btn-link-danger" onclick="emptyTrash()">Esvaziar</button>
                        <?php endif; ?>
                    </div>
                    <div class="column-cards" id="column-<?= $stateKey ?>">
                        <?php foreach ($todosByState[$stateKey] as $todo): ?>
                            <div class="todo-card" data-id="<?= $todo['id'] ?>" data-state="<?= $todo['state'] ?>">
                                <div class="todo-card-header">
                                    <h4 class="todo-title"><?= htmlspecialchars($todo['todo_name']) ?></h4>
                                    <div class="todo-actions">
                                        <?php if ($todo['state'] !== 'trash'): ?>
                                            <button class="btn-icon" title="Focar" onclick="setFocusTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['todo_name'])) ?>')">Focar</button>
                                            <button class="btn-icon" title="Editar" onclick='openEditModal(<?= htmlspecialchars(json_encode($todo)) ?>)'>Editar</button>
                                            <button class="btn-icon" title="Lixeira" onclick="deleteTodo(<?= $todo['id'] ?>)">Excluir</button>
                                        <?php else: ?>
                                            <button class="btn-icon" title="Restaurar" onclick="changeTodoState(<?= $todo['id'] ?>, 'todo')">Restaurar</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($todo['todo_description'])): ?>
                                    <p class="todo-desc"><?= nl2br(htmlspecialchars($todo['todo_description'])) ?></p>
                                <?php endif; ?>
                                <div class="todo-card-footer">
                                    <span><?= date('d/m/Y H:i', strtotime($todo['updated_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Pomodoro Floating Widget -->
    <div id="pomodoro-widget" class="pomodoro-floating-widget">
        <div class="pomodoro-header">
            <span class="pomodoro-title">Pomodoro Timer</span>
            <button id="pomodoro-minimize-btn" class="btn-icon" title="Minimizar">_</button>
        </div>
        <div class="pomodoro-body">
            <div id="pomodoro-focused-task" class="focused-task-display hidden">
                <span class="focus-label">Foco:</span>
                <span id="focus-task-name" class="focus-task-name">---</span>
                <button class="btn-icon-sm" onclick="clearFocusTodo()">X</button>
            </div>
            <div class="timer-display-container">
                <div id="timer-display" class="timer-display">25:00</div>
                <div id="timer-mode-badge" class="timer-mode-badge work">Trabalho</div>
            </div>
            <div class="timer-controls">
                <button id="timer-start-btn" class="btn btn-primary btn-sm">Iniciar</button>
                <button id="timer-pause-btn" class="btn btn-warning btn-sm hidden">Pausar</button>
                <button id="timer-reset-btn" class="btn btn-secondary btn-sm">Resetar</button>
            </div>
            <div class="pomodoro-stats-summary">
                <span>Sessoes hoje: <strong id="pomodoro-completed-count">0</strong></span>
                <button class="btn-link-sm" onclick="togglePomodoroHistory()">Historico</button>
            </div>
        </div>
    </div>

    <!-- Modal Nova / Editar Tarefa -->
    <div id="todo-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-todo-title">Nova Tarefa</h3>
                <button class="modal-close" onclick="closeTodoModal()">X</button>
            </div>
            <form id="todo-form" onsubmit="handleTodoFormSubmit(event)">
                <input type="hidden" id="todo-id">
                <div class="form-group">
                    <label for="todo-name">Titulo</label>
                    <input type="text" id="todo-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="todo-description">Descricao</label>
                    <textarea id="todo-description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="todo-state">Estado</label>
                    <select id="todo-state" class="form-control">
                        <option value="draft">Rascunho</option>
                        <option value="todo" selected>A Fazer</option>
                        <option value="doing">Fazendo</option>
                        <option value="done">Concluido</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeTodoModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Historico -->
    <div id="pomodoro-history-modal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Historico Pomodoro</h3>
                <button class="modal-close" onclick="togglePomodoroHistory()">X</button>
            </div>
            <div class="modal-body">
                <div class="stats-boxes">
                    <div class="stat-box">
                        <span class="stat-val" id="stats-total-minutes">0</span>
                        <span class="stat-lbl">Minutos Focados</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-val" id="stats-total-sessions">0</span>
                        <span class="stat-lbl">Sessoes Concluidas</span>
                    </div>
                </div>
                <h4>Ultimas Sessoes</h4>
                <ul id="pomodoro-history-list" class="history-list">
                    <li class="empty-msg">Nenhuma sessao registrada.</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="js/auth.js"></script>
    <script src="js/todos.js"></script>
    <script src="js/pomodoro.js"></script>
</body>
</html>
