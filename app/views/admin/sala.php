<?php
/**
 * Lobby da sala — QR Code de entrada e participantes ao vivo.
 *
 * @var array  $room
 * @var string $joinUrl
 * @var array  $participants
 * @var array  $events
 * @var array  $allowedTransitions
 * @var array|null $flash
 */
use App\Core\Csrf;
use App\Models\Participant;
use App\Services\RoomService;

$participants = $participants ?? [];
$events = $events ?? [];
$allowedTransitions = $allowedTransitions ?? [];
$flash = $flash ?? null;
$status = (string) $room['status'];
$canStart = in_array('PROFILES', $allowedTransitions, true) && count($participants) >= 1;
$canFinish = in_array('FINISHED', $allowedTransitions, true);

$tilePalette = ['#1368CE', '#E21B3C', '#FFA602', '#26890C', '#864CBF', '#0D9488'];
$roomId = (int) $room['id'];
?>

<?php if ($flash !== null): ?>
    <div class="gk-alert gk-alert--<?= e($flash['type'] === 'danger' ? 'danger' : $flash['type']) ?> gk-mb-3" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="gk-toolbar gk-mb-3">
    <div class="gk-flex-center gk-gap-sm gk-wrap">
        <h2 class="gk-room-code gk-mb-0" style="font-size:clamp(1.6rem,3vw,2.2rem)"><?= e((string) $room['code']) ?></h2>
        <span class="gk-badge <?= e(RoomService::statusBadgeClass($status)) ?>"><?= e(RoomService::statusLabel($status)) ?></span>
    </div>
    <div class="gk-toolbar__actions">
        <a class="gk-btn gk-btn--dark gk-btn--sm" href="<?= e(base_url('admin/telao.php?id=' . $roomId)) ?>" target="_blank" rel="noopener">
            🖥️ Modo telão
        </a>
        <a class="gk-btn gk-btn--ghost gk-btn--sm" href="<?= e(base_url('admin/dashboard.php')) ?>">← Voltar</a>
        <a class="gk-btn gk-btn--white gk-btn--sm" href="<?= e(base_url('admin/sala.php?id=' . $roomId)) ?>">Atualizar</a>
    </div>
</div>

