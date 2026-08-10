<?php
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Cadastro';
$metaDescription = 'Crie sua conta no ConectaPrédio.';
$bodyClass = 'auth-page';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $cpf = trim((string) ($_POST['cpf'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $senha = trim((string) ($_POST['senha'] ?? ''));
    $confirmarSenha = trim((string) ($_POST['confirmar_senha'] ?? ''));
    $tipoUsuario = trim((string) ($_POST['tipo_usuario'] ?? 'morador'));
    $condominio = trim((string) ($_POST['condominio'] ?? ''));

    if ($nome === '' || $cpf === '' || $email === '' || $telefone === '' || $senha === '' || $confirmarSenha === '' || $condominio === '') {
        $errorMessage = 'Preencha todos os campos obrigatórios.';
    } elseif ($senha !== $confirmarSenha) {
        $errorMessage = 'As senhas não conferem.';
    } elseif (findUserByEmail($email) !== null) {
        $errorMessage = 'Este e-mail já está cadastrado.';
    } else {
        $userId = createUser([
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone,
            'senha' => $senha,
            'tipo_usuario' => $tipoUsuario,
            'condominio' => $condominio,
            'unidade' => 'Não informado',
        ]);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $tipoUsuario;
        redirect(getBasePath() . 'pages/dashboard.php');
    }
}

require __DIR__ . '/../includes/header.php';
?>

<main class="auth-frame">
    <div class="container">
        <div class="auth-card">
            <p class="eyebrow">Cadastro</p>
            <h1>Criar conta</h1>
            <p>Cadastre-se como morador, prestador ou síndico para começar a usar a plataforma.</p>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-error"><?= sanitize($errorMessage) ?></div>
            <?php endif; ?>

            <form action="<?= $basePath ?>pages/cadastro.php" method="post">
                <label>
                    <span>Nome completo</span>
                    <input type="text" name="nome" placeholder="Seu nome" required>
                </label>
                <label>
                    <span>CPF</span>
                    <input type="text" name="cpf" placeholder="000.000.000-00" required>
                </label>
                <label>
                    <span>E-mail</span>
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </label>
                <label>
                    <span>Telefone</span>
                    <input type="tel" name="telefone" placeholder="(11) 99999-9999" required>
                </label>
                <label>
                    <span>Senha</span>
                    <input type="password" name="senha" placeholder="Crie uma senha" required>
                </label>
                <label>
                    <span>Confirme a senha</span>
                    <input type="password" name="confirmar_senha" placeholder="Repita a senha" required>
                </label>
                <label>
                    <span>Tipo de usuário</span>
                    <select name="tipo_usuario" required>
                        <option value="morador">Morador</option>
                        <option value="prestador">Prestador</option>
                        <option value="sindico">Síndico</option>
                    </select>
                </label>
                <label>
                    <span>Condomínio</span>
                    <input type="text" name="condominio" placeholder="Nome do condomínio" required>
                </label>
                <button type="submit" class="button button-primary" style="width:100%;">Criar conta</button>
            </form>

            <div class="auth-footer">
                <span>Já possui conta?</span>
                <a href="login.php">Entrar</a>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
