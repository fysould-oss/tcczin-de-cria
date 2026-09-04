<?php

declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_method('GET');

$user = current_user();
respond(['ok' => true, 'authenticated' => $user !== null, 'user' => $user]);
