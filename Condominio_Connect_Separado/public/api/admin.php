<?php

declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$admin = require_user('administrador');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $summary = [
        'usuarios' => (int) db()->query('SELECT COUNT(*) FROM usuarios WHERE status = "ativo"')->fetchColumn(),
        'profissionais' => (int) db()->query('SELECT COUNT(*) FROM profissionais WHERE status_validacao = "aprovado"')->fetchColumn(),
        'chamados' => (int) db()->query('SELECT COUNT(*) FROM solicitacoes_servico WHERE status NOT IN ("concluida", "cancelada")')->fetchColumn(),
        'validacoes' => (int) db()->query('SELECT COUNT(*) FROM profissionais WHERE status_validacao = "em_analise"')->fetchColumn(),
    ];

    $users = db()->query(
        'SELECT u.id, u.nome, u.email, u.tipo, u.status, c.nome AS condominio
         FROM usuarios u LEFT JOIN condominios c ON c.id = u.condominio_id
         ORDER BY u.criado_em DESC LIMIT 100'
    )->fetchAll();
    $professionals = db()->query(
        'SELECT p.id, u.nome, u.email, p.cnpj, p.razao_social, p.anos_experiencia,
                p.status_validacao, p.cnpj_situacao,
                GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ", ") AS categorias
         FROM profissionais p
         INNER JOIN usuarios u ON u.id = p.usuario_id
         LEFT JOIN profissional_categoria pc ON pc.profissional_id = p.id
         LEFT JOIN categorias_servico c ON c.id = pc.categoria_id
         GROUP BY p.id, u.nome, u.email, p.cnpj, p.razao_social,
                  p.anos_experiencia, p.status_validacao, p.cnpj_situacao
         ORDER BY p.criado_em DESC LIMIT 100'
    )->fetchAll();
    $requests = db()->query(
        'SELECT s.id, s.titulo, s.status, s.prioridade, s.criada_em,
                c.nome AS categoria, u.nome AS cliente, co.nome AS condominio
         FROM solicitacoes_servico s
         INNER JOIN categorias_servico c ON c.id = s.categoria_id
         INNER JOIN usuarios u ON u.id = s.cliente_id
         INNER JOIN condominios co ON co.id = s.condominio_id
         ORDER BY s.criada_em DESC LIMIT 100'
    )->fetchAll();
    $reviews = db()->query(
        'SELECT a.id, a.nota, a.comentario, a.status_moderacao, a.criada_em,
                cliente.nome AS cliente, profissional_usuario.nome AS profissional
         FROM avaliacoes a
         INNER JOIN usuarios cliente ON cliente.id = a.cliente_id
         INNER JOIN profissionais p ON p.id = a.profissional_id
         INNER JOIN usuarios profissional_usuario ON profissional_usuario.id = p.usuario_id
         ORDER BY a.criada_em DESC LIMIT 100'
    )->fetchAll();
    $categories = db()->query(
        'SELECT id, nome, descricao, ativo FROM categorias_servico ORDER BY nome'
    )->fetchAll();

    respond([
        'ok' => true,
        'summary' => $summary,
        'users' => $users,
        'professionals' => $professionals,
        'requests' => $requests,
        'reviews' => $reviews,
        'categories' => $categories,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $data = input();
    $professionalId = positive_int($data['profissional_id'] ?? null, 'profissional');
    $decision = (string) ($data['decisao'] ?? '');
    if (!in_array($decision, ['aprovar', 'recusar'], true)) {
        fail('Decisão inválida.', 422);
    }
    if ($decision === 'aprovar' && (empty($data['cnpj_conferido']) || empty($data['documentos_conferidos']))) {
        fail('Confira o CNPJ e os documentos antes da aprovação.', 422);
    }

    if ($decision === 'aprovar') {
        $evidence = db()->prepare(
            'SELECT p.cnpj, COUNT(DISTINCT d.tipo) AS documentos
             FROM profissionais p
             LEFT JOIN documentos_profissional d ON d.profissional_id = p.id
             WHERE p.id = ? AND p.status_validacao = "em_analise"
             GROUP BY p.id, p.cnpj'
        );
        $evidence->execute([$professionalId]);
        $record = $evidence->fetch();
        if (!$record || strlen((string) $record['cnpj']) !== 14 || (int) $record['documentos'] < 4) {
            fail('O cadastro precisa ter CNPJ e os quatro documentos obrigatórios antes da aprovação.', 422);
        }
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();
        $status = $decision === 'aprovar' ? 'aprovado' : 'recusado';
        $userStatus = $decision === 'aprovar' ? 'ativo' : 'bloqueado';
        $reason = trim((string) ($data['motivo'] ?? '')) ?: null;
        $update = $pdo->prepare(
            'UPDATE profissionais p
             INNER JOIN usuarios u ON u.id = p.usuario_id
             SET p.status_validacao = ?, p.cnpj_situacao = ?, p.validado_por = ?,
                 p.validado_em = NOW(), p.motivo_recusa = ?, u.status = ?
             WHERE p.id = ?'
        );
        $update->execute([
            $status,
            $decision === 'aprovar' ? 'ativo' : 'nao_consultado',
            (int) $admin['id'],
            $reason,
            $userStatus,
            $professionalId,
        ]);
        if ($decision === 'aprovar') {
            $verify = $pdo->prepare(
                'UPDATE verificacoes_profissional SET status = "aprovado", verificado_por = ?, verificado_em = NOW()
                 WHERE profissional_id = ?'
            );
            $verify->execute([(int) $admin['id'], $professionalId]);
            $documents = $pdo->prepare(
                'UPDATE documentos_profissional SET status = "aprovado", analisado_por = ?, analisado_em = NOW()
                 WHERE profissional_id = ?'
            );
            $documents->execute([(int) $admin['id'], $professionalId]);
        }
        audit((int) $admin['id'], $decision, 'profissional', $professionalId, ['motivo' => $reason]);
        $pdo->commit();
        respond(['ok' => true, 'message' => $decision === 'aprovar' ? 'Profissional aprovado.' : 'Profissional recusado.']);
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($exception->getMessage());
        fail('Não foi possível registrar a decisão.', 500);
    }
}

fail('Método não permitido.', 405);
