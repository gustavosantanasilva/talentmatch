<?php

declare(strict_types=1);

/**
 * POST /api/room/join.php
 * Entra em uma sala com apelido e avatar e devolve o token do participante.
 *
 * Corpo: { "room": "ABC123", "nickname": "Fulano", "avatar": "🦊", "avatar_color": "#087F5B" }
 * Exige token CSRF (cabeçalho X-CSRF-Token ou campo csrf_token).
 */

use App\Core\Csrf;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\ParticipantAuth;
use App\Models\Room;
use App\Services\ParticipantService;

require dirname(__DIR__, 3) . '/app/bootstrap.php';

if (!Request::isPost()) {
    Response::error('Método não permitido.', null, 405);
}

Csrf::validate();

$code = strtoupper(Request::string('room'));
$room = $code !== '' ? Room::findByCode($code) : null;

if ($room === null) {
    Response::error('Sala não encontrada.', null, 404);
}

try {
    $result = ParticipantService::join(
        $room,
        Request::string('nickname'),
        Request::string('avatar'),
        Request::string('avatar_color')
    );
} catch (Throwable $exception) {
    Logger::error('Falha ao entrar na sala via API', ['message' => $exception->getMessage()]);
    Response::error('Não foi possível entrar na sala.', null, 500);
}

if (!$result['ok']) {
    $status = match ($result['code'] ?? 'validation') {
        'closed', 'duplicate' => 409,
        default => 422,
    };

    Response::error($result['message'], null, $status);
}

ParticipantAuth::login($result['participant'], (string) $result['token']);

Response::success([
    'participant_id' => (int) $result['participant']['id'],
    'nickname' => (string) $result['participant']['nickname'],
    'avatar' => (string) ($result['participant']['avatar'] ?? '🙂'),
    'avatar_color' => (string) ($result['participant']['avatar_color'] ?? '#087F5B'),
    'room_code' => (string) $room['code'],
    'room_status' => (string) $room['status'],
    'token' => (string) $result['token'],
], 'Você entrou na sala.', 201);
