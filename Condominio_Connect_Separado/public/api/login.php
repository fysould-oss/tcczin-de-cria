<?php

declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_method('POST');

$data = input();
$email = filter_var(trim((string) ($data['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$password = (string) ($data['password'] ?? '');

if (!$email || $password === '') {
    fail('Informe um e-mail e uma senha válidos.', 422);
}

$statement = db()->prepare(
    'SELECT id, nome, email, senha_hash, tipo, status, condominio_id, bloco, unidade
     FROM usuarios WHERE email = ? LIMIT 1'
);
$statement->execute([$email]);
$user = $statement->fetch();

if (!$user || !password_verify($password, $user['senha_hash'])) {
    fail('E-mail ou senha incorretos.', 401);
}
if ($user['status'] !== 'ativo') {
    fail('O cadastro ainda não está liberado para acesso.', 403);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];

$update = db()->prepare('UPDATE usuarios SET ultimo_acesso_em = NOW() WHERE id = ?');
$update->execute([(int) $user['id']]);
unset($user['senha_hash']);

respond(['ok' => true, 'user' => $user]);
