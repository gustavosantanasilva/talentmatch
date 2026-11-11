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
    ['label' => 'Salas criadas', 'value' => $stats['total'], 'icon' => 'grid'],
    ['label' => 'Sala de espera', 'value' => $stats['waiting'], 'icon' => 'clock'],
    ['label' => 'Em andamento', 'value' => $stats['active'], 'icon' => 'play'],
    ['label' => 'Participantes', 'value' => $stats['participants'], 'icon' => 'users'],
];
?>

<?php if ($flash !== null): ?>
    <div class="tm-alert tm-alert--<?= e($flash['type']) ?> tm-mb-3" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="tm-grid tm-grid-4 tm-mb-3">
    <?php foreach ($cards as $card): ?>
        <div class="tm-card tm-reveal">
            <div class="tm-stat">
                <span class="tm-stat__label"><?= e($card['label']) ?></span>
                <span class="tm-stat__value"><?= (int) $card['value'] ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="tm-grid" style="grid-template-columns: 1.6fr 1fr; gap:1.25rem">
    <div class="tm-card">
        <div class="tm-card__header">
            <div>
                <h2 class="tm-card__title">Suas salas</h2>
                <p class="tm-card__subtitle">Partidas criadas por este organizador.</p>
            </div>
        </div>

        <?php if ($rooms === []): ?>
            <div class="tm-alert tm-alert--info">
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
                                <td><strong class="tm-mono"><?= e((string) $room['code']) ?></strong></td>
                                <td>
                                    <span class="tm-badge <?= e(RoomService::statusBadgeClass((string) $room['status'])) ?>">
                                        <?= e(RoomService::statusLabel((string) $room['status'])) ?>
                                    </span>
                                </td>
                                <td><?= (int) ($room['participants_count'] ?? 0) ?></td>
                                <td><?= (int) ($room['jobs_count'] ?? 0) ?></td>
                                <td class="tm-text-secondary tm-text-sm">
                                    <?= e(date('d/m/Y H:i', strtotime((string) $room['created_at']))) ?>
                                </td>
                                <td style="text-align:right">
                                    <a class="tm-btn tm-btn--secondary tm-btn--sm"
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

    <div class="tm-flex-col tm-gap">
        <div class="tm-card">
            <h2 class="tm-card__title tm-mb-2">Nova partida</h2>
            <p class="tm-text-secondary tm-text-sm">
                Cria uma sala com código e QR Code para os participantes entrarem.
            </p>
            <form method="post" action="<?= e(base_url('admin/dashboard.php')) ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" value="create_room">
                <button type="submit" class="tm-btn tm-btn--primary tm-btn--block">Criar sala</button>
            </form>
        </div>

        <div class="tm-card">
            <h2 class="tm-card__title tm-mb-2">Como funciona</h2>
            <ol class="tm-text-secondary tm-text-sm" style="padding-left:1.1rem;margin:0">
                <li>Crie a sala e projete o QR Code no telão.</li>
                <li>Os participantes entram e montam o perfil.</li>
                <li>A IA gera até quatro vagas fictícias.</li>
                <li>O sistema calcula a compatibilidade e o ranking.</li>
            </ol>
        </div>
    </div>
</div>

<style>
    @media (max-width: 992px) {
        .tm-grid[style*="1.6fr"] { grid-template-columns: 1fr !important; }
    }
</style>
