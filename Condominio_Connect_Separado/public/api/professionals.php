<?php

declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_method('GET');

$categoryId = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);
$sql =
    'SELECT p.id, u.nome, u.foto_caminho, p.nome_fantasia, p.descricao,
            p.anos_experiencia, p.taxa_visita, p.raio_atendimento_km,
            p.media_avaliacao, p.quantidade_avaliacoes,
            c.id AS categoria_id, c.nome AS categoria
     FROM profissionais p
     INNER JOIN usuarios u ON u.id = p.usuario_id
     INNER JOIN profissional_categoria pc ON pc.profissional_id = p.id
     INNER JOIN categorias_servico c ON c.id = pc.categoria_id
     WHERE p.status_validacao = "aprovado" AND u.status = "ativo" AND c.ativo = TRUE';
$params = [];
if ($categoryId) {
    $sql .= ' AND c.id = ?';
    $params[] = $categoryId;
}
$sql .= ' ORDER BY p.media_avaliacao DESC, p.quantidade_avaliacoes DESC, u.nome';

$statement = db()->prepare($sql);
$statement->execute($params);
respond(['ok' => true, 'professionals' => $statement->fetchAll()]);
