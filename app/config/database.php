<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Configuração do banco de dados MySQL/MariaDB.
 *
 * Este arquivo NÃO contém segredos: ele apenas lê as variáveis do arquivo .env,
 * que fica fora da raiz pública e não é versionado.
 */
return [
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => Env::int('DB_PORT', 3306),
    'database' => Env::get('DB_DATABASE', 'talentmatch'),
    'username' => Env::get('DB_USERNAME', ''),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
];
