<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Renderizador de views simples com suporte a layouts.
 *
 * Ex.: View::render('home', ['title' => '...'], 'layouts/base');
 */
final class View
{
    /**
     * @param array<string,mixed> $data
     */
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        $content = self::capture($view, $data);

        if ($layout === null) {
            echo $content;

            return;
        }

        echo self::capture($layout, array_merge($data, ['content' => $content]));
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function capture(string $view, array $data = []): string
    {
        $file = APP_PATH . '/views/' . $view . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View não encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }

    /**
     * Escapa texto para saída segura em HTML (anti-XSS).
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
