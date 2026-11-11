<?php
/**
 * Detalhe e controle de uma sala.
 *
 * @var array  $room
 * @var string $joinUrl
 * @var array  $participants
 * @var array  $events
 * @var array  $allowedTransitions
 * @var array|null $flash
 */
use App\Core\Csrf;
use App\Services\RoomService;

$participants = $participants ?? [];
$events = $events ?? [];
$allowedTransitions = $allowedTransitions ?? [];
$flash = $flash ?? null;
$status = (string) $room['status'];
$canStart = in_array('PROFILES', $allowedTransitions, true) && count($participants) >= 1;
$canFinish = in_array('FINISHED', $allowedTransitions, true);
?>

<?php if ($flash !== null): ?>
    <div class="tm-alert tm-alert--<?= e($flash['type']) ?> tm-mb-3" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="tm-flex-between tm-wrap tm-gap tm-mb-3">
    <div>
        <div class="tm-flex-center tm-gap-sm">
            <h2 class="tm-room-code tm-mb-0"><?= e((string) $room['code']) ?></h2>
            <span class="tm-badge <?= e(RoomService::statusBadgeClass($status)) ?>">
                <?= e(RoomService::statusLabel($status)) ?>
            </span>
        </div>
        <p class="tm-text-secondary tm-mb-0 tm-text-sm">
            Sala criada em <?= e(date('d/m/Y H:i', strtotime((string) $room['created_at']))) ?>
            <?php if (!empty($room['started_at'])): ?>
                · iniciada em <?= e(date('d/m/Y H:i', strtotime((string) $room['started_at']))) ?>
            <?php endif; ?>
        </p>
    </div>

    <div class="tm-flex-center tm-gap-sm">
        <a class="tm-btn tm-btn--secondary tm-btn--sm" href="<?= e(base_url('admin/dashboard.php')) ?>">← Voltar</a>
        <a class="tm-btn tm-btn--secondary tm-btn--sm" href="<?= e(base_url('admin/sala.php?id=' . (int) $room['id'])) ?>">Atualizar</a>
    </div>
</div>

