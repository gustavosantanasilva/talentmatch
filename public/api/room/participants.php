<?php

declare(strict_types=1);

/**
 * GET /api/room/participants.php?code=XXXXXX
 * Lista pública dos participantes de uma sala (apelido, status e horário).
 *
 * Não exige autenticação e nunca devolve tokens nem dados de perfil.
 * É usada pela sala de espera dos participantes e pelo telão.
 */

use App\Core\Request;
use App\Core\Response;
use App\Models\Participant;
use App\Models\Room;
use App\Services\ParticipantService;
use App\Services\RoomService;

require dirname(__DIR__, 3) . '/app/bootstrap.php';

if (Request::method() !== 'GET') {
    Response::error('Método não permitido.', null, 405);
}

$code = strtoupper(Request::string('code'));
$room = $code !== '' ? Room::findByCode($code) : null;

if ($room === null) {
    Response::error('Sala não encontrada.', null, 404);
}

$participants = ParticipantService::publicList((int) $room['id']);

Response::success([
    'code' => (string) $room['code'],
    'status' => (string) $room['status'],
    'status_label' => RoomService::statusLabel((string) $room['status']),
    'participants_count' => Participant::countByRoom((int) $room['id']),
    'participants' => $participants,
]);
