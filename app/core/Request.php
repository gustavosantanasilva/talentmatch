<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Abstração de requisição HTTP (JSON ou formulário).
 */
final class Request
{
    /**
     * @return array<string,mixed>
     */
    public static function all(): array
    {
        $method = self::method();

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (str_contains($contentType, 'application/json')) {
                $raw = file_get_contents('php://input') ?: '';
                $decoded = json_decode($raw, true);

                if (is_array($decoded)) {
                    return array_merge($_GET, $decoded);
                }

                return $_GET;
            }

            return array_merge($_GET, $_POST);
        }

        return $_GET;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function input(string $key, $default = null)
    {
        $data = self::all();

        return $data[$key] ?? $default;
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::input($key, $default);

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::input($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @return array<int,mixed>
     */
    public static function array(string $key): array
    {
        $value = self::input($key, []);

        return is_array($value) ? $value : [];
    }

    public static function has(string $key): bool
    {
        $data = self::all();

        return array_key_exists($key, $data);
    }

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public static function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public static function isAjax(): bool
    {
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));

        return $requestedWith === 'xmlhttprequest' || str_contains($accept, 'application/json');
    }
}
