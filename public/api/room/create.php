<?php

declare(strict_types=1);

/**
 * POST /api/room/create.php
 * Cria uma sala. Exige autenticação de administrador e token CSRF.
 */

use App\Core\Csrf;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\Auth;
use App\Services\RoomService;

require dirname(__DIR__, 3) . '/app/bootstrap.php';

if (!Request::isPost()) {
    Response::error('Método não permitido.', null, 405);
}

Auth::requireApi();
Csrf::validate();

try {
    $room = RoomService::createRoom((int) Auth::id());
} catch (Throwable $exception) {
    Logger::error('Falha ao criar sala via API', ['message' => $exception->getMessage()]);
    Response::error('Não foi possível criar a sala.', null, 500);
}

Response::success([
    'id' => (int) $room['id'],
    'code' => (string) $room['code'],
    'status' => (string) $room['status'],
    'join_url' => RoomService::joinUrl((string) $room['code']),
], 'Sala criada com sucesso.', 201);
