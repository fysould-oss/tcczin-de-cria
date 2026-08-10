<?php
$pageTitle = 'FAQ';
$metaDescription = 'Perguntas frequentes sobre o ConectaPrédio.';
$bodyClass = 'public-page';
require __DIR__ . '/../includes/header.php';
?>

<main class="section section-single">
    <div class="container">
        <div class="section-header">
            <span>FAQ</span>
            <h2>Perguntas frequentes</h2>
            <p>Encontre respostas rápidas sobre como a plataforma funciona.</p>
        </div>
        <div class="faq-grid">
            <article class="card">
                <h3>Como solicitar um serviço?</h3>
                <p>Cadastre-se, faça login e crie um chamado com categoria, descrição e prioridade.</p>
            </article>
            <article class="card">
                <h3>Quem pode usar a plataforma?</h3>
                <p>Moradores, prestadores e síndicos podem utilizar o sistema com perfis específicos.</p>
            </article>
            <article class="card">
                <h3>Como funciona o acompanhamento?</h3>
                <p>O chamado acompanha um fluxo com status, histórico e comunicação entre as partes.</p>
            </article>
            <article class="card">
                <h3>Como avaliar um prestador?</h3>
                <p>Depois da conclusão do serviço, o morador pode enviar uma avaliação de 1 a 5 estrelas.</p>
            </article>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
