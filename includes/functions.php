<?php
function getBasePath(): string
{
    $script = ltrim($_SERVER['SCRIPT_NAME'] ?? '/', '/');
    $segments = array_values(array_filter(explode('/', $script), 'strlen'));
    $depth = max(0, count($segments) - 1);
    return str_repeat('../', $depth);
}

function sanitize($value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function getDatabasePath(): string
{
    $dir = __DIR__ . '/../storage';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $dir . '/conectapredio.sqlite';
}

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dbPath = getDatabasePath();
        $dsn = 'sqlite:' . $dbPath;
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        initializeDatabase($pdo);
    }

    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            cpf TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            telefone TEXT NOT NULL,
            senha TEXT NOT NULL,
            tipo_usuario TEXT NOT NULL DEFAULT 'morador',
            condominio TEXT NOT NULL,
            unidade TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'ativo',
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chamados (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            categoria TEXT NOT NULL,
            titulo TEXT NOT NULL,
            descricao TEXT NOT NULL,
            local_problema TEXT NOT NULL,
            prioridade TEXT NOT NULL,
            data_servico TEXT NOT NULL,
            protocolo TEXT NOT NULL UNIQUE,
            status TEXT NOT NULL DEFAULT 'aberto',
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS anexos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            chamado_id INTEGER NOT NULL,
            arquivo TEXT NOT NULL,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mensagens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            chamado_id INTEGER NOT NULL,
            remetente_id INTEGER NOT NULL,
            mensagem TEXT NOT NULL,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
            FOREIGN KEY (remetente_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notificacoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            titulo TEXT NOT NULL,
            mensagem TEXT NOT NULL,
            lida INTEGER NOT NULL DEFAULT 0,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS avaliacoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            chamado_id INTEGER NOT NULL,
            morador_id INTEGER NOT NULL,
            prestador_id INTEGER NOT NULL,
            nota INTEGER NOT NULL,
            comentario TEXT,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
            FOREIGN KEY (morador_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (prestador_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
}

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function passwordMatches(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function createUser(array $data): int
{
    $pdo = getPDO();
    $senha = $data['senha'] ?? '';
    if ($senha !== '' && strpos($senha, '$2') !== 0) {
        $senha = hashPassword($senha);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO users (nome, cpf, email, telefone, senha, tipo_usuario, condominio, unidade) VALUES (:nome, :cpf, :email, :telefone, :senha, :tipo_usuario, :condominio, :unidade)'
    );
    $stmt->execute([
        ':nome' => $data['nome'] ?? '',
        ':cpf' => $data['cpf'] ?? '',
        ':email' => $data['email'] ?? '',
        ':telefone' => $data['telefone'] ?? '',
        ':senha' => $senha,
        ':tipo_usuario' => $data['tipo_usuario'] ?? 'morador',
        ':condominio' => $data['condominio'] ?? '',
        ':unidade' => $data['unidade'] ?? '',
    ]);
    return (int) $pdo->lastInsertId();
}

function authenticateUser(string $email, string $password): ?array
{
    $user = findUserByEmail($email);
    if ($user === null) {
        return null;
    }

    if (!passwordMatches($password, (string) $user['senha'])) {
        return null;
    }

    return $user;
}

function findUserByEmail(string $email): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function findUserById(int $id): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function updateUserProfile(int $userId, array $data): bool
{
    $pdo = getPDO();
    $fields = [];
    $params = [':id' => $userId];

    if (!empty($data['nome'])) {
        $fields[] = 'nome = :nome';
        $params[':nome'] = $data['nome'];
    }
    if (!empty($data['telefone'])) {
        $fields[] = 'telefone = :telefone';
        $params[':telefone'] = $data['telefone'];
    }
    if (!empty($data['email'])) {
        $fields[] = 'email = :email';
        $params[':email'] = $data['email'];
    }
    if (!empty($data['senha'])) {
        $fields[] = 'senha = :senha';
        $params[':senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
    }

    if ($fields === []) {
        return false;
    }

    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function createChamado(array $data): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO chamados (usuario_id, categoria, titulo, descricao, local_problema, prioridade, data_servico, protocolo, status) VALUES (:usuario_id, :categoria, :titulo, :descricao, :local_problema, :prioridade, :data_servico, :protocolo, :status)'
    );
    $stmt->execute($data);
    return (int) $pdo->lastInsertId();
}

function addChamadoAttachment(int $chamadoId, string $arquivo): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO anexos (chamado_id, arquivo) VALUES (:chamado_id, :arquivo)');
    $stmt->execute([':chamado_id' => $chamadoId, ':arquivo' => $arquivo]);
}

function getChamadosForUser(int $userId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM chamados WHERE usuario_id = :usuario_id ORDER BY criado_em DESC');
    $stmt->execute([':usuario_id' => $userId]);
    return $stmt->fetchAll();
}

function getChamadoById(int $id): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM chamados WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $chamado = $stmt->fetch();
    return $chamado ?: null;
}

function saveMessage(int $chamadoId, int $remetenteId, string $mensagem): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO mensagens (chamado_id, remetente_id, mensagem) VALUES (:chamado_id, :remetente_id, :mensagem)');
    $stmt->execute([':chamado_id' => $chamadoId, ':remetente_id' => $remetenteId, ':mensagem' => $mensagem]);
}

function listMessagesForChamado(int $chamadoId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT m.*, u.nome AS remetente FROM mensagens m JOIN users u ON u.id = m.remetente_id WHERE m.chamado_id = :chamado_id ORDER BY m.criado_em ASC');
    $stmt->execute([':chamado_id' => $chamadoId]);
    return $stmt->fetchAll();
}

function createNotification(int $userId, string $titulo, string $mensagem): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO notificacoes (usuario_id, titulo, mensagem) VALUES (:usuario_id, :titulo, :mensagem)');
    $stmt->execute([':usuario_id' => $userId, ':titulo' => $titulo, ':mensagem' => $mensagem]);
}

