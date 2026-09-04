<?php

declare(strict_types=1);

/**
 * Configuração central do MySQL.
 * No XAMPP, os valores padrão normalmente funcionam sem alterações.
 * Em produção, defina as variáveis DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS.
 */
return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'condominio_connect',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
