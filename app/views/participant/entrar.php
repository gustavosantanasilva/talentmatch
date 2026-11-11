<?php
/**
 * Entrada do participante por QR Code — com apelido e avatar.
 *
 * @var array|null  $room              Sala encontrada (ou null)
 * @var string      $mode              'form' | 'notfound' | 'closed'
 * @var string      $error
 * @var string      $nickname
 * @var string      $avatar
 * @var string      $avatarColor
 * @var array       $avatars
 * @var array       $avatarColors
 * @var int         $participantsCount
 */
use App\Core\Csrf;
use App\Services\RoomService;

$room = $room ?? null;
$mode = $mode ?? 'notfound';
$error = $error ?? '';
$nickname = $nickname ?? '';
$avatar = $avatar ?? '🙂';
$avatarColor = $avatarColor ?? '#087F5B';
$avatars = $avatars ?? [];
$avatarColors = $avatarColors ?? [];
$participantsCount = (int) ($participantsCount ?? 0);
?>

<?php if ($mode === 'notfound' || $mode === 'closed'): ?>
    <div class="gk-card gk-card--pad-lg gk-anim-pop" style="max-width:520px;margin:2rem auto;text-align:center">
        <div class="gk-empty__icon" style="font-size:3rem"><?= $mode === 'closed' ? '⏳' : '🔎' ?></div>
        <h1 class="gk-title-lg gk-mt-2"><?= $mode === 'closed' ? 'A partida já começou' : 'Sala não encontrada' ?></h1>
        <p class="gk-lead gk-mb-3">
            <?php if ($mode === 'closed'): ?>
                A sala <strong class="gk-mono"><?= e((string) $room['code']) ?></strong> não está mais aceitando
                novos participantes. Fale com o organizador.
            <?php else: ?>
                O código informado não corresponde a nenhuma sala ativa.
                Confira o QR Code ou peça o código correto ao organizador.
            <?php endif; ?>
        </p>
        <a class="gk-btn gk-btn--primary" href="<?= e(base_url('/')) ?>">Voltar ao início</a>
    </div>
<?php else: ?>
    <div class="gk-join">
        <div class="gk-card gk-card--pad-lg gk-anim-pop" style="max-width:540px;margin:0 auto">
            <div class="gk-center gk-mb-3">
                <span class="gk-eyebrow">Você está entrando na sala</span>
                <div class="gk-room-code gk-mt-1"><?= e((string) $room['code']) ?></div>
                <span class="gk-badge <?= e(RoomService::statusBadgeClass((string) $room['status'])) ?> gk-mt-1">
                    <?= e(RoomService::statusLabel((string) $room['status'])) ?>
                </span>
                <p class="gk-text-muted gk-text-sm gk-mt-2 gk-mb-0">
                    <?= $participantsCount ?>
                    <?= $participantsCount === 1 ? 'participante já entrou' : 'participantes já entraram' ?>.
                </p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="gk-alert gk-alert--danger gk-mb-2" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(base_url('entrar.php?room=' . rawurlencode((string) $room['code']))) ?>" novalidate>
                <?= Csrf::field() ?>

                <div class="gk-center gk-mb-2">
                    <span class="gk-avatar gk-avatar--xl"
                          data-gk-avatar-preview
                          style="background:<?= e($avatarColor) ?>"><?= e($avatar) ?></span>
                </div>

                <div class="gk-field">
                    <span class="gk-label">Escolha seu avatar</span>
                    <div class="gk-avatar-picker" data-gk-avatar-picker>
                        <input type="hidden" name="avatar" value="<?= e($avatar) ?>">
                        <?php foreach ($avatars as $option): ?>
                            <button type="button"
                                    class="gk-avatar-option <?= $option === $avatar ? 'is-selected' : '' ?>"
                                    data-value="<?= e($option) ?>"
                                    aria-label="Avatar <?= e($option) ?>"
                                    aria-pressed="<?= $option === $avatar ? 'true' : 'false' ?>"><?= e($option) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="gk-field">
                    <span class="gk-label">Cor do avatar</span>
                    <div class="gk-color-picker" data-gk-color-picker>
                        <input type="hidden" name="avatar_color" value="<?= e($avatarColor) ?>">
                        <?php foreach ($avatarColors as $color): ?>
                            <button type="button"
                                    class="gk-color-option <?= $color === $avatarColor ? 'is-selected' : '' ?>"
                                    style="--gk-color:<?= e($color) ?>"
                                    data-value="<?= e($color) ?>"
                                    aria-label="Cor <?= e($color) ?>"
                                    aria-pressed="<?= $color === $avatarColor ? 'true' : 'false' ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="gk-field">
                    <label class="gk-label" for="nickname">Seu apelido</label>
                    <input class="gk-input" type="text" id="nickname" name="nickname"
                           value="<?= e($nickname) ?>" required autofocus
                           minlength="2" maxlength="30" autocomplete="off"
                           placeholder="Como você quer aparecer">
                    <div class="gk-hint">De 2 a 30 caracteres. Precisa ser único nesta sala.</div>
                </div>

                <button type="submit" class="gk-btn gk-btn--primary gk-btn--lg gk-btn--block gk-mt-1">
                    Entrar na sala
                </button>
            </form>

            <p class="gk-center gk-text-muted gk-text-sm gk-mt-3 gk-mb-0">
                Use apenas dados fictícios. Não informe informações pessoais reais.
            </p>
        </div>
    </div>
<?php endif; ?>
