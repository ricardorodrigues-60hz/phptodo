<?php
require_once __DIR__ . '/functions.php';

function registerUser(string $name, string $lastname, string $username, string $email, string $password): array
{
    $name = sanitize($name);
    $lastname = sanitize($lastname);
    $username = sanitize($username);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if (!$name || !$lastname || !$username || !$email || strlen($password) < 6) {
        return ['success' => false, 'message' => 'Dados de cadastro invalidos.'];
    }

    $existingEmail = query("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])->fetch();
    if ($existingEmail) {
        return ['success' => false, 'message' => 'Email ja cadastrado.'];
    }

    $existingUser = query("SELECT id FROM users WHERE username = ? LIMIT 1", [$username])->fetch();
    if ($existingUser) {
        return ['success' => false, 'message' => 'Nome de usuario ja cadastrado.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    query(
        "INSERT INTO users (name, lastname, username, email, password) VALUES (?, ?, ?, ?, ?)",
        [$name, $lastname, $username, $email, $hashedPassword]
    );

    $userId = (int) $GLOBALS['pdo']->lastInsertId();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user'] = [
        'id' => $userId,
        'name' => $name,
        'lastname' => $lastname,
        'username' => $username,
        'email' => $email
    ];

    return ['success' => true, 'data' => $_SESSION['user']];
}

function loginUser(string $login, string $password): array
{
    $login = sanitize($login);
    $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

    $sql = $isEmail
        ? "SELECT * FROM users WHERE email = ? LIMIT 1"
        : "SELECT * FROM users WHERE username = ? LIMIT 1";

    $user = query($sql, [$login])->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Credenciais invalidas.'];
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'lastname' => $user['lastname'],
        'username' => $user['username'],
        'email' => $user['email']
    ];

    return ['success' => true, 'data' => $_SESSION['user']];
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
