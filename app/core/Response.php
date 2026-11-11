<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Respostas JSON padronizadas da API interna.
 *
 * Sucesso: { "success": true,  "message": "...", "data": {...} }
 * Erro:    { "success": false, "message": "...", "data": null }
 *
 * Nunca inclui stack traces nem informações sensíveis.
 */
final class Response
{
    public static function success(mixed $data = null, string $message = 'Operação realizada com sucesso.', int $status = 200): never
    {
        self::send(true, $message, $data, $status);
    }

    public static function error(string $message = 'Não foi possível realizar a operação.', mixed $data = null, int $status = 400): never
    {
        self::send(false, $message, $data, $status);
    }

    /**
     * @param array<int,string> $errors
     */
    public static function validationError(array $errors, string $message = 'Dados inválidos.', int $status = 422): never
    {
        self::send(false, $message, ['errors' => $errors], $status);
    }

    private static function send(bool $success, string $message, mixed $data, int $status): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }

        $payload = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
