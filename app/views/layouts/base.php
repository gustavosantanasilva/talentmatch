<?php
/**
 * Layout base — cabeçalho, navegação e rodapé compartilhados.
 *
 * Variáveis esperadas (opcionais):
 * @var string      $title
 * @var string      $page      Identificador do item ativo no menu
 * @var array       $nav       Itens de navegação: [['label','url','page','style'], ...]
 * @var string      $content   Conteúdo já renderizado
 * @var array       $scripts   Scripts adicionais (relativos a assets/js)
 * @var string      $bodyClass
 */
use App\Core\Csrf;

$appName = (string) config('app.name', 'TalentMatch');
$tagline = (string) config('app.tagline', '');
$pageTitle = isset($title) ? ($title . ' · ' . $appName) : $appName;
$current = $page ?? '';
$extraScripts = $scripts ?? [];
$bodyClass = $bodyClass ?? '';

// Somente páginas realmente disponíveis entram no menu (sem links quebrados).
$navItems = $nav ?? [
    ['label' => 'Início', 'url' => base_url('/'), 'page' => 'home', 'style' => 'link'],
    ['label' => 'Painel do organizador', 'url' => base_url('admin/'), 'page' => 'admin', 'style' => 'button'],
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#087F5B">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <meta name="description" content="<?= e($tagline) ?>">
    <title><?= e($pageTitle) ?></title>

    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="<?= e($bodyClass) ?>">

<header class="tm-topbar">
    <div class="tm-container tm-container-wide">
        <a class="tm-brand" href="<?= e(base_url('/')) ?>">
            <span class="tm-brand__mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v6"></path>
                    <path d="M7 9h10l-1.2 8.2a2 2 0 0 1-2 1.8h-3.6a2 2 0 0 1-2-1.8L7 9Z"></path>
                    <circle cx="12" cy="4" r="1.4"></circle>
                </svg>
            </span>
            <?= e($appName) ?>
        </a>

        <nav class="tm-nav tm-nav--desktop" aria-label="Navegação principal">
            <?php foreach ($navItems as $item): ?>
                <?php if (($item['style'] ?? 'link') === 'button'): ?>
                    <a href="<?= e($item['url']) ?>" class="tm-btn tm-btn--primary tm-btn--sm"><?= e($item['label']) ?></a>
                <?php else: ?>
                    <a href="<?= e($item['url']) ?>" class="<?= $current === ($item['page'] ?? '') ? 'is-active' : '' ?>"><?= e($item['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<main id="conteudo">
    <?= $content ?>
</main>

<footer class="tm-footer tm-mt-4">
    <div class="tm-container tm-container-wide tm-flex-between tm-wrap tm-gap">
        <div>
            <strong><?= e($appName) ?></strong> — <?= e($tagline) ?>
            <div class="tm-text-sm">Projeto educacional. Todas as empresas, vagas e salários são fictícios.</div>
        </div>
        <div class="tm-text-sm">
            <?= e($appName) ?> v<?= e(TM_VERSION) ?>
        </div>
    </div>
</footer>

<script src="<?= e(asset('vendor/bootstrap.bundle.min.js')) ?>" defer></script>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<?php foreach ($extraScripts as $script): ?>
<script src="<?= e(asset('js/' . ltrim((string) $script, '/'))) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
