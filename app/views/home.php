<?php
/**
 * Página inicial (landing) — apresenta o projeto e o status do sistema.
 *
 * @var array $health  Informações de diagnóstico retornadas por public/index.php
 */
$health = $health ?? ['database' => false, 'php' => PHP_VERSION, 'message' => ''];
$steps = [
    ['t' => 'Entrar', 'd' => 'Participantes escaneiam o QR Code e informam um apelido.'],
    ['t' => 'Criar perfil', 'd' => 'Habilidades, experiências e formações — sem dados sensíveis.'],
    ['t' => 'Vagas com IA', 'd' => 'A IA gera até quatro vagas fictícias a partir do grupo.'],
    ['t' => 'Candidatar-se', 'd' => 'Cada pessoa decide se candidatar ou pular cada vaga.'],
    ['t' => 'Compatibilidade', 'd' => 'O PHP calcula a pontuação com pesos transparentes.'],
    ['t' => 'Resultados', 'd' => 'Rankings por vaga e Top 10 geral no telão.'],
];
?>

<section class="tm-section tm-ambient">
    <div class="tm-container tm-container-wide">
        <div class="tm-hero tm-reveal">
            <span class="tm-badge tm-badge--gold tm-mb-2">Simulação educacional · Feira de ciências e tecnologia</span>
            <h1>Conectando talentos, oportunidades e inteligência artificial.</h1>
            <p>
                O <strong>TalentMatch</strong> é um jogo multiplayer de recrutamento simulado. Vários participantes
                entram em uma sala por QR Code, criam perfis profissionais e disputam vagas fictícias geradas por IA.
                No fim, algoritmos de seleção são analisados — inclusive seus possíveis vieses.
            </p>
            <div class="tm-flex tm-gap-sm tm-wrap tm-mt-3">
                <a href="<?= e(base_url('admin/')) ?>" class="tm-btn tm-btn--lg" style="background:#fff;color:#065F46">
                    Sou organizador
                </a>
                <a href="#como-funciona" class="tm-btn tm-btn--lg" style="background:transparent;border-color:rgba(255,255,255,.5);color:#fff">
                    Como funciona
                </a>
            </div>
        </div>

        <div id="como-funciona" class="tm-grid tm-grid-3 tm-mt-4" style="scroll-margin-top:90px">
            <?php foreach ($steps as $index => $step): ?>
                <div class="tm-card tm-card--hover tm-reveal tm-reveal-delay-<?= (int) ($index % 3 + 1) ?>">
                    <div class="tm-stat__icon tm-mb-2">
                        <strong><?= (int) ($index + 1) ?></strong>
                    </div>
                    <h3 class="tm-card__title"><?= e($step['t']) ?></h3>
                    <p class="tm-text-secondary tm-mb-0"><?= e($step['d']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="tm-section-sm">
    <div class="tm-container tm-container-wide">
        <div class="tm-grid tm-grid-2">
            <div class="tm-card">
                <div class="tm-card__header">
                    <div>
                        <h2 class="tm-card__title">Transparência do algoritmo</h2>
                        <p class="tm-card__subtitle">A pontuação não é uma caixa-preta.</p>
                    </div>
                </div>
                <p class="tm-text-secondary">Cada candidatura recebe uma nota de 0 a 100 composta por quatro critérios:</p>
                <table class="tm-table">
                    <thead>
                        <tr><th>Critério</th><th style="text-align:right">Peso</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Habilidades obrigatórias</td><td style="text-align:right">50%</td></tr>
                        <tr><td>Habilidades desejáveis</td><td style="text-align:right">25%</td></tr>
                        <tr><td>Experiências relacionadas</td><td style="text-align:right">15%</td></tr>
                        <tr><td>Formação relevante</td><td style="text-align:right">10%</td></tr>
                    </tbody>
                </table>
                <p class="tm-text-secondary tm-mt-2 tm-mb-0 tm-text-sm">
                    O resultado não representa probabilidade real de contratação.
                </p>
            </div>

            <div class="tm-card" id="diagnostico" style="scroll-margin-top:90px">
                <div class="tm-card__header">
                    <div>
                        <h2 class="tm-card__title">Diagnóstico do sistema</h2>
                        <p class="tm-card__subtitle">Verificação de ambiente e banco de dados.</p>
                    </div>
                </div>

                <div class="tm-flex-between tm-mb-2">
                    <span>Versão do PHP</span>
                    <span class="tm-badge tm-badge--primary"><?= e((string) $health['php']) ?></span>
                </div>
                <div class="tm-flex-between tm-mb-2">
                    <span>Conexão com o banco</span>
                    <?php if (!empty($health['database'])): ?>
                        <span class="tm-badge tm-badge--success">Conectado</span>
                    <?php else: ?>
                        <span class="tm-badge tm-badge--danger">Sem conexão</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($health['database'])): ?>
                    <div class="tm-alert tm-alert--success">
                        O ambiente está pronto e o banco respondeu corretamente.
                        Acesse o <strong>Painel do organizador</strong> para criar uma sala e gerar o QR Code.
                    </div>
                <?php else: ?>
                    <div class="tm-alert tm-alert--warning">
                        <div>
                            <strong>Banco de dados não configurado.</strong><br>
                            <?= e((string) $health['message']) ?>
                            <div class="tm-text-sm tm-mt-1">
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
