<?php
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect(getBasePath() . 'pages/login.php');
    }
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return findUserById((int) $_SESSION['user_id']);
}
