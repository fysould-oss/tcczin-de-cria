<?php

declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_method('GET');

$statement = db()->query(
    'SELECT c.id, c.nome, c.descricao, c.icone,
            COUNT(DISTINCT CASE WHEN p.status_validacao = "aprovado" THEN p.id END) AS profissionais
     FROM categorias_servico c
     LEFT JOIN profissional_categoria pc ON pc.categoria_id = c.id
     LEFT JOIN profissionais p ON p.id = pc.profissional_id
     WHERE c.ativo = TRUE
     GROUP BY c.id, c.nome, c.descricao, c.icone
     ORDER BY c.nome'
);

respond(['ok' => true, 'categories' => $statement->fetchAll()]);
