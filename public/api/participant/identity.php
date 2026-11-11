<?php

declare(strict_types=1);

/**
 * POST /api/participant/identity.php
 * Atualiza apelido e avatar do participante autenticado.
 *
 * Autenticação: sessão do participante ou cabeçalho X-Participant-Token.
 * Exige token CSRF.
 */

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\ParticipantAuth;
use App\Services\ParticipantService;

require dirname(__DIR__, 3) . '/app/bootstrap.php';

if (!Request::isPost()) {
    Response::error('Método não permitido.', null, 405);
}

$participant = ParticipantAuth::requireApi();
Csrf::validate();

$result = ParticipantService::updateIdentity(
    (int) $participant['id'],
    Request::string('nickname'),
    Request::string('avatar'),
    Request::string('avatar_color')
);

if (!$result['ok']) {
    Response::error($result['message'], null, 409);
}

Response::success([
    'participant_id' => (int) $result['participant']['id'],
    'nickname' => (string) $result['participant']['nickname'],
    'avatar' => (string) ($result['participant']['avatar'] ?? '🙂'),
    'avatar_color' => (string) ($result['participant']['avatar_color'] ?? '#087F5B'),
    'status' => (string) $result['participant']['status'],
], $result['message']);
