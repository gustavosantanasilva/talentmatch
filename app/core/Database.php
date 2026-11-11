<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Conexão PDO única (singleton) com o MySQL/MariaDB.
 *
 * - Usa prepared statements reais (emulação desativada).
 * - Lança exceções em erros de SQL.
 * - Trabalha com utf8mb4.
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = Config::get('database');

        if (!is_array($config)) {
            throw new RuntimeException('Configuração de banco de dados ausente.');
        }

        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (int) ($config['port'] ?? 3306);
        $name = (string) ($config['database'] ?? '');
        $user = (string) ($config['username'] ?? '');
        $pass = (string) ($config['password'] ?? '');
        $charset = (string) ($config['charset'] ?? 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
        ];

        try {
            self::$connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $exception) {
            // Nunca expor credenciais ou stack trace ao navegador.
            throw new RuntimeException(
                'Falha ao conectar ao banco de dados. Verifique a configuração em .env.',
                (int) $exception->getCode(),
                $exception
            );
        }

        return self::$connection;
    }

    public static function isConnected(): bool
    {
        try {
            return self::connection() instanceof PDO;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Executa uma função dentro de uma transação.
     *
     * @template T
     * @param callable(PDO):T $callback
     * @return T
     */
    public static function transaction(callable $callback)
    {
        $pdo = self::connection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public static function reset(): void
    {
        self::$connection = null;
    }
}
