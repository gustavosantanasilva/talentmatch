<?php
/**
 * Tela de login do painel administrativo.
 *
 * @var string $error
 * @var string $username
 */
use App\Core\Csrf;

$error = $error ?? '';
$username = $username ?? '';
?>
<div class="tm-auth__card gk-anim-pop">
    <div class="gk-center gk-mb-3">
        <span class="tm-brand__mark" style="margin:0 auto 1rem;width:60px;height:60px;background:var(--gk-stage)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px">
                <path d="M12 3v6"></path>
                <path d="M7 9h10l-1.2 8.2a2 2 0 0 1-2 1.8h-3.6a2 2 0 0 1-2-1.8L7 9Z"></path>
                <circle cx="12" cy="4" r="1.4"></circle>
            </svg>
        </span>
        <h1 class="gk-title-lg gk-mt-2 gk-mb-1">Painel do organizador</h1>
        <p class="gk-text-muted gk-text-sm gk-mb-0">Entre para criar e conduzir as partidas.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="gk-alert gk-alert--danger gk-mb-2" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(base_url('admin/')) ?>" autocomplete="off" novalidate>
        <?= Csrf::field() ?>
        <input type="hidden" name="action" value="login">

        <div class="gk-field">
            <label class="gk-label" for="username">Usuário</label>
            <input class="gk-input" type="text" id="username" name="username"
                   value="<?= e($username) ?>" required autofocus
                   maxlength="50" placeholder="seu usuário">
        </div>

        <div class="gk-field">
            <label class="gk-label" for="password">Senha</label>
            <input class="gk-input" type="password" id="password" name="password"
                   required maxlength="100" placeholder="sua senha">
        </div>

        <button type="submit" class="gk-btn gk-btn--primary gk-btn--lg gk-btn--block">Entrar</button>
    </form>

    <p class="gk-center gk-text-muted gk-text-sm gk-mt-3 gk-mb-0">
        <a href="<?= e(base_url('/')) ?>">← Voltar ao início</a>
    </p>
</div>
