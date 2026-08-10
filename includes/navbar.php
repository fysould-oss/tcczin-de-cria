<?php
require_once __DIR__ . '/auth.php';

$basePath = getBasePath();
$user = currentUser();
$loggedIn = !empty($user);
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= $basePath ?>index.php" aria-label="ConectaPrédio início">
            <span class="brand-mark">CP</span>
            <div>
                <strong>ConectaPrédio</strong>
                <span>Gestão de serviços</span>
            </div>
        </a>

        <button class="menu-toggle" type="button" aria-label="Abrir menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="main-nav" aria-label="Navegação principal">
            <a href="<?= $basePath ?>index.php">Início</a>
            <a href="<?= $basePath ?>pages/sobre.php">Sobre</a>
            <a href="<?= $basePath ?>pages/servicos.php">Serviços</a>
            <a href="<?= $basePath ?>pages/faq.php">FAQ</a>
            <a href="<?= $basePath ?>pages/contato.php">Contato</a>
            <?php if ($loggedIn): ?>
                <a href="<?= $basePath ?>pages/dashboard.php">Dashboard</a>
                <a href="<?= $basePath ?>logout.php">Sair</a>
            <?php else: ?>
                <a href="<?= $basePath ?>pages/login.php">Entrar</a>
                <a href="<?= $basePath ?>pages/cadastro.php" class="button button-primary">Cadastrar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
