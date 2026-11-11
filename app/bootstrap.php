<?php

declare(strict_types=1);

/**
 * Bootstrap do TalentMatch.
 *
 * Responsável por:
 *  - definir caminhos do projeto;
 *  - registrar o autoloader (PSR-4: App\ => app/);
 *  - carregar variáveis de ambiente e configuração;
 *  - configurar timezone, tratamento de erros e sessão.
 *
 * Este arquivo fica FORA da raiz pública e é incluído por todos os entrypoints.
 */

use App\Core\Config;
use App\Core\Env;
use App\Core\Logger;
use App\Core\Response;
use App\Core\Session;

if (defined('TM_BOOTSTRAPPED')) {
    return;
}

define('TM_BOOTSTRAPPED', true);
define('TM_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('TM_VERSION', '0.3.0');

// ---------------------------------------------------------------------------
// Autoloader PSR-4: namespace App\ mapeado para o diretório app/
// Subdiretórios em minúsculas (app/core, app/services, ...).
// ---------------------------------------------------------------------------
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $parts = explode('\\', $relative);
    $className = array_pop($parts);
    $directory = $parts === [] ? '' : strtolower(implode('/', $parts)) . '/';
    $file = APP_PATH . '/' . $directory . $className . '.php';

    if (is_file($file)) {
        require $file;
    }
});

// ---------------------------------------------------------------------------
// Helpers globais
// ---------------------------------------------------------------------------
require APP_PATH . '/helpers/functions.php';

// ---------------------------------------------------------------------------
// Ambiente e configuração
// ---------------------------------------------------------------------------
Env::load(BASE_PATH . '/.env');
Config::load(APP_PATH . '/config');

date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));

$debug = (bool) Config::get('app.debug', false);

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');

// ---------------------------------------------------------------------------
// Tratamento global de erros e exceções — nunca expõe detalhes ao navegador
// ---------------------------------------------------------------------------
set_exception_handler(static function (Throwable $exception): void {
    Logger::error($exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ]);

    $debug = (bool) Config::get('app.debug', false);
    $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')
        || str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
        || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');

    if ($isApi) {
        Response::error(
            'Ocorreu um erro interno no servidor. Tente novamente.',
            $debug ? ['detail' => $exception->getMessage()] : null,
            500
        );
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    $message = $debug ? $exception->getMessage() : 'Ocorreu um erro interno no servidor.';
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Erro — TalentMatch</title></head><body style="font-family:system-ui;background:#F7F9F8;color:#17212B;padding:3rem;text-align:center">'
        . '<h1 style="color:#087F5B">Ops! Algo deu errado.</h1>'
        . '<p>' . htmlspecialchars($message, ENT_QUOTES) . '</p>'
        . '<p><a href="/" style="color:#087F5B">Voltar ao início</a></p>'
        . '</body></html>';
    exit;
});

set_error_handler(static function (int $severity, string $message, string $file = '', int $line = 0): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

// ---------------------------------------------------------------------------
// Sessão segura (ignorada em CLI para permitir testes de linha de comando)
// ---------------------------------------------------------------------------
if (PHP_SAPI !== 'cli') {
    Session::start();
}
