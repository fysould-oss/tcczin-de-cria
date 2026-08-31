<?php
$pageTitle = 'Serviços';
$metaDescription = 'Veja as categorias de serviços disponíveis no ConectaPrédio.';
$bodyClass = 'public-page';
require __DIR__ . '/../includes/header.php';
?>

<main class="section section-single">
    <div class="container">
        <div class="section-header">
            <span>Serviços</span>
            <h2>Categorias disponíveis para o condomínio</h2>
            <p>O CondoConnect organiza as demandas mais comuns de manutenção residencial.</p>
        </div>
        <div class="service-list">
            <article class="service-card">
                <div class="icon">⚡</div>
                <h3>Elétrica</h3>
                <p>Instalações, reparos e manutenção elétrica.</p>
            </article>
            <article class="service-card">
                <div class="icon icon-green">💧</div>
                <h3>Hidráulica</h3>
                <p>Vazamentos, encanamentos e manutenção de água.</p>
            </article>
            <article class="service-card">
                <div class="icon icon-purple">🎨</div>
                <h3>Pintura</h3>
                <p>Reparo, pintura e manutenção de superfícies.</p>
            </article>
            <article class="service-card">
                <div class="icon icon-gray">🧹</div>
                <h3>Limpeza</h3>
                <p>Higienização e manutenção de áreas comuns.</p>
            </article>
            <article class="service-card">
                <div class="icon icon-orange">⬆️</div>
                <h3>Elevadores</h3>
                <p>Inspeções e manutenção de elevadores.</p>
            </article>
            <article class="service-card">
                <div class="icon icon-teal">🌿</div>
                <h3>Jardinagem</h3>
                <p>Cuidados com áreas verdes e jardins.</p>
            </article>
            <article class="service-card">
                <div class="icon icon-red">🔒</div>
                <h3>Segurança</h3>
                <p>Controle de acesso e suporte de segurança.</p>
            </article>
            <article class="service-card">
                <div class="icon">🛠️</div>
                <h3>Reparos gerais</h3>
                <p>Manutenção preventiva e pequenos reparos.</p>
            </article>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
