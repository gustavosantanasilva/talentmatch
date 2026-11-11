<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Carregador mínimo de variáveis de ambiente a partir de um arquivo .env.
 * Não depende de bibliotecas externas e funciona em hospedagem compartilhada.
 */
final class Env
{
    /** @var array<string,string> */
    private static array $vars = [];

    private static bool $loaded = false;

    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $separator = strpos($line, '=');
            if ($separator === false) {
                continue;
            }

            $key = trim(substr($line, 0, $separator));
            $value = trim(substr($line, $separator + 1));

            if ($key === '') {
                continue;
            }

            // Remove comentário inline apenas quando precedido de espaço (evita
            // cortar valores que contenham "#", como chaves de API).
            if (preg_match('/\s+#/', $value) === 1) {
                $value = preg_split('/\s+#/', $value, 2)[0];
                $value = trim($value);
            }

            // Remove aspas envolventes.
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            self::$vars[$key] = $value;

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
            $_ENV[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$vars)) {
            return self::$vars[$key];
        }

        $value = getenv($key);

        return $value === false ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return ($value === null || $value === '') ? $default : (int) $value;
    }

    public static function isLoaded(): bool
    {
        return self::$loaded;
    }
}
