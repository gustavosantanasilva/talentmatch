<?php
/**
 * Entrada do participante por QR Code.
 *
 * @var array|null  $room              Sala encontrada (ou null)
 * @var string      $mode              'form' | 'notfound' | 'closed'
 * @var string      $error
 * @var string      $nickname
 * @var int         $participantsCount
 */
use App\Core\Csrf;
use App\Services\RoomService;

$room = $room ?? null;
$mode = $mode ?? 'notfound';
$error = $error ?? '';
$nickname = $nickname ?? '';
$participantsCount = (int) ($participantsCount ?? 0);
?>

<?php if ($mode === 'notfound'): ?>
    <div class="tm-auth__card tm-reveal" style="max-width:520px;margin:2rem auto">
        <div class="tm-text-center">
            <h1 style="font-size:1.5rem">Sala não encontrada</h1>
            <p class="tm-text-secondary">
                O código informado não corresponde a nenhuma sala ativa.
                Confira o QR Code ou peça o código correto ao organizador.
            </p>
            <a class="tm-btn tm-btn--primary tm-mt-2" href="<?= e(base_url('/')) ?>">Voltar ao início</a>
        </div>
    </div>
<?php elseif ($mode === 'closed'): ?>
    <div class="tm-auth__card tm-reveal" style="max-width:520px;margin:2rem auto">
        <div class="tm-text-center">
            <h1 style="font-size:1.5rem">A partida já começou</h1>
            <p class="tm-text-secondary">
                A sala <strong class="tm-mono"><?= e((string) $room['code']) ?></strong> não está
                mais aceitando novos participantes. Fale com o organizador.
            </p>
            <a class="tm-btn tm-btn--primary tm-mt-2" href="<?= e(base_url('/')) ?>">Voltar ao início</a>
        </div>
    </div>
<?php else: ?>
    <div class="tm-auth__card tm-reveal" style="max-width:520px;margin:2rem auto">
        <div class="tm-text-center tm-mb-3">
            <div class="tm-text-sm tm-text-secondary">Você está entrando na sala</div>
            <div class="tm-room-code"><?= e((string) $room['code']) ?></div>
            <span class="tm-badge <?= e(RoomService::statusBadgeClass((string) $room['status'])) ?>">
                <?= e(RoomService::statusLabel((string) $room['status'])) ?>
            </span>
            <p class="tm-text-secondary tm-text-sm tm-mt-2 tm-mb-0">
                <?= (int) $participantsCount ?>
                <?= $participantsCount === 1 ? 'participante já entrou' : 'participantes já entraram' ?>.
            </p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="tm-alert tm-alert--danger tm-mb-2" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(base_url('entrar.php?room=' . rawurlencode((string) $room['code']))) ?>" novalidate>
            <?= Csrf::field() ?>

            <div class="tm-field">
                <label class="tm-label" for="nickname">Seu apelido</label>
                <input class="tm-input" type="text" id="nickname" name="nickname"
                       value="<?= e($nickname) ?>" required autofocus
                       minlength="2" maxlength="30" autocomplete="off"
                       placeholder="Como você quer aparecer">
                <div class="tm-hint">De 2 a 30 caracteres. Precisa ser único nesta sala.</div>
            </div>

            <button type="submit" class="tm-btn tm-btn--primary tm-btn--block tm-btn--lg">Entrar na sala</button>
        </form>

        <p class="tm-text-center tm-text-secondary tm-text-sm tm-mt-3 tm-mb-0">
            Use apenas dados fictícios. Não informe informações pessoais reais.
        </p>
    </div>
<?php endif; ?>
