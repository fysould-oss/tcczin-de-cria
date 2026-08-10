<?php
$basePath = getBasePath();
?>
<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <div class="footer-brand">
                <span>CP</span>
                <div>
                    <strong>ConectaPrédio</strong>
                    <p>Gestão inteligente para condomínios.</p>
                </div>
            </div>
            <p class="footer-copy">2026 © ConectaPrédio. Projeto acadêmico de TCC.</p>
        </div>
        <nav class="footer-nav" aria-label="Rodapé">
            <a href="<?= $basePath ?>index.php">Início</a>
            <a href="<?= $basePath ?>pages/sobre.php">Sobre</a>
            <a href="<?= $basePath ?>pages/servicos.php">Serviços</a>
            <a href="<?= $basePath ?>pages/contato.php">Contato</a>
            <a href="<?= $basePath ?>pages/faq.php">FAQ</a>
        </nav>
    </div>
</footer>
<script src="<?= $basePath ?>assets/js/main.js?v=20260810"></script>
</body>
</html>
