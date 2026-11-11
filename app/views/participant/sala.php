<?php
/**
 * Sala de espera do participante — lobby ao vivo com jogadores.
 *
 * @var array      $participant
 * @var array      $room
 * @var array      $participants
 * @var array|null $flash
 */
use App\Core\Csrf;
use App\Models\Participant;
use App\Services\RoomService;

$participants = $participants ?? [];
$flash = $flash ?? null;
$status = (string) $room['status'];

$tilePalette = ['#1368CE', '#E21B3C', '#FFA602', '#26890C', '#864CBF', '#0D9488'];

$banner = match ($status) {
    'WAITING' => ['icon' => '⏳', 'class' => 'gk-banner--blue', 'text' => 'Aguardando o organizador iniciar a partida.'],
    'PROFILES' => ['icon' => '⚡', 'class' => 'gk-banner--yellow', 'text' => 'O organizador iniciou! Monte seu perfil profissional.'],
    'GENERATING' => ['icon' => '🤖', 'class' => 'gk-banner--blue', 'text' => 'As vagas fictícias estão sendo geradas.'],
    'JOBS' => ['icon' => '💼', 'class' => 'gk-banner--green', 'text' => 'A apresentação das vagas está em andamento.'],
    'RESULTS' => ['icon' => '🏆', 'class' => 'gk-banner--ink', 'text' => 'Os resultados estão sendo calculados.'],
    'FINISHED' => ['icon' => '🎉', 'class' => 'gk-banner--ink', 'text' => 'A partida foi encerrada.'],
    default => ['icon' => '🎮', 'class' => 'gk-banner--blue', 'text' => 'Aguarde as instruções do organizador.'],
};

$myAvatar = (string) ($participant['avatar'] ?? '🙂');
$myColor = (string) ($participant['avatar_color'] ?? '#087F5B');
?>

