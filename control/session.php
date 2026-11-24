<?php
declare(strict_types=1);

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (headers_sent($file, $line)) {
        throw new RuntimeException("Impossible de démarrer la session : les en-têtes ont déjà été envoyés dans {$file}:{$line}.");
    }

    if (ini_set('session.use_strict_mode', '1') === false
        || ini_set('session.use_only_cookies', '1') === false) {
        throw new RuntimeException('Impossible d’activer les paramètres de sécurité de session PHP.');
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

    if (!session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ])) {
        throw new RuntimeException('Impossible de configurer le cookie de session.');
    }

    if (!session_start()) {
        throw new RuntimeException('Impossible de démarrer la session PHP.');
    }
}

function set_authenticated_user(int $userId, string $role): void
{
    if ($userId < 1 || trim($role) === '') {
        throw new InvalidArgumentException('Un identifiant utilisateur et un rôle valides sont requis.');
    }

    start_app_session();

    if (!session_regenerate_id(true)) {
        throw new RuntimeException('Impossible de renouveler l’identifiant de session.');
    }

    $_SESSION['auth_user'] = [
        'id' => $userId,
        'role' => trim($role),
    ];
}

/**
 * @return array{id: int, role: string}|null
 */
function current_authenticated_user(): ?array
{
    start_app_session();

    if (!array_key_exists('auth_user', $_SESSION)) {
        return null;
    }

    $user = $_SESSION['auth_user'];
    if (!is_array($user)
        || !isset($user['id'], $user['role'])
        || !is_int($user['id'])
        || $user['id'] < 1
        || !is_string($user['role'])
        || trim($user['role']) === '') {
        throw new UnexpectedValueException('Les données utilisateur de la session sont invalides.');
    }

    return [
        'id' => $user['id'],
        'role' => $user['role'],
    ];
}

function is_authenticated(): bool
{
    return current_authenticated_user() !== null;
}

function app_csrf_token(): string
{
    start_app_session();

    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function app_csrf_token_is_valid(mixed $token): bool
{
    start_app_session();
    $expectedToken = $_SESSION['csrf_token'] ?? null;

    return is_string($token)
        && is_string($expectedToken)
        && hash_equals($expectedToken, $token);
}

function logout_user(): void
{
    start_app_session();
    $_SESSION = [];

    $cookie = session_get_cookie_params();
    if (!setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'],
    ])) {
        throw new RuntimeException('Impossible de supprimer le cookie de session.');
    }

    if (!session_destroy()) {
        throw new RuntimeException('Impossible de fermer la session PHP.');
    }
}
