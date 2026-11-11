<?php
/**
 * Layout do painel administrativo (shell com sidebar).
 *
 * @var string      $title
 * @var string      $page      Item ativo do menu
 * @var string      $heading   Título exibido na barra superior
 * @var string      $content
 * @var array       $scripts
 * @var string|null $actions   HTML das ações da barra superior
 */
use App\Core\Csrf;

$appName = (string) config('app.name', 'TalentMatch');
$pageTitle = isset($title) ? ($title . ' · ' . $appName) : $appName;
$current = $page ?? '';
$extraScripts = $scripts ?? [];
$heading = $heading ?? '';
$actions = $actions ?? '';
$adminName = (string) \App\Middleware\Auth::name();

$menu = [
    ['label' => 'Visão geral', 'url' => base_url('admin/dashboard.php'), 'page' => 'dashboard'],
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#17212B">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/game.css')) ?>">
</head>
<body>
<div class="tm-shell">

    <aside class="tm-sidebar" aria-label="Menu do painel">
        <a class="tm-sidebar__brand" href="<?= e(base_url('admin/dashboard.php')) ?>">
            <span class="tm-brand__mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v6"></path>
                    <path d="M7 9h10l-1.2 8.2a2 2 0 0 1-2 1.8h-3.6a2 2 0 0 1-2-1.8L7 9Z"></path>
                    <circle cx="12" cy="4" r="1.4"></circle>
                </svg>
            </span>
            <?= e($appName) ?>
        </a>

        <nav class="tm-sidebar__nav">
            <span class="tm-sidebar__section">Organização</span>
            <?php foreach ($menu as $item): ?>
                <a class="tm-sidebar__link <?= $current === $item['page'] ? 'is-active' : '' ?>"
                   href="<?= e($item['url']) ?>">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>

            <span class="tm-sidebar__section">Sistema</span>
            <a class="tm-sidebar__link" href="<?= e(base_url('/')) ?>">Ver o site público</a>
            <a class="tm-sidebar__link" href="<?= e(base_url('admin/?logout=1')) ?>">Sair</a>
        </nav>

        <div class="tm-sidebar__footer">
            <div class="tm-text-sm" style="color:rgba(247,250,249,.55)">Conectado como</div>
            <div style="color:#fff;font-weight:600"><?= e($adminName) ?></div>
        </div>
    </aside>

    <div class="tm-main">
        <header class="tm-appbar">
            <div class="tm-flex-center tm-gap-sm">
                <button type="button" class="tm-btn tm-btn--secondary tm-btn--sm tm-sidebar-toggle" data-tm-shell-toggle aria-label="Abrir menu">
                    ☰
                </button>
                <h1 class="tm-appbar__title"><?= e($heading) ?></h1>
            </div>
            <div class="tm-flex-center tm-gap-sm">
                <?= $actions ?>
            </div>
        </header>

        <div class="tm-content">
            <?= $content ?>
        </div>
    </div>
</div>

<script src="<?= e(asset('vendor/bootstrap.bundle.min.js')) ?>" defer></script>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<script src="<?= e(asset('js/game.js')) ?>" defer></script>
<?php foreach ($extraScripts as $script): ?>
<script src="<?= e(asset('js/' . ltrim((string) $script, '/'))) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
