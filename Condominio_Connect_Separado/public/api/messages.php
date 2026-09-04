<?php

declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$user = require_user();

function conversation_for_user(int $conversationId, array $user): array
{
    $statement = db()->prepare(
        'SELECT c.id, c.solicitacao_id, c.profissional_id,
                s.cliente_id, s.titulo, cliente.nome AS cliente,
                profissional_usuario.id AS profissional_usuario_id,
                profissional_usuario.nome AS profissional
         FROM conversas c
         INNER JOIN solicitacoes_servico s ON s.id = c.solicitacao_id
         INNER JOIN usuarios cliente ON cliente.id = s.cliente_id
         INNER JOIN profissionais p ON p.id = c.profissional_id
         INNER JOIN usuarios profissional_usuario ON profissional_usuario.id = p.usuario_id
         WHERE c.id = ? AND c.ativa = TRUE LIMIT 1'
    );
    $statement->execute([$conversationId]);
    $conversation = $statement->fetch();
    if (!$conversation) {
        fail('Conversa não encontrada.', 404);
    }
    $allowed = $user['tipo'] === 'administrador'
        || (int) $conversation['cliente_id'] === (int) $user['id']
        || (int) $conversation['profissional_usuario_id'] === (int) $user['id'];
    if (!$allowed) {
        fail('Você não participa desta conversa.', 403);
    }
    return $conversation;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conversationId = positive_int($_GET['conversa_id'] ?? null, 'conversa');
    $conversation = conversation_for_user($conversationId, $user);
    $statement = db()->prepare(
        'SELECT m.id, m.conteudo, m.tipo, m.lida_em, m.criada_em,
                m.remetente_id, u.nome AS remetente
         FROM mensagens m
         INNER JOIN usuarios u ON u.id = m.remetente_id
         WHERE m.conversa_id = ? ORDER BY m.criada_em ASC, m.id ASC'
    );
    $statement->execute([$conversationId]);
    $mark = db()->prepare('UPDATE mensagens SET lida_em = NOW() WHERE conversa_id = ? AND remetente_id <> ? AND lida_em IS NULL');
    $mark->execute([$conversationId, (int) $user['id']]);
    respond(['ok' => true, 'conversation' => $conversation, 'messages' => $statement->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = input();
    $conversationId = positive_int($data['conversa_id'] ?? null, 'conversa');
    conversation_for_user($conversationId, $user);
    $message = clean_text($data['conteudo'] ?? '', 'mensagem', 2000);
    $statement = db()->prepare(
        'INSERT INTO mensagens (conversa_id, remetente_id, conteudo, tipo)
         VALUES (?, ?, ?, "texto")'
    );
    $statement->execute([$conversationId, (int) $user['id'], $message]);
    respond(['ok' => true, 'message' => 'Mensagem enviada.', 'id' => (int) db()->lastInsertId()], 201);
}

fail('Método não permitido.', 405);
