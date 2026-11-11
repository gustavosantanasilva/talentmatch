<?php

declare(strict_types=1);

/**
 * Verificação de sintaxe de todos os arquivos PHP do projeto.
 *
 * Uso: php tests/lint.php
 * Retorna código de saída 1 quando encontra algum erro.
 */

$root = dirname(__DIR__);
$binary = PHP_BINARY;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$files = [];
$ignored = ['/vendor/', '/.git/', '/storage/logs/', '/storage/cache/'];

foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();

    foreach ($ignored as $fragment) {
        if (str_contains($path, $fragment)) {
            continue 2;
        }
    }

    $files[] = $path;
}

sort($files);

$errors = 0;
$checked = 0;

echo "\nVerificação de sintaxe PHP (" . count($files) . " arquivos)\n";
echo str_repeat('─', 64) . "\n";

foreach ($files as $file) {
    $relative = str_replace($root . '/', '', $file);
    $output = [];
    $exitCode = 0;

    exec(escapeshellarg($binary) . ' -l ' . escapeshellarg($file) . ' 2>&1', $output, $exitCode);
    $checked++;

    if ($exitCode !== 0) {
        $errors++;
        echo "  \033[31m✗\033[0m {$relative}\n";
        echo '      ' . trim(implode(' ', $output)) . "\n";
    }
}

echo str_repeat('─', 64) . "\n";
printf("  Arquivos verificados: %d  |  \033[31mErros: %d\033[0m\n\n", $checked, $errors);

exit($errors === 0 ? 0 : 1);