function listNotifications(int $userId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM notificacoes WHERE usuario_id = :usuario_id ORDER BY criado_em DESC LIMIT 10');
    $stmt->execute([':usuario_id' => $userId]);
    return $stmt->fetchAll();
}

function createReview(int $chamadoId, int $moradorId, int $prestadorId, int $nota, string $comentario): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO avaliacoes (chamado_id, morador_id, prestador_id, nota, comentario) VALUES (:chamado_id, :morador_id, :prestador_id, :nota, :comentario)');
    $stmt->execute([':chamado_id' => $chamadoId, ':morador_id' => $moradorId, ':prestador_id' => $prestadorId, ':nota' => $nota, ':comentario' => $comentario]);
}

function getUserStats(int $userId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM chamados WHERE usuario_id = :usuario_id');
    $stmt->execute([':usuario_id' => $userId]);
    $total = (int) $stmt->fetchColumn();

    $abertos = $pdo->prepare('SELECT COUNT(*) FROM chamados WHERE usuario_id = :usuario_id AND status = "aberto"');
    $abertos->execute([':usuario_id' => $userId]);
    $emAndamento = $pdo->prepare('SELECT COUNT(*) FROM chamados WHERE usuario_id = :usuario_id AND status = "andamento"');
    $emAndamento->execute([':usuario_id' => $userId]);
    $concluidos = $pdo->prepare('SELECT COUNT(*) FROM chamados WHERE usuario_id = :usuario_id AND status = "concluido"');
    $concluidos->execute([':usuario_id' => $userId]);
    $cancelados = $pdo->prepare('SELECT COUNT(*) FROM chamados WHERE usuario_id = :usuario_id AND status = "cancelado"');
    $cancelados->execute([':usuario_id' => $userId]);

    return [
        'total' => $total,
        'abertos' => (int) $abertos->fetchColumn(),
        'andamento' => (int) $emAndamento->fetchColumn(),
        'concluidos' => (int) $concluidos->fetchColumn(),
        'cancelados' => (int) $cancelados->fetchColumn(),
    ];
}
