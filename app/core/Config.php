<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Repositório de configuração com acesso por notação de ponto.
 * Ex.: Config::get('app.name') ou Config::get('game.max_jobs').
 */
final class Config
{
    /** @var array<string,mixed> */
    private static array $items = [];

    public static function load(string $directory): void
    {
        $files = glob(rtrim($directory, '/') . '/*.php') ?: [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            $data = require $file;

            if (is_array($data)) {
                self::$items[$name] = $data;
            }
        }
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function set(string $key, $value): void
    {
        $segments = explode('.', $key);
        $ref = &self::$items;

        foreach ($segments as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }

        $ref = $value;
    }

    public static function has(string $key): bool
    {
        $sentinel = new \stdClass();

        return self::get($key, $sentinel) !== $sentinel;
    }
}
