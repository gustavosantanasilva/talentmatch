<?php

declare(strict_types=1);

/**
 * Executor de testes do TalentMatch.
 *
 * Uso:
 *   php tests/run.php
 *   php tests/run.php --only=database
 *
 * Requer o PHP 8.1+ e, para os testes de banco, um MySQL/MariaDB acessível
 * com as credenciais do arquivo .env.
 */

require dirname(__DIR__) . '/app/bootstrap.php';
require __DIR__ . '/framework.php';

/** @var TmTestRunner $runner */
$runner = $GLOBALS['tm_runner'];

echo "\n\033[1mTalentMatch — Suíte de testes\033[0m\n";
echo 'PHP ' . PHP_VERSION . ' · ' . date('d/m/Y H:i') . "\n";

$only = null;
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--only=')) {
        $only = substr($argument, 7);
    }
}

$cases = glob(__DIR__ . '/cases/*.php') ?: [];
sort($cases);

foreach ($cases as $case) {
    $name = basename($case, '.php');

    if ($only !== null && !str_contains($name, $only)) {
        continue;
    }

    require $case;
}

exit($runner->summary());
