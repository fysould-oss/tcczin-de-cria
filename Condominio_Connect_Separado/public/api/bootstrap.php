<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fail(string $message, int $status = 400, array $details = []): never
{
    respond(['ok' => false, 'message' => $message, 'details' => $details], $status);
}

function require_method(string ...$allowed): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        fail('Método não permitido.', 405);
    }
}

function input(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($contentType, 'application/json')) {
        $decoded = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($decoded)) {
            fail('O corpo JSON é inválido.', 422);
        }
        return $decoded;
    }
    return $_POST;
}

function db(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = require dirname(__DIR__, 2) . '/config/database.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );

    try {
        $connection = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        fail('Não foi possível conectar ao banco de dados.', 500);
    }

    return $connection;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $statement = db()->prepare(
        'SELECT id, nome, email, tipo, status, condominio_id, bloco, unidade
         FROM usuarios WHERE id = ? LIMIT 1'
    );
    $statement->execute([(int) $_SESSION['user_id']]);
    $user = $statement->fetch();
    return $user ?: null;
}

function require_user(?string $role = null): array
{
    $user = current_user();
    if (!$user) {
        fail('Faça login para continuar.', 401);
    }
    if ($user['status'] !== 'ativo') {
        fail('Esta conta não está ativa.', 403);
    }
    if ($role !== null && $user['tipo'] !== $role) {
        fail('Você não possui permissão para esta ação.', 403);
    }
    return $user;
}

function positive_int(mixed $value, string $field): int
{
    $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($number === false) {
        fail("O campo {$field} é inválido.", 422);
    }
    return (int) $number;
}

function clean_text(mixed $value, string $field, int $max = 1000): string
{
    $text = trim((string) $value);
    if ($text === '' || mb_strlen($text) > $max) {
        fail("O campo {$field} é obrigatório e deve ter até {$max} caracteres.", 422);
    }
    return $text;
}

function audit(int $adminId, string $action, string $entity, ?int $entityId, array $details = []): void
{
    $statement = db()->prepare(
        'INSERT INTO logs_administrativos
         (administrador_id, acao, entidade, entidade_id, detalhes, ip)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        $adminId,
        $action,
        $entity,
        $entityId,
        json_encode($details, JSON_UNESCAPED_UNICODE),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}
