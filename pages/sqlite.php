<?php
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'SQLite';
$metaDescription = 'Visualize as tabelas e registros do banco SQLite do CondoConnect.';
$bodyClass = 'public-page';
require __DIR__ . '/../includes/header.php';

try {
    $pdo = getPDO();
    $tables = [];
    foreach ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name") as $row) {
        $tables[] = $row['name'];
    }
} catch (Throwable $e) {
    $tables = [];
    $errorMessage = 'Não foi possível acessar o banco SQLite: ' . $e->getMessage();
}
?>

<main class="section section-single">
    <div class="container">
        <div class="section-header">
            <span>Banco</span>
            <h2>Visualização do SQLite</h2>
            <p>Consulte as tabelas e os registros salvos pelo sistema.</p>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?= sanitize($errorMessage) ?></div>
        <?php endif; ?>

        <?php foreach ($tables as $table): ?>
            <section class="panel-card" style="margin-bottom: 20px;">
                <h3><?= sanitize($table) ?></h3>
                <?php
                try {
                    $stmt = $pdo->query('SELECT * FROM ' . $table . ' ORDER BY 1 DESC LIMIT 20');
                    $rows = $stmt->fetchAll();
                } catch (Throwable $e) {
                    $rows = [];
                    $rowsError = 'Não foi possível listar os registros: ' . $e->getMessage();
                }
                ?>
                <?php if (!empty($rowsError)): ?>
                    <div class="alert alert-error"><?= sanitize($rowsError) ?></div>
                <?php elseif ($rows === []): ?>
                    <p>Nenhum registro encontrado.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($rows[0]) as $column): ?>
                                        <th><?= sanitize($column) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?= sanitize((string) $value) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
