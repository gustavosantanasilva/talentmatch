<?php

declare(strict_types=1);

/**
 * GET /api/room/state.php?code=XXXXXX  (ou ?id=123)
 * Retorna o estado de uma sala.
 *
 * - Administrador dono da sala: dados completos.
 * - Demais visitantes (ex.: telão): apenas dados públicos, sem informações pessoais.
 */

use App\Core\Request;
use App\Core\Response;
use App\Middleware\Auth;
use App\Models\Room;
use App\Services\RoomService;

require dirname(__DIR__, 3) . '/app/bootstrap.php';

if (Request::method() !== 'GET') {
    Response::error('Método não permitido.', null, 405);
}

$room = null;
$code = Request::string('code');
$id = Request::int('id');

if ($code !== '') {
    $room = Room::findByCode($code);
} elseif ($id > 0) {
    $room = Room::find($id);
}

if ($room === null) {
    Response::error('Sala não encontrada.', null, 404);
}

$isOwner = Auth::check() && (int) $room['admin_id'] === (int) Auth::id();

Response::success(RoomService::statePayload($room, $isOwner));
