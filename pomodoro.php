<?php
require_once __DIR__ . '/functions.php';

function startPomodoro(int $userId, ?int $todoId = null, int $durationMinutes = 25): array
{
    if ($todoId) {
        $todo = query("SELECT id FROM todos WHERE id = ? AND user_id = ?", [$todoId, $userId])->fetch();
        if (!$todo) {
            return ['success' => false, 'message' => 'Tarefa nao encontrada.'];
        }
    }

    query(
        "INSERT INTO pomodoro_sessions (user_id, todo_id, duration_minutes, completed, started_at) VALUES (?, ?, ?, 0, NOW())",
        [$userId, $todoId, $durationMinutes]
    );

    $id = (int) $GLOBALS['pdo']->lastInsertId();
    $session = query("SELECT * FROM pomodoro_sessions WHERE id = ?", [$id])->fetch();

    return ['success' => true, 'data' => $session];
}

function completePomodoro(int $sessionId, int $userId): array
{
    $session = query("SELECT id FROM pomodoro_sessions WHERE id = ? AND user_id = ?", [$sessionId, $userId])->fetch();
    if (!$session) {
        return ['success' => false, 'message' => 'Sessao nao encontrada.'];
    }

    query(
        "UPDATE pomodoro_sessions SET completed = 1, ended_at = NOW() WHERE id = ? AND user_id = ?",
        [$sessionId, $userId]
    );

    $updated = query("SELECT * FROM pomodoro_sessions WHERE id = ?", [$sessionId])->fetch();
    return ['success' => true, 'data' => $updated];
}

function getPomodoroStats(int $userId): array
{
    $stats = query(
        "SELECT 
            COUNT(*) as total_sessions,
            SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_sessions,
            SUM(CASE WHEN completed = 1 THEN duration_minutes ELSE 0 END) as total_focus_minutes
         FROM pomodoro_sessions WHERE user_id = ?",
        [$userId]
    )->fetch();

    return [
        'success' => true,
        'data' => [
            'total_sessions' => (int)($stats['total_sessions'] ?? 0),
            'completed_sessions' => (int)($stats['completed_sessions'] ?? 0),
            'total_focus_minutes' => (int)($stats['total_focus_minutes'] ?? 0),
        ]
    ];
}

function getPomodoroHistory(int $userId, int $limit = 20): array
{
    $history = query(
        "SELECT ps.*, t.todo_name 
         FROM pomodoro_sessions ps 
         LEFT JOIN todos t ON ps.todo_id = t.id 
         WHERE ps.user_id = ? AND ps.completed = 1 
         ORDER BY ps.ended_at DESC LIMIT ?",
        [$userId, $limit]
    )->fetchAll();

    return ['success' => true, 'data' => $history];
}