<div class="gk-lobby">
    <div class="gk-lobby__side">
        <div class="gk-card gk-card--pad-lg gk-center gk-anim-pop">
            <span class="gk-eyebrow">Entre na sala</span>
            <h3 class="gk-card__title gk-mt-1 gk-mb-2">Escaneie o QR Code</h3>

            <div id="qrcode" class="gk-qr gk-qr--lg" data-url="<?= e($joinUrl) ?>" role="img"
                 aria-label="QR Code de entrada da sala <?= e((string) $room['code']) ?>"></div>

            <div class="gk-link-box gk-mt-3" style="text-align:left">
                <code class="gk-mono"><?= e($joinUrl) ?></code>
                <button type="button" class="gk-btn gk-btn--white gk-btn--sm" data-tm-copy="<?= e($joinUrl) ?>">Copiar</button>
            </div>

            <div class="gk-count gk-mt-3" style="justify-content:center">
                <span class="gk-count__value" data-gk-count><?= count($participants) ?></span>
                <span class="gk-count__label"><?= count($participants) === 1 ? 'participante na sala' : 'participantes na sala' ?></span>
            </div>
        </div>

        <div class="gk-card gk-anim-slide gk-delay-1">
            <h3 class="gk-card__title gk-mb-2">Controle da partida</h3>

            <?php if ($status === 'WAITING'): ?>
                <div class="gk-alert gk-alert--info gk-mb-2">
                    Aguardando participantes. O início exige pelo menos uma pessoa na sala.
                </div>
                <form method="post" action="<?= e(base_url('admin/sala.php?id=' . $roomId)) ?>">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="start">
                    <button type="submit" class="gk-btn gk-btn--primary gk-btn--lg gk-btn--block" <?= $canStart ? '' : 'disabled' ?>>
                        ▶ Iniciar partida
                    </button>
                    <?php if (!$canStart): ?>
                        <div class="gk-hint gk-center gk-mt-1">Disponível após o primeiro participante entrar.</div>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <p class="gk-lead gk-mb-2">
                    Estado atual: <strong><?= e(RoomService::statusLabel($status)) ?></strong>.
                    Os controles das próximas etapas serão liberados nas fases seguintes.
                </p>
            <?php endif; ?>

            <?php if ($canFinish): ?>
                <hr class="gk-divider">
                <form method="post" action="<?= e(base_url('admin/sala.php?id=' . $roomId)) ?>"
                      data-tm-confirm="Encerrar a sala? Esta ação não pode ser desfeita.">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="finish">
                    <button type="submit" class="gk-btn gk-btn--red gk-btn--block">Encerrar sala</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="gk-card gk-anim-slide gk-delay-2">
            <h3 class="gk-card__title gk-mb-2">Resumo</h3>
            <div class="gk-flex-between gk-mb-1"><span class="gk-text-muted">Participantes</span><strong><?= count($participants) ?></strong></div>
            <div class="gk-flex-between gk-mb-1"><span class="gk-text-muted">Perfis concluídos</span><strong><?= (int) ($completedProfiles ?? 0) ?> / <?= count($participants) ?></strong></div>
            <div class="gk-flex-between gk-mb-1"><span class="gk-text-muted">Vagas geradas</span><strong><?= (int) $room['current_job_index'] > 0 || $status === 'JOBS' || $status === 'RESULTS' ? (int) ($jobsCount ?? 0) : 0 ?></strong></div>
            <div class="gk-flex-between gk-mb-1"><span class="gk-text-muted">Origem das vagas</span><strong><?= e((string) $room['jobs_source']) ?></strong></div>
            <div class="gk-flex-between"><span class="gk-text-muted">Estado</span><strong><?= e($status) ?></strong></div>
        </div>
    </div>

    <div class="gk-lobby__main">
        <div class="gk-card gk-card--pad-lg gk-anim-slide">
            <div class="gk-card__head">
                <div>
                    <h2 class="gk-card__title">Participantes</h2>
                    <p class="gk-card__subtitle gk-mb-0">A lista atualiza sozinha a cada 3 segundos.</p>
                </div>
                <span class="gk-badge gk-badge--green" data-gk-status><?= e(RoomService::statusLabel($status)) ?></span>
            </div>

            <ul class="gk-players" data-gk-players>
                <?php foreach ($participants as $index => $item): ?>
                    <?php
                        $color = (string) ($item['avatar_color'] ?? '');
                        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color) !== 1) {
                            $color = $tilePalette[$index % count($tilePalette)];
                        }
                    ?>
                    <li class="gk-tile" style="--gk-tile-bg:<?= e(strtoupper($color)) ?>">
                        <span class="gk-tile__avatar" aria-hidden="true"><?= e((string) ($item['avatar'] ?? '🙂')) ?></span>
                        <span class="gk-tile__name" title="<?= e((string) $item['nickname']) ?>"><?= e((string) $item['nickname']) ?></span>
                        <span class="gk-tile__status"><?= e(Participant::statusLabel((string) $item['status'])) ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if ($participants === []): ?>
                    <li class="gk-tile gk-tile--empty" style="grid-column:1/-1">
                        <span class="gk-empty__icon" aria-hidden="true">📱</span>
                        <span>Ninguém entrou ainda.<br>Compartilhe o QR Code!</span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="gk-card gk-anim-slide gk-delay-1">
            <div class="gk-card__head">
                <div>
                    <h3 class="gk-card__title">Eventos recentes</h3>
                    <p class="gk-card__subtitle gk-mb-0">Registro da partida.</p>
                </div>
            </div>
            <?php if ($events === []): ?>
                <p class="gk-text-muted gk-mb-0 gk-text-sm">Nenhum evento registrado.</p>
            <?php else: ?>
                <ul class="gk-flex-col gk-gap-sm" style="list-style:none;padding:0;margin:0">
                    <?php foreach ($events as $event): ?>
                        <li class="gk-flex-between gk-text-sm">
                            <span class="gk-badge"><?= e((string) $event['type']) ?></span>
                            <span class="gk-text-muted"><?= e(date('d/m H:i', strtotime((string) $event['created_at']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var target = document.getElementById('qrcode');
    if (target && typeof QRCode !== 'undefined') {
        new QRCode(target, {
            text: target.getAttribute('data-url'),
            width: 300,
            height: 300,
            colorDark: '#17212B',
            colorLight: '#FFFFFF',
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    var container = document.querySelector('[data-gk-players]');
    var count = document.querySelector('[data-gk-count]');
    var statusEl = document.querySelector('[data-gk-status]');

    if (container) {
        TM.watchParticipants({
            code: <?= json_encode((string) $room['code']) ?>,
            endpoint: <?= json_encode(base_url('api/room/participants.php')) ?>,
            container: container,
            count: count,
            status: statusEl,
            interval: 3000
        });
    }
});

document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form.hasAttribute || !form.hasAttribute('data-tm-confirm')) return;
    if (form.dataset.tmConfirmed === '1') return;
    event.preventDefault();
    var message = form.getAttribute('data-tm-confirm');
    TM.confirm(message, 'Encerrar').then(function (ok) {
        if (ok) {
            form.dataset.tmConfirmed = '1';
            form.submit();
        }
    });
});
</script>
