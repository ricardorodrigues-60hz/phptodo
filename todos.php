<?php
require_once __DIR__ . '/functions.php';

const VALID_TODOS_STATES = ['draft', 'todo', 'doing', 'done', 'trash'];

function createTodo(int $userId, string $name, ?string $description = null, string $state = 'draft'): array
{
    $name = sanitize($name);
    $description = $description ? sanitize($description) : null;
    $state = in_array($state, VALID_TODOS_STATES, true) ? $state : 'draft';

    if (empty($name)) {
        return ['success' => false, 'message' => 'Titulo da tarefa e obrigatorio.'];
    }

    query(
        "INSERT INTO todos (user_id, todo_name, todo_description, state) VALUES (?, ?, ?, ?)",
        [$userId, $name, $description, $state]
    );

    $id = (int) $GLOBALS['pdo']->lastInsertId();
    $todo = query("SELECT * FROM todos WHERE id = ? AND user_id = ?", [$id, $userId])->fetch();

    return ['success' => true, 'data' => $todo];
}

function getTodos(int $userId, ?string $state = null): array
{
    if ($state && in_array($state, VALID_TODOS_STATES, true)) {
        return query(
            "SELECT * FROM todos WHERE user_id = ? AND state = ? ORDER BY updated_at DESC",
            [$userId, $state]
        )->fetchAll();
    }

    return query(
        "SELECT * FROM todos WHERE user_id = ? ORDER BY updated_at DESC",
        [$userId]
    )->fetchAll();
}

function updateTodo(int $id, int $userId, string $name, ?string $description = null, string $state = 'draft'): array
{
    $existing = query("SELECT id FROM todos WHERE id = ? AND user_id = ?", [$id, $userId])->fetch();
    if (!$existing) {
        return ['success' => false, 'message' => 'Tarefa nao encontrada.'];
    }

    $name = sanitize($name);
    $description = $description ? sanitize($description) : null;
    $state = in_array($state, VALID_TODOS_STATES, true) ? $state : 'draft';

    query(
        "UPDATE todos SET todo_name = ?, todo_description = ?, state = ? WHERE id = ? AND user_id = ?",
        [$name, $description, $state, $id, $userId]
    );

    $updated = query("SELECT * FROM todos WHERE id = ?", [$id])->fetch();
    return ['success' => true, 'data' => $updated];
}

function changeTodoState(int $id, int $userId, string $state): array
{
    if (!in_array($state, VALID_TODOS_STATES, true)) {
        return ['success' => false, 'message' => 'Estado invalido.'];
    }

    query("UPDATE todos SET state = ? WHERE id = ? AND user_id = ?", [$state, $id, $userId]);
    $updated = query("SELECT * FROM todos WHERE id = ? AND user_id = ?", [$id, $userId])->fetch();

    if (!$updated) {
        return ['success' => false, 'message' => 'Tarefa nao encontrada.'];
    }

    return ['success' => true, 'data' => $updated];
}

function deleteTodo(int $id, int $userId): array
{
    return changeTodoState($id, $userId, 'trash');
}

function emptyTrash(int $userId): array
{
    $stmt = query("DELETE FROM todos WHERE user_id = ? AND state = 'trash'", [$userId]);
    return ['success' => true, 'deleted_count' => $stmt->rowCount()];
}
