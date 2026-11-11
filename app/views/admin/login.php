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
<div class="tm-auth__card tm-reveal">
    <div class="tm-text-center tm-mb-3">
        <span class="tm-brand__mark" style="margin:0 auto 1rem;width:52px;height:52px" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:28px;height:28px">
                <path d="M12 3v6"></path>
                <path d="M7 9h10l-1.2 8.2a2 2 0 0 1-2 1.8h-3.6a2 2 0 0 1-2-1.8L7 9Z"></path>
                <circle cx="12" cy="4" r="1.4"></circle>
            </svg>
        </span>
        <h1 style="font-size:1.4rem;margin-bottom:.25rem">Painel do organizador</h1>
        <p class="tm-text-secondary tm-mb-0 tm-text-sm">Entre para criar e conduzir as partidas.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="tm-alert tm-alert--danger tm-mb-2" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(base_url('admin/')) ?>" autocomplete="off" novalidate>
        <?= Csrf::field() ?>
        <input type="hidden" name="action" value="login">

        <div class="tm-field">
            <label class="tm-label" for="username">Usuário</label>
            <input class="tm-input" type="text" id="username" name="username"
                   value="<?= e($username) ?>" required autofocus
                   maxlength="50" placeholder="seu usuário">
        </div>

        <div class="tm-field">
            <label class="tm-label" for="password">Senha</label>
            <input class="tm-input" type="password" id="password" name="password"
                   required maxlength="100" placeholder="sua senha">
        </div>

        <button type="submit" class="tm-btn tm-btn--primary tm-btn--block tm-btn--lg">Entrar</button>
    </form>

    <p class="tm-text-center tm-text-secondary tm-text-sm tm-mt-3 tm-mb-0">
        <a href="<?= e(base_url('/')) ?>">← Voltar ao início</a>
    </p>
</div>
