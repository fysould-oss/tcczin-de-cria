<?php
$pageTitle = 'Contato';
$metaDescription = 'Entre em contato com o ConectaPrédio.';
$bodyClass = 'public-page';
require __DIR__ . '/../includes/header.php';
?>

<main class="section section-single">
    <div class="container">
        <div class="section-header">
            <span>Contato</span>
            <h2>Fale conosco</h2>
            <p>Envie uma mensagem e vamos responder com a maior brevidade possível.</p>
        </div>
        <div class="form-card">
            <form action="#" method="post">
                <label>
                    <span>Nome</span>
                    <input type="text" name="nome" placeholder="Seu nome completo" required>
                </label>
                <label>
                    <span>E-mail</span>
                    <input type="email" name="email" placeholder="nome@exemplo.com" required>
                </label>
                <label>
                    <span>Assunto</span>
                    <input type="text" name="assunto" placeholder="Motivo do contato" required>
                </label>
                <label>
                    <span>Mensagem</span>
                    <textarea name="mensagem" rows="6" placeholder="Escreva sua mensagem..." required></textarea>
                </label>
                <button type="submit" class="button button-primary">Enviar mensagem</button>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
