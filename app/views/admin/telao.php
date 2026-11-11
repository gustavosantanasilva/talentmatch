<?php
/**
 * Telão da sala — QR Code grande e participantes ao vivo em tela cheia.
 *
 * @var array  $room
 * @var string $joinUrl
 * @var array  $participants
 */
use App\Models\Participant;
use App\Services\RoomService;

$participants = $participants ?? [];
$status = (string) $room['status'];
$tilePalette = ['#1368CE', '#E21B3C', '#FFA602', '#26890C', '#864CBF', '#0D9488'];
?>

<div class="gk-telao">
    <header class="gk-telao__head">
        <div class="gk-telao__brand">
            <span class="tm-brand__mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v6"></path>
                    <path d="M7 9h10l-1.2 8.2a2 2 0 0 1-2 1.8h-3.6a2 2 0 0 1-2-1.8L7 9Z"></path>
                    <circle cx="12" cy="4" r="1.4"></circle>
                </svg>
            </span>
            TalentMatch
        </div>
        <div class="gk-telao__meta">
            <span class="gk-badge gk-badge--white"><?= e(RoomService::statusLabel($status)) ?></span>
            <a class="gk-btn gk-btn--white gk-btn--sm" href="<?= e(base_url('admin/sala.php?id=' . (int) $room['id'])) ?>">Painel</a>
        </div>
    </header>

    <div class="gk-telao__grid">
        <section class="gk-card gk-card--pad-lg gk-center gk-anim-pop">
            <span class="gk-eyebrow">Entre na sala</span>
            <div class="gk-room-code gk-mt-1 gk-mb-2"><?= e((string) $room['code']) ?></div>

            <div id="qrcode" class="gk-qr gk-qr--lg" data-url="<?= e($joinUrl) ?>" role="img"
                 aria-label="QR Code de entrada da sala <?= e((string) $room['code']) ?>"></div>

            <div class="gk-link-box gk-mt-3" style="text-align:left">
                <code class="gk-mono"><?= e($joinUrl) ?></code>
            </div>

            <div class="gk-count gk-mt-3" style="justify-content:center">
                <span class="gk-count__value" data-gk-count><?= count($participants) ?></span>
                <span class="gk-count__label"><?= count($participants) === 1 ? 'participante na sala' : 'participantes na sala' ?></span>
            </div>
        </section>

        <section class="gk-card gk-card--pad-lg gk-anim-slide gk-delay-1">
            <div class="gk-card__head">
                <div>
                    <h2 class="gk-card__title">Jogadores</h2>
                    <p class="gk-card__subtitle gk-mb-0">A lista atualiza sozinha.</p>
                </div>
                <span class="gk-badge gk-badge--green" data-gk-status><?= e(RoomService::statusLabel($status)) ?></span>
            </div>

            <ul class="gk-players gk-players--telao" data-gk-players>
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
                        <span>Ninguém entrou ainda.<br>Mostre o QR Code para a turma!</span>
                    </li>
                <?php endif; ?>
            </ul>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var target = document.getElementById('qrcode');
    if (target && typeof QRCode !== 'undefined') {
        new QRCode(target, {
            text: target.getAttribute('data-url'),
            width: 320,
            height: 320,
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
</script>
