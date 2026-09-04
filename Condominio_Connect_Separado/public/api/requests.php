<?php

declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$user = require_user();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql =
        'SELECT s.id, CONCAT("#", s.id) AS codigo, s.titulo, s.descricao,
                s.local_atendimento, s.prioridade, s.status, s.criada_em,
                c.id AS categoria_id, c.nome AS categoria,
                cliente.nome AS cliente,
                profissional.id AS profissional_id, conversa.id AS conversa_id,
                profissional_usuario.nome AS profissional
         FROM solicitacoes_servico s
         INNER JOIN categorias_servico c ON c.id = s.categoria_id
         INNER JOIN usuarios cliente ON cliente.id = s.cliente_id
         LEFT JOIN agendamentos a ON a.id = (
             SELECT MAX(a2.id) FROM agendamentos a2 WHERE a2.solicitacao_id = s.id
         )
         LEFT JOIN conversas conversa ON conversa.id = (
             SELECT MAX(c2.id) FROM conversas c2 WHERE c2.solicitacao_id = s.id AND c2.ativa = TRUE
         )
         LEFT JOIN profissionais profissional ON profissional.id = COALESCE(a.profissional_id, conversa.profissional_id)
         LEFT JOIN usuarios profissional_usuario ON profissional_usuario.id = profissional.usuario_id';
    $params = [];
    if ($user['tipo'] === 'cliente') {
        $sql .= ' WHERE s.cliente_id = ?';
        $params[] = (int) $user['id'];
    } elseif ($user['tipo'] === 'profissional') {
        $sql .=
            ' WHERE (s.status = "publicada" AND EXISTS (
                   SELECT 1 FROM profissionais p_proprio
                   INNER JOIN profissional_categoria pc_proprio ON pc_proprio.profissional_id = p_proprio.id
                   WHERE p_proprio.usuario_id = ? AND pc_proprio.categoria_id = s.categoria_id
               )) OR profissional.usuario_id = ?';
        $params[] = (int) $user['id'];
        $params[] = (int) $user['id'];
    }
    $sql .= ' ORDER BY s.criada_em DESC';
    $statement = db()->prepare($sql);
    $statement->execute($params);
    respond(['ok' => true, 'requests' => $statement->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user['tipo'] !== 'cliente') {
        fail('Somente moradores ou síndicos podem abrir chamados.', 403);
    }
    $data = input();
    $categoryId = positive_int($data['categoria_id'] ?? null, 'categoria');
    $title = clean_text($data['titulo'] ?? '', 'título', 160);
    $description = clean_text($data['descricao'] ?? '', 'descrição', 3000);
    $place = clean_text($data['local_atendimento'] ?? '', 'local', 220);
    $priority = (string) ($data['prioridade'] ?? 'normal');
    if (!in_array($priority, ['baixa', 'normal', 'alta', 'urgente'], true)) {
        fail('Prioridade inválida.', 422);
    }
    if (empty($user['condominio_id'])) {
        fail('Complete o condomínio no perfil antes de abrir um chamado.', 422);
    }

    $statement = db()->prepare(
        'INSERT INTO solicitacoes_servico
         (cliente_id, condominio_id, categoria_id, titulo, descricao, local_atendimento, prioridade)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        (int) $user['id'],
        (int) $user['condominio_id'],
        $categoryId,
        $title,
        $description,
        $place,
        $priority,
    ]);
    $id = (int) db()->lastInsertId();

    $history = db()->prepare(
        'INSERT INTO historico_status_servico (solicitacao_id, alterado_por, status_novo, observacao)
         VALUES (?, ?, "publicada", "Chamado aberto pelo cliente")'
    );
    $history->execute([$id, (int) $user['id']]);
    respond(['ok' => true, 'message' => 'Chamado publicado.', 'id' => $id], 201);
}

fail('Método não permitido.', 405);
