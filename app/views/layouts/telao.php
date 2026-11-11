<?php
/**
 * Layout do telão — tela cheia, sem chrome de administração.
 *
 * @var string $title
 * @var string $content
 */
use App\Core\Csrf;

$appName = (string) config('app.name', 'TalentMatch');
$pageTitle = isset($title) ? ($title . ' · ' . $appName) : $appName;
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/game.css')) ?>">
</head>
<body class="tm-body-telao">
    <?= $content ?>
    <script src="<?= e(asset('vendor/bootstrap.bundle.min.js')) ?>" defer></script>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
    <script src="<?= e(asset('js/game.js')) ?>" defer></script>
</body>
</html>
