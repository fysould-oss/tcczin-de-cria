<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = 'Novo chamado';
$metaDescription = 'Abra um novo chamado de manutenção no CondoConnect.';
$bodyClass = 'auth-page';
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = trim((string) ($_POST['categoria'] ?? ''));
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));
    $local = trim((string) ($_POST['local_problema'] ?? ''));
    $prioridade = trim((string) ($_POST['prioridade'] ?? ''));
    $dataServico = trim((string) ($_POST['data_servico'] ?? ''));

    if ($categoria === '' || $titulo === '' || $descricao === '' || $local === '' || $prioridade === '' || $dataServico === '') {
        $errorMessage = 'Preencha todos os campos do formulário.';
    } else {
        $protocolo = 'CP-' . date('Ymd') . '-' . str_pad((string) time(), 4, '0', STR_PAD_LEFT);
        $chamadoId = createChamado([
            ':usuario_id' => (int) $_SESSION['user_id'],
            ':categoria' => $categoria,
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':local_problema' => $local,
            ':prioridade' => $prioridade,
            ':data_servico' => $dataServico,
            ':protocolo' => $protocolo,
            ':status' => 'aberto',
        ]);

        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!empty($_FILES['anexo']['name'])) {
            $file = $_FILES['anexo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errorMessage = 'Houve um problema ao enviar o arquivo.';
            } elseif ($file['size'] > 2097152) {
                $errorMessage = 'O arquivo deve ter no máximo 2MB.';
            } elseif (!in_array($file['type'], $allowedTypes, true)) {
                $errorMessage = 'Apenas imagens JPG, PNG, WEBP ou GIF são permitidas.';
            } else {
                $filename = 'anexo-' . time() . '-' . basename($file['name']);
                $targetPath = $uploadDir . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    addChamadoAttachment($chamadoId, $filename);
                } else {
                    $errorMessage = 'Não foi possível salvar o arquivo enviado.';
                }
            }
        }

        if ($errorMessage === '') {
            createNotification((int) $_SESSION['user_id'], 'Chamado criado', 'Seu chamado foi registrado com sucesso.');
            redirect(getBasePath() . 'pages/dashboard.php');
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<main class="auth-frame">
    <div class="container">
        <div class="auth-card">
            <p class="eyebrow">Solicitação</p>
            <h1>Nova solicitação de manutenção</h1>
            <p>Descreva o problema para que o condomínio possa organizar o atendimento.</p>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-error"><?= sanitize($errorMessage) ?></div>
            <?php endif; ?>
            <?php if ($successMessage !== ''): ?>
                <div class="alert alert-success"><?= sanitize($successMessage) ?></div>
            <?php endif; ?>

            <form action="<?= $basePath ?>pages/novo-chamado.php" method="post" enctype="multipart/form-data">
                <label>
                    <span>Categoria</span>
                    <select name="categoria" required>
                        <option value="Elétrica">Elétrica</option>
                        <option value="Hidráulica">Hidráulica</option>
                        <option value="Pintura">Pintura</option>
                        <option value="Limpeza">Limpeza</option>
                        <option value="Elevadores">Elevadores</option>
                        <option value="Jardinagem">Jardinagem</option>
                        <option value="Segurança">Segurança</option>
                        <option value="Reparos gerais">Reparos gerais</option>
                    </select>
                </label>
                <label>
                    <span>Título do problema</span>
                    <input type="text" name="titulo" placeholder="Ex.: Queda de energia no 12º andar" required>
                </label>
                <label>
                    <span>Descrição</span>
                    <textarea name="descricao" rows="5" placeholder="Descreva detalhadamente o problema" required></textarea>
                </label>
                <label>
                    <span>Local do problema</span>
                    <input type="text" name="local_problema" placeholder="Bloco A, garagem, apartamento 1202" required>
                </label>
                <label>
                    <span>Prioridade</span>
                    <select name="prioridade" required>
                        <option value="Baixa">Baixa</option>
                        <option value="Média">Média</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgente">Urgente</option>
                    </select>
                </label>
                <label>
                    <span>Data desejada</span>
                    <input type="date" name="data_servico" required>
                </label>
                <label>
                    <span>Anexo (imagem)</span>
                    <input type="file" name="anexo" accept="image/*">
                </label>
                <button type="submit" class="button button-primary" style="width:100%;">Salvar chamado</button>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
