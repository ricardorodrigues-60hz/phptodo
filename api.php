<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/todos.php';
require_once __DIR__ . '/pomodoro.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$input = is_array($json) ? array_merge($_POST, $json) : $_POST;

switch ($action) {
    case 'register':
        if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Metodo invalido.'], 405);
        $res = registerUser(
            $input['name'] ?? '',
            $input['lastname'] ?? '',
            $input['username'] ?? '',
            $input['email'] ?? '',
            $input['password'] ?? ''
        );
        jsonResponse($res, $res['success'] ? 201 : 400);
        break;

    case 'login':
        if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Metodo invalido.'], 405);
        $res = loginUser($input['login'] ?? ($input['username'] ?? ''), $input['password'] ?? '');
        jsonResponse($res, $res['success'] ? 200 : 401);
        break;

    case 'logout':
        logoutUser();
        jsonResponse(['success' => true, 'message' => 'Logout realizado.']);
        break;

    case 'me':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        jsonResponse(['success' => true, 'data' => $_SESSION['user']]);
        break;

    case 'list_todos':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $state = $_GET['state'] ?? null;
        $todos = getTodos(getCurrentUserId(), $state);
        jsonResponse(['success' => true, 'data' => $todos]);
        break;

    case 'create_todo':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $res = createTodo(
            getCurrentUserId(),
            $input['todo_name'] ?? ($input['name'] ?? ''),
            $input['todo_description'] ?? ($input['description'] ?? null),
            $input['state'] ?? 'draft'
        );
        jsonResponse($res, $res['success'] ? 201 : 400);
        break;

    case 'update_todo':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $res = updateTodo(
            (int)($input['id'] ?? 0),
            getCurrentUserId(),
            $input['todo_name'] ?? ($input['name'] ?? ''),
            $input['todo_description'] ?? ($input['description'] ?? null),
            $input['state'] ?? 'draft'
        );
        jsonResponse($res, $res['success'] ? 200 : 400);
        break;

    case 'change_state':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $res = changeTodoState((int)($input['id'] ?? 0), getCurrentUserId(), $input['state'] ?? '');
        jsonResponse($res, $res['success'] ? 200 : 400);
        break;

    case 'delete_todo':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $res = deleteTodo((int)($input['id'] ?? 0), getCurrentUserId());
        jsonResponse($res, $res['success'] ? 200 : 400);
        break;

    case 'empty_trash':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $res = emptyTrash(getCurrentUserId());
        jsonResponse($res);
        break;

    case 'start_pomodoro':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $todoId = !empty($input['todo_id']) ? (int)$input['todo_id'] : null;
        $res = startPomodoro(getCurrentUserId(), $todoId);
        jsonResponse($res, $res['success'] ? 201 : 400);
        break;

    case 'complete_pomodoro':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $res = completePomodoro((int)($input['session_id'] ?? 0), getCurrentUserId());
        jsonResponse($res, $res['success'] ? 200 : 400);
        break;

    case 'pomodoro_stats':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $res = getPomodoroStats(getCurrentUserId());
        jsonResponse($res);
        break;

    case 'pomodoro_history':
        if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Nao autenticado.'], 401);
        $res = getPomodoroHistory(getCurrentUserId());
        jsonResponse($res);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Acao nao encontrada.'], 404);
}