<div class="tm-grid" style="grid-template-columns: 1fr 1.5fr; gap:1.25rem">
    <div class="tm-flex-col tm-gap">
        <div class="tm-card tm-text-center">
            <h3 class="tm-card__title">Entrada por QR Code</h3>
            <p class="tm-card__subtitle">Projete no telão para os participantes.</p>

            <div class="tm-flex-center tm-mt-2" style="justify-content:center">
                <div id="qrcode" class="tm-qr" data-url="<?= e($joinUrl) ?>" role="img"
                     aria-label="QR Code de entrada da sala <?= e((string) $room['code']) ?>"></div>
            </div>

            <div class="tm-link-box tm-mt-2" style="text-align:left">
                <code class="tm-mono"><?= e($joinUrl) ?></code>
                <button type="button" class="tm-btn tm-btn--secondary tm-btn--sm" data-tm-copy="<?= e($joinUrl) ?>">Copiar</button>
            </div>
        </div>

        <div class="tm-card">
            <h3 class="tm-card__title tm-mb-2">Resumo</h3>
            <div class="tm-flex-between tm-mb-1"><span class="tm-text-secondary">Participantes</span><strong><?= count($participants) ?></strong></div>
            <div class="tm-flex-between tm-mb-1"><span class="tm-text-secondary">Vagas geradas</span><strong><?= (int) $room['current_job_index'] > 0 || $status === 'JOBS' || $status === 'RESULTS' ? (int) ($jobsCount ?? 0) : 0 ?></strong></div>
            <div class="tm-flex-between tm-mb-1"><span class="tm-text-secondary">Origem das vagas</span><strong><?= e((string) $room['jobs_source']) ?></strong></div>
            <div class="tm-flex-between"><span class="tm-text-secondary">Estado</span><strong><?= e($status) ?></strong></div>
        </div>
    </div>

    <div class="tm-flex-col tm-gap">
        <div class="tm-card">
            <div class="tm-card__header">
                <div>
                    <h3 class="tm-card__title">Controle da partida</h3>
                    <p class="tm-card__subtitle">As transições são validadas no servidor.</p>
                </div>
            </div>

            <?php if ($status === 'WAITING'): ?>
                <div class="tm-alert tm-alert--info tm-mb-2">
                    Aguardando participantes. O início exige pelo menos uma pessoa na sala.
                </div>
                <form method="post" action="<?= e(base_url('admin/sala.php?id=' . (int) $room['id'])) ?>">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="start">
                    <button type="submit" class="tm-btn tm-btn--primary" <?= $canStart ? '' : 'disabled' ?>>
                        Iniciar partida
                    </button>
                    <?php if (!$canStart): ?>
                        <span class="tm-hint">Disponível após o primeiro participante entrar.</span>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <p class="tm-text-secondary">
                    Estado atual: <strong><?= e(RoomService::statusLabel($status)) ?></strong>.
                    Os controles das próximas etapas serão liberados nas fases seguintes do desenvolvimento.
                </p>
            <?php endif; ?>

            <?php if ($canFinish): ?>
                <hr style="border:0;border-top:1px solid var(--tm-border);margin:1.25rem 0">
                <form method="post" action="<?= e(base_url('admin/sala.php?id=' . (int) $room['id'])) ?>"
                      data-tm-confirm="Encerrar a sala? Esta ação não pode ser desfeita.">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="finish">
                    <button type="submit" class="tm-btn tm-btn--danger tm-btn--sm">Encerrar sala</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="tm-card">
            <div class="tm-card__header">
                <div>
                    <h3 class="tm-card__title">Participantes</h3>
                    <p class="tm-card__subtitle">Nomes de exibição (apelidos).</p>
                </div>
            </div>

            <?php if ($participants === []): ?>
                <div class="tm-alert tm-alert--info">
                    Ninguém entrou ainda. Compartilhe o QR Code ou o link de entrada.
                </div>
            <?php else: ?>
                <div class="tm-table-scroll">
                    <table class="tm-table tm-table--zebra">
                        <thead>
                            <tr><th>#</th><th>Apelido</th><th>Perfil</th><th>Entrou em</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $index => $participant): ?>
                                <tr>
                                    <td><?= (int) ($index + 1) ?></td>
                                    <td><?= e((string) $participant['nickname']) ?></td>
                                    <td><span class="tm-badge"><?= e((string) $participant['status']) ?></span></td>
                                    <td class="tm-text-secondary tm-text-sm">
                                        <?= e(date('H:i', strtotime((string) $participant['joined_at']))) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="tm-card">
            <div class="tm-card__header">
                <div>
                    <h3 class="tm-card__title">Eventos recentes</h3>
                    <p class="tm-card__subtitle">Registro da partida.</p>
                </div>
            </div>
            <?php if ($events === []): ?>
                <p class="tm-text-secondary tm-mb-0 tm-text-sm">Nenhum evento registrado.</p>
            <?php else: ?>
                <ul class="tm-flex-col tm-gap-sm" style="list-style:none;padding:0;margin:0">
                    <?php foreach ($events as $event): ?>
                        <li class="tm-flex-between tm-text-sm">
                            <span class="tm-badge tm-badge--outline"><?= e((string) $event['type']) ?></span>
                            <span class="tm-text-secondary"><?= e(date('d/m H:i', strtotime((string) $event['created_at']))) ?></span>
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
    if (!target || typeof QRCode === 'undefined') return;
    new QRCode(target, {
        text: target.getAttribute('data-url'),
        width: 196,
        height: 196,
        colorDark: '#17212B',
        colorLight: '#FFFFFF',
        correctLevel: QRCode.CorrectLevel.M
    });
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
