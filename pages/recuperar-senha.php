<?php
$pageTitle = 'Recuperar senha';
$metaDescription = 'Recupere o acesso à sua conta do ConectaPrédio.';
$bodyClass = 'auth-page';
require __DIR__ . '/../includes/header.php';
?>

<main class="auth-frame">
    <div class="container">
        <div class="auth-card">
            <p class="eyebrow">Recuperação</p>
            <h1>Recuperar senha</h1>
            <p>Informe seu e-mail para receber instruções de redefinição de senha.</p>

            <form action="#" method="post">
                <label>
                    <span>E-mail cadastrado</span>
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </label>
                <button type="submit" class="button button-primary" style="width:100%;">Enviar instruções</button>
            </form>

            <div class="auth-footer">
                <a href="login.php">Voltar para o login</a>
                <a href="cadastro.php">Criar conta</a>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
