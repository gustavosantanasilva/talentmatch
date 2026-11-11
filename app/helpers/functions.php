<?php

declare(strict_types=1);

use App\Core\Config;

if (!function_exists('config')) {
    /**
     * @param mixed $default
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('e')) {
    /**
     * Escapa uma string para saída segura em HTML.
     */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    /**
     * Monta uma URL absoluta a partir do APP_URL configurado.
     */
    function base_url(string $path = ''): string
    {
        $base = rtrim((string) config('app.url', ''), '/');
        $path = ltrim($path, '/');

        return $path === '' ? $base : $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    /**
     * URL de um recurso em public/assets.
     */
    function asset(string $path): string
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('normalize_skill')) {
    /**
     * Normaliza o nome de uma habilidade para comparação
     * (minúsculas, sem acentos, sem pontuação redundante).
     */
    function normalize_skill(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');

        $from = ['á', 'à', 'ã', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'õ', 'ô', 'ö', 'ú', 'ù', 'û', 'ü', 'ç', 'ñ'];
        $to   = ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c', 'n'];
        $value = str_replace($from, $to, $value);

        $value = preg_replace('/[^a-z0-9+#. ]+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text(?string $text, int $limit = 160): string
    {
        $text = trim((string) $text);

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
    }
}

if (!function_exists('json_array')) {
    /**
     * Decodifica um campo JSON de forma segura, sempre retornando array.
     *
     * @return array<int|string,mixed>
     */
    function json_array(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
