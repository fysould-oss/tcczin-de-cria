<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';
$metaDescription = 'Painel principal do ConectaPrédio para moradores, prestadores e síndicos.';
$bodyClass = 'dashboard-page';
require __DIR__ . '/../includes/header.php';

$user = currentUser();
$stats = getUserStats((int) $user['id']);
$chamados = getChamadosForUser((int) $user['id']);
$roleLabel = [
    'morador' => 'Morador',
    'prestador' => 'Prestador',
    'sindico' => 'Síndico',
][$user['tipo_usuario'] ?? 'morador'];
?>

<main class="section section-single">
    <div class="container">
        <div class="section-header" style="text-align:left; margin-bottom:26px;">
            <span>Dashboard</span>
            <h2>Olá, <?= sanitize($user['nome']) ?> 👋</h2>
            <p>Você está acessando o painel do ConectaPrédio como <?= sanitize($roleLabel) ?>.</p>
        </div>

        <div class="dashboard-grid">
            <article class="stats-card">
                <h3>Total de chamados</h3>
                <p>Solicitações registradas</p>
                <strong><?= (int) $stats['total'] ?></strong>
            </article>
            <article class="stats-card">
                <h3>Abertos</h3>
                <p>Chamados pendentes</p>
                <strong><?= (int) $stats['abertos'] ?></strong>
            </article>
            <article class="stats-card">
                <h3>Em andamento</h3>
                <p>Atendimento ativo</p>
                <strong><?= (int) $stats['andamento'] ?></strong>
            </article>
            <article class="stats-card">
                <h3>Concluídos</h3>
                <p>Serviços finalizados</p>
                <strong><?= (int) $stats['concluidos'] ?></strong>
            </article>
        </div>

        <div class="panel-grid" style="margin-top: 24px;">
            <section class="panel-card">
                <h3>Meus chamados recentes</h3>
                <?php if ($chamados === []): ?>
                    <div class="empty-state">
                        <p>Você ainda não criou nenhum chamado.</p>
                        <a href="novo-chamado.php" class="button button-primary">+ Solicitar manutenção</a>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Protocolo</th>
                                    <th>Categoria</th>
                                    <th>Status</th>
                                    <th>Prioridade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($chamados as $chamado): ?>
                                    <?php $statusClass = strtolower($chamado['status']); if ($statusClass === 'em andamento') { $statusClass = 'andamento'; } elseif ($statusClass === 'em análise') { $statusClass = 'andamento'; } ?>
                                    <tr>
                                        <td><?= sanitize($chamado['protocolo']) ?></td>
                                        <td><?= sanitize($chamado['categoria']) ?></td>
                                        <td><span class="badge badge-<?= sanitize($statusClass) ?>"><?= sanitize($chamado['status']) ?></span></td>
                                        <td><?= sanitize($chamado['prioridade']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="panel-card">
                <h3>Próximos passos</h3>
                <ul>
                    <li>Crie chamados com descrição, prioridade e localização.</li>
                    <li>Acompanhe a evolução do serviço até a conclusão.</li>
                    <li>Use o histórico para manter tudo organizado.</li>
                </ul>
            </section>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
