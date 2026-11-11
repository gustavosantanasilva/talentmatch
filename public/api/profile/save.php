<?php

declare(strict_types=1);

/**
 * POST /api/profile/save.php
 * Salva o rascunho do perfil do participante autenticado.
 *
 * Autenticação: sessão do participante ou cabeçalho X-Participant-Token.
 * Exige token CSRF.
 */

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\ParticipantAuth;
use App\Models\Room;
use App\Services\ProfileService;

require dirname(__DIR__, 3) . '/app/bootstrap.php';

if (!Request::isPost()) {
    Response::error('Método não permitido.', null, 405);
}

$participant = ParticipantAuth::requireApi();
Csrf::validate();

$room = Room::find((int) $participant['room_id']);

if ($room === null) {
    Response::error('Sala não encontrada.', null, 404);
}

if (!ProfileService::isEditable((string) $room['status'])) {
    Response::error('A edição de perfil não está disponível nesta etapa da partida.', null, 409);
}

$result = ProfileService::persist((int) $participant['id'], [
    'area' => Request::string('area'),
    'headline' => Request::string('headline'),
    'skills' => Request::array('skills'),
    'experiences' => Request::array('experiences'),
    'educations' => Request::array('educations'),
], false);

if (!$result['ok']) {
    Response::validationError($result['errors'], $result['message']);
}

Response::success(ProfileService::viewData((int) $participant['id']), $result['message']);