<?php if ($flash !== null): ?>
    <div class="gk-alert gk-alert--<?= e($flash['type'] === 'danger' ? 'danger' : $flash['type']) ?> gk-mb-3" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="gk-lobby">
    <div class="gk-lobby__side">
        <div class="gk-card gk-card--pad-lg gk-center gk-anim-pop">
            <span class="gk-avatar gk-avatar--xl gk-mb-2"
                  data-gk-my-avatar
                  style="background:<?= e($myColor) ?>"><?= e($myAvatar) ?></span>
            <div class="gk-eyebrow">Bem-vindo(a)</div>
            <h1 class="gk-title-lg gk-mt-1 gk-mb-0" data-gk-my-name><?= e((string) $participant['nickname']) ?></h1>
            <span class="gk-badge <?= e(RoomService::statusBadgeClass($status)) ?> gk-mt-2">
                <?= e(Participant::statusLabel((string) $participant['status'])) ?>
            </span>
        </div>

        <div class="gk-banner <?= e($banner['class']) ?> gk-anim-slide">
            <span class="gk-banner__icon" aria-hidden="true"><?= e($banner['icon']) ?></span>
            <span><?= e($banner['text']) ?></span>
        </div>

        <?php if ($status === 'PROFILES'): ?>
            <div class="gk-card gk-anim-slide gk-delay-1">
                <h3 class="gk-card__title gk-mb-2">Sua próxima etapa</h3>
                <?php if ((string) $participant['status'] === 'READY'): ?>
                    <p class="gk-lead gk-mb-2">Perfil finalizado. Você pode revisar enquanto esta etapa durar.</p>
                    <a class="gk-btn gk-btn--primary gk-btn--block" href="<?= e(base_url('perfil.php')) ?>">Revisar meu perfil</a>
                <?php else: ?>
                    <p class="gk-lead gk-mb-2">Preencha habilidades, experiências e formações para participar.</p>
                    <a class="gk-btn gk-btn--primary gk-btn--lg gk-btn--block" href="<?= e(base_url('perfil.php')) ?>">Montar meu perfil</a>
                <?php endif; ?>
            </div>
        <?php elseif ($status === 'WAITING'): ?>
            <div class="gk-alert gk-alert--info">
                Mantenha esta página aberta. Ela é atualizada automaticamente quando o organizador iniciar.
            </div>
        <?php endif; ?>

        <div class="gk-card gk-anim-slide gk-delay-2">
            <h3 class="gk-card__title gk-mb-2">Editar nome e avatar</h3>
            <form data-gk-identity-form action="<?= e(base_url('api/participant/identity.php')) ?>" method="post">
                <div class="gk-field">
                    <label class="gk-label" for="edit-nickname">Apelido</label>
                    <input class="gk-input" type="text" id="edit-nickname" name="nickname"
                           value="<?= e((string) $participant['nickname']) ?>" required
                           minlength="2" maxlength="30" autocomplete="off">
                </div>
                <div class="gk-field">
                    <span class="gk-label">Avatar</span>
                    <div class="gk-avatar-picker" data-gk-avatar-picker>
                        <input type="hidden" name="avatar" value="<?= e($myAvatar) ?>">
                        <?php foreach ((array) config('app.avatars', []) as $option): ?>
                            <button type="button"
                                    class="gk-avatar-option <?= $option === $myAvatar ? 'is-selected' : '' ?>"
                                    data-value="<?= e($option) ?>"
                                    aria-label="Avatar <?= e($option) ?>"
                                    aria-pressed="<?= $option === $myAvatar ? 'true' : 'false' ?>"><?= e($option) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="gk-field">
                    <span class="gk-label">Cor</span>
                    <div class="gk-color-picker" data-gk-color-picker>
                        <input type="hidden" name="avatar_color" value="<?= e($myColor) ?>">
                        <?php foreach ((array) config('app.avatar_colors', []) as $color): ?>
                            <button type="button"
                                    class="gk-color-option <?= $color === $myColor ? 'is-selected' : '' ?>"
                                    style="--gk-color:<?= e($color) ?>"
                                    data-value="<?= e($color) ?>"
                                    aria-label="Cor <?= e($color) ?>"
                                    aria-pressed="<?= $color === $myColor ? 'true' : 'false' ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="gk-btn gk-btn--secondary gk-btn--block">Salvar alterações</button>
            </form>
        </div>

        <form method="post" action="<?= e(base_url('sala.php')) ?>"
              data-tm-confirm="Sair da sala? Você poderá entrar novamente enquanto a partida não começar.">
            <?= Csrf::field() ?>
            <input type="hidden" name="action" value="leave">
            <button type="submit" class="gk-btn gk-btn--ghost gk-btn--block">Sair da sala</button>
        </form>
    </div>

    <div class="gk-lobby__main">
        <div class="gk-card gk-card--pad-lg gk-anim-slide">
            <div class="gk-card__head">
                <div>
                    <h2 class="gk-card__title">Jogadores na sala</h2>
                    <p class="gk-card__subtitle gk-mb-0">
                        <strong data-gk-count><?= count($participants) ?></strong>
                        <?= count($participants) === 1 ? 'pessoa conectada' : 'pessoas conectadas' ?>
                    </p>
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
                        $isMe = (int) ($item['id'] ?? 0) === (int) $participant['id'];
                    ?>
                    <li class="gk-tile <?= $isMe ? 'gk-tile--me' : '' ?>" style="--gk-tile-bg:<?= e(strtoupper($color)) ?>">
                        <span class="gk-tile__avatar" aria-hidden="true"><?= e((string) ($item['avatar'] ?? '🙂')) ?></span>
                        <span class="gk-tile__name" title="<?= e((string) $item['nickname']) ?>"><?= e((string) $item['nickname']) ?></span>
                        <?php if ($isMe): ?>
                            <span class="gk-tile__me">você</span>
                        <?php else: ?>
                            <span class="gk-tile__status"><?= e(Participant::statusLabel((string) $item['status'])) ?></span>
                        <?php endif; ?>
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
            <h3 class="gk-card__title gk-mb-1">Código da sala</h3>
            <div class="gk-room-code"><?= e((string) $room['code']) ?></div>
            <p class="gk-text-muted gk-text-sm gk-mb-0 gk-mt-1">Compartilhe com quem ainda não entrou.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var code = <?= json_encode((string) $room['code']) ?>;
    var container = document.querySelector('[data-gk-players]');
    var count = document.querySelector('[data-gk-count]');
    var statusEl = document.querySelector('[data-gk-status]');

    if (!container) return;

    TM.watchParticipants({
        code: code,
        endpoint: <?= json_encode(base_url('api/room/participants.php')) ?>,
        container: container,
        count: count,
        status: statusEl,
        interval: 3000
    });
});

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
