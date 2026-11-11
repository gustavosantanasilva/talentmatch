<?php
/**
 * Visão geral do painel: estatísticas e lista de salas.
 *
 * @var array $stats
 * @var array $rooms
 * @var array $flash   ['type' => 'success|danger', 'message' => '...']
 */
use App\Core\Csrf;
use App\Services\RoomService;

$stats = $stats ?? ['total' => 0, 'waiting' => 0, 'active' => 0, 'finished' => 0, 'participants' => 0];
$rooms = $rooms ?? [];
$flash = $flash ?? null;

$cards = [
    ['label' => 'Salas criadas', 'value' => $stats['total'], 'icon' => '🎮', 'color' => '#1368CE'],
    ['label' => 'Sala de espera', 'value' => $stats['waiting'], 'icon' => '⏳', 'color' => '#FFA602'],
    ['label' => 'Em andamento', 'value' => $stats['active'], 'icon' => '⚡', 'color' => '#E21B3C'],
    ['label' => 'Participantes', 'value' => $stats['participants'], 'icon' => '👥', 'color' => '#26890C'],
];
?>

<?php if ($flash !== null): ?>
    <div class="gk-alert gk-alert--<?= e($flash['type'] === 'danger' ? 'danger' : $flash['type']) ?> gk-mb-3" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="tm-grid tm-grid-4 gk-mb-3">
    <?php foreach ($cards as $card): ?>
        <div class="gk-card gk-anim-pop">
            <span class="gk-step__num gk-mb-2" style="background:<?= e($card['color']) ?>"><?= e($card['icon']) ?></span>
            <span class="gk-stat__label"><?= e($card['label']) ?></span>
            <span class="gk-stat__value"><?= (int) $card['value'] ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="gk-split-2">
    <div class="gk-card gk-anim-slide">
        <div class="gk-card__head">
            <div>
                <h2 class="gk-card__title">Suas salas</h2>
                <p class="gk-card__subtitle gk-mb-0">Partidas criadas por este organizador.</p>
            </div>
        </div>

        <?php if ($rooms === []): ?>
            <div class="gk-alert gk-alert--info">
                Nenhuma sala criada ainda. Use o botão <strong>Criar sala</strong> para começar.
            </div>
        <?php else: ?>
            <div class="tm-table-scroll">
                <table class="tm-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Estado</th>
                            <th>Participantes</th>
                            <th>Vagas</th>
                            <th>Criada em</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><strong class="gk-mono"><?= e((string) $room['code']) ?></strong></td>
                                <td>
                                    <span class="gk-badge <?= e(RoomService::statusBadgeClass((string) $room['status'])) ?>">
                                        <?= e(RoomService::statusLabel((string) $room['status'])) ?>
                                    </span>
                                </td>
                                <td><?= (int) ($room['participants_count'] ?? 0) ?></td>
                                <td><?= (int) ($room['jobs_count'] ?? 0) ?></td>
                                <td class="gk-text-muted gk-text-sm">
                                    <?= e(date('d/m/Y H:i', strtotime((string) $room['created_at']))) ?>
                                </td>
                                <td style="text-align:right">
                                    <a class="gk-btn gk-btn--primary gk-btn--sm"
                                       href="<?= e(base_url('admin/sala.php?id=' . (int) $room['id'])) ?>">
                                        Abrir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="gk-flex-col gk-gap">
        <div class="gk-card gk-anim-slide gk-delay-1">
            <h2 class="gk-card__title gk-mb-1">Nova partida</h2>
            <p class="gk-text-muted gk-text-sm gk-mb-2">
                Cria uma sala com código e QR Code para os participantes entrarem.
            </p>
            <form method="post" action="<?= e(base_url('admin/dashboard.php')) ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" value="create_room">
                <button type="submit" class="gk-btn gk-btn--primary gk-btn--lg gk-btn--block">+ Criar sala</button>
            </form>
        </div>

        <div class="gk-card gk-anim-slide gk-delay-2">
            <h2 class="gk-card__title gk-mb-2">Como funciona</h2>
            <ol class="gk-text-muted gk-text-sm" style="padding-left:1.1rem;margin:0">
                <li>Crie a sala e projete o QR Code no telão.</li>
                <li>Os participantes entram e montam o perfil.</li>
                <li>A IA gera até quatro vagas fictícias.</li>
                <li>O sistema calcula a compatibilidade e o ranking.</li>
            </ol>
        </div>
    </div>
</div>
