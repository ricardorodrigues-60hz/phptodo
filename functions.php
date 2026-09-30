<?php
require_once __DIR__ . '/config.php';

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function getCurrentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function redirect(string $page): void
{
    header("Location: $page");
    exit;
}

function jsonResponse(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}



function sanitize(mixed $input): mixed
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    if (is_string($input)) {
        return htmlspecialchars(trim(strip_tags($input)), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
