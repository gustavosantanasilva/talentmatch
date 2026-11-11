<?php

declare(strict_types=1);

/**
 * Painel do organizador — visão geral e criação de salas.
 */

use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Middleware\Auth;
use App\Models\Room;
use App\Services\RoomService;

require dirname(__DIR__, 2) . '/app/bootstrap.php';

Auth::requirePage();

$adminId = (int) Auth::id();
$flash = null;

if (Request::isPost()) {
    Csrf::validate();

    $action = Request::string('action');

    if ($action === 'create_room') {
        try {
            $room = RoomService::createRoom($adminId);
            header('Location: ' . base_url('admin/sala.php?id=' . (int) $room['id']));
            exit;
        } catch (Throwable $exception) {
            \App\Core\Logger::error('Falha ao criar sala', ['message' => $exception->getMessage()]);
            $flash = ['type' => 'danger', 'message' => 'Não foi possível criar a sala. Tente novamente.'];
        }
    }
}

if ($flash === null) {
    $flash = flash_get();
}

View::render('admin/dashboard', [
    'title' => 'Visão geral',
    'page' => 'dashboard',
    'heading' => 'Visão geral',
    'stats' => Room::stats($adminId),
    'rooms' => Room::listByAdmin($adminId),
    'flash' => $flash,
], 'layouts/admin');
