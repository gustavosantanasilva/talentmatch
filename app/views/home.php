<?php
/**
 * Página inicial (landing) — apresenta o projeto e o status do sistema.
 *
 * @var array $health  Informações de diagnóstico retornadas por public/index.php
 */
$health = $health ?? ['database' => false, 'php' => PHP_VERSION, 'message' => ''];

$steps = [
    ['t' => 'Entrar', 'd' => 'Escaneie o QR Code, escolha avatar e apelido.', 'c' => '#1368CE'],
    ['t' => 'Criar perfil', 'd' => 'Habilidades, experiências e formações — sem dados sensíveis.', 'c' => '#E21B3C'],
    ['t' => 'Vagas com IA', 'd' => 'A IA gera até quatro vagas fictícias a partir do grupo.', 'c' => '#FFA602'],
    ['t' => 'Candidatar-se', 'd' => 'Cada pessoa decide se candidatar ou pular cada vaga.', 'c' => '#26890C'],
    ['t' => 'Compatibilidade', 'd' => 'O PHP calcula a pontuação com pesos transparentes.', 'c' => '#864CBF'],
    ['t' => 'Resultados', 'd' => 'Rankings por vaga e Top 10 geral no telão.', 'c' => '#0D9488'],
];

$weights = [
    ['label' => 'Habilidades obrigatórias', 'value' => '50%', 'color' => '#E21B3C'],
    ['label' => 'Habilidades desejáveis', 'value' => '25%', 'color' => '#1368CE'],
    ['label' => 'Experiências relacionadas', 'value' => '15%', 'color' => '#FFA602'],
    ['label' => 'Formação relevante', 'value' => '10%', 'color' => '#26890C'],
];
?>

<section class="tm-section tm-ambient" style="padding-top:clamp(1.5rem,4vw,3.5rem)">
    <div class="tm-container tm-container-wide">
        <div class="gk-stage gk-anim-pop">
            <span class="gk-badge gk-badge--white gk-mb-2">Simulação educacional · Feira de ciências e tecnologia</span>
            <h1 class="gk-title-xl" style="max-width:820px">
                Conectando talentos, oportunidades e inteligência artificial.
            </h1>
            <p class="gk-lead gk-text-white gk-mb-3" style="max-width:680px">
                O <strong>TalentMatch</strong> é um jogo multiplayer de recrutamento simulado.
                Entre na sala pelo QR Code, monte seu perfil, dispute vagas fictícias geradas por IA
                e discuta os vieses dos algoritmos de seleção.
            </p>
            <div class="gk-flex gk-gap-sm gk-wrap">
                <a class="gk-btn gk-btn--yellow gk-btn--lg" href="<?= e(base_url('admin/')) ?>">Sou organizador</a>
                <a class="gk-btn gk-btn--white gk-btn--lg" href="#como-funciona">Como funciona</a>
            </div>
        </div>

        <div id="como-funciona" class="tm-grid tm-grid-3 gk-mt-4" style="scroll-margin-top:90px">
            <?php foreach ($steps as $index => $step): ?>
                <div class="gk-card gk-card--hover gk-anim-slide gk-delay-<?= (int) ($index % 3 + 1) ?>">
                    <span class="gk-step__num gk-mb-2" style="background:<?= e($step['c']) ?>"><?= (int) ($index + 1) ?></span>
                    <h3 class="gk-card__title"><?= e($step['t']) ?></h3>
                    <p class="gk-text-muted gk-mb-0"><?= e($step['d']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="tm-section-sm">
    <div class="tm-container tm-container-wide">
        <div class="gk-split-2">
            <div class="gk-card">
                <span class="gk-eyebrow">Transparência</span>
                <h2 class="gk-title-md gk-mt-1 gk-mb-2">O algoritmo não é uma caixa-preta</h2>
                <p class="gk-text-muted">Cada candidatura recebe uma nota de 0 a 100 composta por quatro critérios:</p>

                <div class="gk-mt-2">
                    <?php foreach ($weights as $weight): ?>
                        <div class="gk-mb-2">
                            <div class="gk-flex-between gk-mb-1">
                                <span style="font-weight:600"><?= e($weight['label']) ?></span>
                                <strong><?= e($weight['value']) ?></strong>
                            </div>
                            <div class="gk-progress">
                                <div class="gk-progress__bar" style="width:<?= e($weight['value']) ?>;background:<?= e($weight['color']) ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p class="gk-text-muted gk-text-sm gk-mt-2 gk-mb-0">
                    O resultado não representa probabilidade real de contratação.
                </p>
            </div>

            <div class="gk-card" id="diagnostico" style="scroll-margin-top:90px">
                <span class="gk-eyebrow">Ambiente</span>
                <h2 class="gk-title-md gk-mt-1 gk-mb-2">Diagnóstico do sistema</h2>

                <div class="gk-flex-between gk-mb-2">
                    <span>Versão do PHP</span>
                    <span class="gk-badge gk-badge--green"><?= e((string) $health['php']) ?></span>
                </div>
                <div class="gk-flex-between gk-mb-3">
                    <span>Conexão com o banco</span>
                    <?php if (!empty($health['database'])): ?>
                        <span class="gk-badge gk-badge--green">Conectado</span>
                    <?php else: ?>
                        <span class="gk-badge gk-badge--red">Sem conexão</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($health['database'])): ?>
                    <div class="gk-alert gk-alert--success">
                        O ambiente está pronto e o banco respondeu corretamente.
                        Acesse o <strong>Painel do organizador</strong> para criar uma sala e gerar o QR Code.
                    </div>
                <?php else: ?>
                    <div class="gk-alert gk-alert--warning">
                        <div>
                            <strong>Banco de dados não configurado.</strong><br>
                            <?= e((string) $health['message']) ?>
                            <div class="gk-text-sm gk-mt-1">
                                Copie <code>.env.example</code> para <code>.env</code>, preencha as credenciais
                                e importe <code>database/schema.sql</code>.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
