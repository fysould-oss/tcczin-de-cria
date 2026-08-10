<?php
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Login';
$metaDescription = 'Acesse sua conta do ConectaPrédio.';
$bodyClass = 'auth-page';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = trim((string) ($_POST['senha'] ?? ''));

    if ($email === '' || $senha === '') {
        $errorMessage = 'Informe seu e-mail e senha para entrar.';
    } else {
        $user = authenticateUser($email, $senha);
        if ($user !== null) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_role'] = (string) ($user['tipo_usuario'] ?? 'morador');
            redirect(getBasePath() . 'pages/dashboard.php');
        }

        $errorMessage = 'E-mail ou senha inválidos. Tente novamente.';
    }
}

require __DIR__ . '/../includes/header.php';
?>

<main class="auth-frame">
    <div class="container">
        <div class="auth-card">
            <p class="eyebrow">Acesso</p>
            <h1>Entrar no ConectaPrédio</h1>
            <p>Use seus dados para acessar o painel e acompanhar seus chamados.</p>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-error"><?= sanitize($errorMessage) ?></div>
            <?php endif; ?>

            <form action="<?= $basePath ?>pages/login.php" method="post">
                <label>
                    <span>E-mail</span>
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </label>
                <label>
                    <span>Senha</span>
                    <div style="position: relative;">
                        <input type="password" name="senha" data-password placeholder="Sua senha" required>
                        <button type="button" data-toggle-password style="position:absolute; right: 12px; top:50%; transform:translateY(-50%); border:0; background:transparent; color:var(--blue); cursor:pointer; font-weight:700;">Mostrar</button>
                    </div>
                </label>
                <button type="submit" class="button button-primary" style="width:100%; margin-top: 12px;">Entrar</button>
            </form>

            <div class="auth-footer">
                <a href="recuperar-senha.php">Esqueci minha senha</a>
                <a href="cadastro.php">Criar conta</a>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
