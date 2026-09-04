<?php

declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_method('POST');

$data = input();
$type = (string) ($data['tipo'] ?? 'cliente');
if (!in_array($type, ['cliente', 'profissional'], true)) {
    fail('Tipo de cadastro inválido.', 422);
}

$name = clean_text($data['nome'] ?? '', 'nome', 150);
$email = filter_var(trim((string) ($data['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$phone = preg_replace('/\D+/', '', (string) ($data['telefone'] ?? ''));
$password = (string) ($data['senha'] ?? '');
if (!$email || strlen($password) < 8 || strlen($phone) < 10) {
    fail('Informe e-mail, telefone e uma senha com pelo menos 8 caracteres.', 422);
}

$pdo = db();
$exists = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
$exists->execute([$email]);
if ($exists->fetch()) {
    fail('Já existe uma conta com este e-mail.', 409);
}

$uploadedDocuments = [];
if ($type === 'profissional') {
    $requiredFiles = [
        'documento_cnpj' => 'ccmei',
        'identificacao' => 'identificacao',
        'certificado' => 'certificado',
        'portfolio' => 'portfolio',
    ];
    foreach ($requiredFiles as $field => $documentType) {
        $file = $_FILES[$field] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            fail('Envie todos os documentos obrigatórios do profissional.', 422);
        }
        if ((int) $file['size'] > 5 * 1024 * 1024) {
            fail('Cada documento deve ter no máximo 5 MB.', 422);
        }
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            fail('Os documentos devem estar em PDF, JPG ou PNG.', 422);
        }
        $uploadedDocuments[] = [
            'type' => $documentType,
            'original_name' => basename((string) $file['name']),
            'temporary_path' => (string) $file['tmp_name'],
            'size' => (int) $file['size'],
            'extension' => $extension,
        ];
    }
}

$createdFiles = [];
try {
    $pdo->beginTransaction();
    $insertUser = $pdo->prepare(
        'INSERT INTO usuarios (nome, email, telefone, senha_hash, tipo, status)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $status = $type === 'profissional' ? 'pendente' : 'ativo';
    $insertUser->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $type, $status]);
    $userId = (int) $pdo->lastInsertId();

    if ($type === 'profissional') {
        $cnpj = preg_replace('/\D+/', '', (string) ($data['cnpj'] ?? ''));
        $legalName = clean_text($data['razao_social'] ?? '', 'razão social', 180);
        $description = clean_text($data['descricao'] ?? '', 'descrição', 1000);
        $experience = max(0, (int) ($data['anos_experiencia'] ?? 0));
        $region = clean_text($data['regiao'] ?? '', 'região', 180);
        $categoryId = positive_int($data['categoria_id'] ?? null, 'categoria');
        if (strlen($cnpj) !== 14) {
            fail('O CNPJ deve conter 14 números.', 422);
        }

        $insertProfessional = $pdo->prepare(
            'INSERT INTO profissionais
             (usuario_id, cnpj, razao_social, nome_fantasia, descricao, anos_experiencia, status_validacao)
             VALUES (?, ?, ?, ?, ?, ?, "em_analise")'
        );
        $insertProfessional->execute([$userId, $cnpj, $legalName, $name, $description . "\nRegião: " . $region, $experience]);
        $professionalId = (int) $pdo->lastInsertId();

        $uploadDirectory = dirname(__DIR__, 2) . '/storage/uploads/profissionais/' . $professionalId;
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0750, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Não foi possível preparar a pasta de documentos.');
        }
        $insertDocument = $pdo->prepare(
            'INSERT INTO documentos_profissional
             (profissional_id, tipo, nome_arquivo, caminho_arquivo, mime_type, tamanho_bytes, status)
             VALUES (?, ?, ?, ?, ?, ?, "pendente")'
        );
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        foreach ($uploadedDocuments as $document) {
            $fileName = $document['type'] . '-' . bin2hex(random_bytes(8)) . '.' . $document['extension'];
            $absolutePath = $uploadDirectory . '/' . $fileName;
            if (!move_uploaded_file($document['temporary_path'], $absolutePath)) {
                throw new RuntimeException('Não foi possível salvar um dos documentos.');
            }
            $createdFiles[] = $absolutePath;
            $relativePath = 'storage/uploads/profissionais/' . $professionalId . '/' . $fileName;
            $insertDocument->execute([
                $professionalId,
                $document['type'],
                $document['original_name'],
                $relativePath,
                $finfo->file($absolutePath) ?: 'application/octet-stream',
                $document['size'],
            ]);
        }

        $linkCategory = $pdo->prepare(
            'INSERT INTO profissional_categoria (profissional_id, categoria_id, principal) VALUES (?, ?, TRUE)'
        );
        $linkCategory->execute([$professionalId, $categoryId]);

        $verification = $pdo->prepare(
            'INSERT INTO verificacoes_profissional (profissional_id, tipo, status)
             VALUES (?, "cnpj", "pendente"), (?, "identidade", "pendente"),
                    (?, "certificado", "pendente"), (?, "portfolio", "pendente")'
        );
        $verification->execute([$professionalId, $professionalId, $professionalId, $professionalId]);
    }

    $pdo->commit();
    respond([
        'ok' => true,
        'message' => $type === 'profissional'
            ? 'Cadastro enviado para validação da SGB Tech.'
            : 'Cadastro concluído. Você já pode entrar.',
    ], 201);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    foreach ($createdFiles as $filePath) {
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
    error_log($exception->getMessage());
    fail('Não foi possível concluir o cadastro.', 500);
}
