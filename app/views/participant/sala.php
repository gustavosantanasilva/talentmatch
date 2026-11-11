<?php
/**
 * Sala de espera do participante.
 *
 * @var array      $participant
 * @var array      $room
 * @var array      $participants
 * @var array|null $flash
 */
use App\Core\Csrf;
use App\Services\RoomService;

$participants = $participants ?? [];
$flash = $flash ?? null;
$status = (string) $room['status'];

$statusMessage = match ($status) {
    'WAITING' => 'Aguardando o organizador iniciar a partida.',
    'PROFILES' => 'O organizador iniciou a partida. Etapa atual: montagem dos perfis.',
    'GENERATING' => 'As vagas fictícias estão sendo geradas.',
    'JOBS' => 'A apresentação das vagas está em andamento.',
    'RESULTS' => 'Os resultados estão sendo calculados.',
    'FINISHED' => 'A partida foi encerrada.',
    default => 'Aguarde as instruções do organizador.',
};
?>

<?php if ($flash !== null): ?>
    <div class="tm-alert tm-alert--<?= e($flash['type']) ?> tm-mb-3" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="tm-grid" style="grid-template-columns: 1.4fr 1fr; gap:1.25rem; align-items:start">
    <div class="tm-flex-col tm-gap">
        <div class="tm-card tm-reveal">
            <div class="tm-flex-between tm-wrap tm-gap-sm">
                <div>
                    <span class="tm-text-secondary tm-text-sm">Bem-vindo(a),</span>
                    <h1 style="font-size:1.6rem;margin:.1rem 0"><?= e((string) $participant['nickname']) ?></h1>
                </div>
                <span class="tm-badge <?= e(RoomService::statusBadgeClass($status)) ?>">
                    <?= e(RoomService::statusLabel($status)) ?>
                </span>
            </div>

            <div class="tm-alert tm-alert--info tm-mt-2" data-tm-status-message>
                <span class="tm-pulse" aria-hidden="true"></span>
                <?= e($statusMessage) ?>
            </div>

            <?php if ($status === 'WAITING'): ?>
                <p class="tm-text-secondary tm-text-sm tm-mb-0">
                    Mantenha esta página aberta. Ela é atualizada automaticamente quando
                    o organizador iniciar a partida.
                </p>
            <?php endif; ?>
        </div>

        <div class="tm-card">
            <div class="tm-card__header">
                <div>
                    <h2 class="tm-card__title">Participantes na sala</h2>
                    <p class="tm-card__subtitle">
                        <span data-tm-participants-count><?= count($participants) ?></span>
                        <?= count($participants) === 1 ? 'pessoa conectada' : 'pessoas conectadas' ?>
                    </p>
                </div>
                <button type="button" class="tm-btn tm-btn--secondary tm-btn--sm" data-tm-refresh>Atualizar</button>
            </div>

            <ul class="tm-roster" data-tm-roster>
                <?php foreach ($participants as $item): ?>
                    <li class="tm-roster__item">
                        <span class="tm-roster__avatar" aria-hidden="true">
                            <?= e(mb_strtoupper(mb_substr((string) $item['nickname'], 0, 1))) ?>
                        </span>
                        <span class="tm-roster__name">
                            <?= e((string) $item['nickname']) ?>
                            <?php if ((int) $item['id'] === (int) $participant['id']): ?>
                                <span class="tm-badge tm-badge--primary" style="margin-left:.4rem">você</span>
                            <?php endif; ?>
                        </span>
                        <span class="tm-text-secondary tm-text-sm">
                            <?= e(date('H:i', strtotime((string) $item['joined_at']))) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="tm-flex-col tm-gap">
        <div class="tm-card tm-text-center">
            <h2 class="tm-card__title">Código da sala</h2>
            <div class="tm-room-code"><?= e((string) $room['code']) ?></div>
            <p class="tm-text-secondary tm-text-sm tm-mb-0">
                Compartilhe com quem ainda não entrou.
            </p>
        </div>

        <div class="tm-card">
            <h2 class="tm-card__title tm-mb-2">Próximos passos</h2>
            <ol class="tm-text-secondary tm-text-sm" style="padding-left:1.1rem;margin:0">
                <li>Aguarde o organizador iniciar.</li>
                <li>Monte seu perfil profissional.</li>
                <li>Analise as vagas fictícias e candidate-se.</li>
                <li>Veja sua compatibilidade no ranking.</li>
            </ol>
        </div>

        <form method="post" action="<?= e(base_url('sala.php')) ?>"
              data-tm-confirm="Sair da sala? Você poderá entrar novamente enquanto a partida não começar.">
            <?= Csrf::field() ?>
            <input type="hidden" name="action" value="leave">
            <button type="submit" class="tm-btn tm-btn--ghost tm-btn--block">Sair da sala</button>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    var code = <?= json_encode((string) $room['code']) ?>;
    var currentStatus = <?= json_encode($status) ?>;
    var renderedCount = <?= count($participants) ?>;
    var intervalMs = 5000;

    function poll() {
        TM.get('<?= e(base_url('api/room/participants.php')) ?>?code=' + encodeURIComponent(code))
            .then(function (payload) {
                if (!payload || !payload.success || !payload.data) return;
                var data = payload.data;

                if (data.status !== currentStatus) {
                    window.location.reload();
                    return;
                }

                if (Number(data.participants_count) !== renderedCount) {
                    window.location.reload();
                }
            });
    }

    var timer = window.setInterval(poll, intervalMs);

    document.querySelectorAll('[data-tm-refresh]').forEach(function (button) {
        button.addEventListener('click', function () {
            window.clearInterval(timer);
            window.location.reload();
        });
    });

    window.addEventListener('beforeunload', function () {
        window.clearInterval(timer);
    });
})();

document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form.hasAttribute || !form.hasAttribute('data-tm-confirm')) return;
    if (form.dataset.tmConfirmed === '1') return;
    event.preventDefault();
    TM.confirm(form.getAttribute('data-tm-confirm'), 'Sair').then(function (ok) {
        if (ok) {
            form.dataset.tmConfirmed = '1';
            form.submit();
        }
    });
});
</script>
