<?php

declare(strict_types=1);

/**
 * TalentMatch — página inicial.
 * Verifica o ambiente e apresenta o projeto.
 */

use App\Core\Database;
use App\Core\View;

require dirname(__DIR__) . '/app/bootstrap.php';

$health = [
    'php' => PHP_VERSION,
    'database' => false,
    'message' => '',
];

try {
    $pdo = Database::connection();
    $pdo->query('SELECT 1');
    $health['database'] = true;
} catch (Throwable $exception) {
    $health['message'] = 'Não foi possível conectar usando as credenciais atuais.';
}

View::render('home', [
    'title' => 'Início',
    'page' => 'home',
    'health' => $health,
], 'layouts/base');
