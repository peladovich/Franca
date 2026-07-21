<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function is_admin(): bool
{
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

function require_login(string $redirectTo = 'login.php'): void
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/' . $redirectTo);
        exit;
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function login_user(array $userRow): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $userRow['id'],
        'name' => $userRow['name'],
        'email' => $userRow['email'],
        'role' => $userRow['role'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// CSRF helpers
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}
