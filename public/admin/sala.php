<?php

declare(strict_types=1);

/**
 * Painel do organizador — detalhe e controle de uma sala.
 */

use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Middleware\Auth;
use App\Models\GameEvent;
use App\Models\Participant;
use App\Models\Profile;
use App\Models\Room;
use App\Services\RoomService;

require dirname(__DIR__, 2) . '/app/bootstrap.php';

Auth::requirePage();

$adminId = (int) Auth::id();
$roomId = Request::int('id');
$room = $roomId > 0 ? Room::find($roomId) : null;

// A sala precisa existir e pertencer a este administrador.
if ($room === null || (int) $room['admin_id'] !== $adminId) {
    flash_set('warning', 'Sala não encontrada ou sem permissão de acesso.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$flash = null;

if (Request::isPost()) {
    Csrf::validate();

    $action = Request::string('action');

    if ($action === 'start') {
        $result = RoomService::transition($roomId, 'PROFILES');
        $flash = ['type' => $result['ok'] ? 'success' : 'danger', 'message' => $result['message']];
    } elseif ($action === 'finish') {
        $result = RoomService::transition($roomId, 'FINISHED');
        $flash = ['type' => $result['ok'] ? 'success' : 'danger', 'message' => $result['message']];
    }

    if ($flash['type'] === 'success') {
        // Recarrega para refletir o novo estado.
        header('Location: ' . base_url('admin/sala.php?id=' . $roomId));
        exit;
    }

    $room = Room::find($roomId);
}

if ($flash === null) {
    $flash = flash_get();
}

$participants = Participant::listByRoom($roomId);

View::render('admin/sala', [
    'title' => 'Sala ' . $room['code'],
    'page' => 'dashboard',
    'heading' => 'Sala ' . $room['code'],
    'room' => $room,
    'joinUrl' => RoomService::joinUrl((string) $room['code']),
    'participants' => $participants,
    'events' => GameEvent::recentForRoom($roomId, 15),
    'allowedTransitions' => RoomService::allowedTransitions((string) $room['status']),
    'jobsCount' => Room::jobsCount($roomId),
    'completedProfiles' => Profile::completedCountForRoom($roomId),
    'flash' => $flash,
], 'layouts/admin');
