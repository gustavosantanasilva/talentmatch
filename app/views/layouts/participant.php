<?php
/**
 * Layout do participante — cabeçalho enxuto e conteúdo centralizado.
 *
 * @var string      $title
 * @var string      $content
 * @var array       $scripts
 * @var string|null $roomCode  Código da sala exibido no topo (opcional)
 * @var string      $bodyClass
 */
use App\Core\Csrf;

$appName = (string) config('app.name', 'TalentMatch');
$pageTitle = isset($title) ? ($title . ' · ' . $appName) : $appName;
$extraScripts = $scripts ?? [];
$roomCode = $roomCode ?? null;
$bodyClass = $bodyClass ?? '';
$participantToken = \App\Middleware\ParticipantAuth::token();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#087F5B">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <?php if ($participantToken !== ''): ?>
    <meta name="participant-token" content="<?= e($participantToken) ?>">
    <?php endif; ?>
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/game.css')) ?>">
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

        <?php if ($roomCode !== null): ?>
            <span class="tm-badge tm-badge--outline">Sala <strong class="tm-mono"><?= e($roomCode) ?></strong></span>
        <?php endif; ?>
    </div>
</header>

<main id="conteudo" class="tm-participant">
    <div class="tm-container tm-container-wide">
        <?= $content ?>
    </div>
</main>

<script src="<?= e(asset('vendor/bootstrap.bundle.min.js')) ?>" defer></script>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<script src="<?= e(asset('js/game.js')) ?>" defer></script>
<?php foreach ($extraScripts as $script): ?>
<script src="<?= e(asset('js/' . ltrim((string) $script, '/'))) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
