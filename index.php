<?php
$pageTitle = 'Início';
<<<<<<< HEAD
$metaDescription = 'CondoConnect reúne moradores, síndicos e prestadores em uma plataforma moderna para gestão de serviços em condomínios.';
=======
$metaDescription = 'ConectaPrédio é legal moradores, síndicos e prestadores em uma plataforma moderna para gestão de serviços em condomínios.';
>>>>>>> 48415c1df16d7f4c7417ac36842397ad957aa99e
$bodyClass = 'home-page';
require __DIR__ . '/includes/header.php';
?>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <p class="eyebrow">CondoConnect</p>
                <h1>Organize manutenção, comunicação e chamados em um só lugar.</h1>
                <p>Uma plataforma moderna para moradores, prestadores e síndicos acompanharem serviços de condomínio com mais praticidade, transparência e agilidade.</p>
                <div class="hero-actions">
                    <a href="pages/cadastro.php" class="button button-primary">Criar conta</a>
                    <a href="pages/login.php" class="button button-secondary">Entrar</a>
                </div>
                <div class="hero-meta">
                    <div>
                        <strong>+8 categorias</strong>
                        <p>Elétrica, hidráulica, pintura, limpeza e muito mais.</p>
                    </div>
                    <div>
                        <strong>Chat integrado</strong>
                        <p>Converse com prestadores e síndicos durante o atendimento.</p>
                    </div>
                </div>
            </div>
            <div class="hero-card">
                <span class="tag">Chamado #1024</span>
                <h3>Manutenção de elevador em andamento</h3>
                <p>O síndico acompanhou a solicitação, designou o prestador e o morador acompanha o status em tempo real.</p>
                <ul>
                    <li>Solicitação com fotos e descrição</li>
                    <li>Status atualizado em cada etapa</li>
                    <li>Histórico completo do chamado</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-header">
                <span>Serviços</span>
                <h2>Categorias de manutenção disponíveis</h2>
                <p>Atenda às principais necessidades do seu condomínio com organização e rapidez.</p>
            </div>
            <div class="cards-grid">
                <article class="service-card">
                    <div class="icon">⚡</div>
                    <h3>Elétrica</h3>
                    <p>Instalações, reparos e manutenção de sistemas elétricos.</p>
                </article>
                <article class="service-card">
                    <div class="icon icon-green">💧</div>
                    <h3>Hidráulica</h3>
                    <p>Vazamentos, encanamentos e manutenção de pontos de água.</p>
                </article>
                <article class="service-card">
                    <div class="icon icon-purple">🎨</div>
                    <h3>Pintura</h3>
                    <p>Pequenos reparos, retoques e pintura de áreas comuns.</p>
                </article>
                <article class="service-card">
                    <div class="icon icon-gray">🧹</div>
                    <h3>Limpeza</h3>
                    <p>Conservação, higienização e limpeza técnica de áreas.</p>
                </article>
                <article class="service-card">
                    <div class="icon icon-orange">⬆️</div>
                    <h3>Elevadores</h3>
                    <p>Inspeção preventiva e suporte emergencial.</p>
                </article>
                <article class="service-card">
                    <div class="icon icon-teal">🌿</div>
                    <h3>Jardinagem</h3>
                    <p>Cuidados com jardins, áreas verdes e paisagismo.</p>
                </article>
                <article class="service-card">
                    <div class="icon icon-red">🔒</div>
                    <h3>Segurança</h3>
                    <p>Portaria, câmeras e controle de acesso.</p>
                </article>
                <article class="service-card">
                    <div class="icon">🛠️</div>
                    <h3>Reparos gerais</h3>
                    <p>Manutenção preventiva e pequenos reparos do dia a dia.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-header">
                <span>Como funciona</span>
                <h2>Em poucos passos, o problema fica resolvido</h2>
            </div>
            <div class="steps-grid">
                <article class="step-card">
                    <h3>1. Registre</h3>
                    <p>Abra um chamado com detalhes, local do problema e fotos.</p>
                </article>
                <article class="step-card">
                    <h3>2. Acompanhe</h3>
                    <p>Veja o andamento, histórico e mensagens em um painel claro.</p>
                </article>
                <article class="step-card">
                    <h3>3. Atribua</h3>
                    <p>O síndico pode designar prestadores de forma rápida e organizada.</p>
                </article>
                <article class="step-card">
                    <h3>4. Conclua</h3>
                    <p>Finalize o serviço e avalie o atendimento com nota e comentário.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-header">
                <span>Benefícios</span>
                <h2>Mais organização para o condomínio</h2>
            </div>
            <div class="benefits-grid">
                <article class="benefit-card">
                    <h3>Comunicação centralizada</h3>
                    <p>Todos os envolvidos acompanham o processo em uma mesma plataforma.</p>
                </article>
                <article class="benefit-card">
                    <h3>Transparência</h3>
                    <p>Status do chamado, histórico e percepção do atendimento ficam visíveis.</p>
                </article>
                <article class="benefit-card">
                    <h3>Mais agilidade</h3>
                    <p>Redução de ruídos e retrabalho com processo mais simples.</p>
                </article>
                <article class="benefit-card">
                    <h3>Histórico confiável</h3>
                    <p>Os registros servem como base para decisões futuras e manutenção preventiva.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-header">
                <span>Para prestadores</span>
                <h2>Receba solicitações e organize seu trabalho</h2>
            </div>
            <div class="content-grid">
                <article class="content-card">
                    <h3>Perfil profissional</h3>
                    <p>Crie seu perfil com especialidades, experiência e região de atendimento.</p>
                </article>
                <article class="content-card">
                    <h3>Atendimento mais claro</h3>
                    <p>Receba os chamados já com categoria, local, prioridade e descrição.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-header">
                <span>Depoimentos</span>
                <h2>O que o público diz sobre a solução</h2>
            </div>
            <div class="testimonial-grid">
                <article class="testimonial-card">
                    <p>“O sistema mudou a forma como meu condomínio organiza manutenção e comunicação.”</p>
                    <strong>Mariana, síndica</strong>
                </article>
                <article class="testimonial-card">
                    <p>“Agora consigo acompanhar todos os chamados e responder com mais rapidez.”</p>
                    <strong>Ricardo, morador</strong>
                </article>
                <article class="testimonial-card">
                    <p>“O perfil profissional e o histórico do chamado deixam tudo muito mais claro.”</p>
                    <strong>Patrícia, prestadora</strong>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container cta-card">
            <div>
                <span class="eyebrow">Pronto para começar?</span>
                <h2>Modernize a gestão do seu condomínio com ConectaPrédio.</h2>
            </div>
            <a href="pages/cadastro.php" class="button button-secondary">Criar conta</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
cd C:\xampp\htdocs\tcczin-de-cria
C:\xampp\php\php.exe -S 127.0.0.1:8000