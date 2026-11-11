<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Proteção CSRF baseada em token de sessão.
 * Para a API interna, aceita o token via cabeçalho X-CSRF-Token ou campo "csrf_token".
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        Session::start();

        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    public static function check(?string $token = null): bool
    {
        Session::start();

        $expected = $_SESSION[self::KEY] ?? '';
        $token ??= $_SERVER['HTTP_X_CSRF_TOKEN'] ?? Request::string('csrf_token');

        if ($expected === '' || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }

    public static function validate(?string $token = null): void
    {
        if (!self::check($token)) {
            Response::error('Sessão expirada ou token de segurança inválido. Recarregue a página.', null, 419);
        }
    }
}
