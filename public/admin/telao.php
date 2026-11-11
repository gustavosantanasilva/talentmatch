<?php

declare(strict_types=1);

/**
 * Telão da sala — projeção em tela cheia (QR Code + participantes ao vivo).
 */

use App\Core\Request;
use App\Core\View;
use App\Middleware\Auth;
use App\Models\Participant;
use App\Models\Room;
use App\Services\RoomService;

require dirname(__DIR__, 2) . '/app/bootstrap.php';

Auth::requirePage();

$adminId = (int) Auth::id();
$roomId = (int) Request::int('id');
$room = $roomId > 0 ? Room::find($roomId) : null;

if ($room === null || (int) $room['admin_id'] !== $adminId) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

View::render('admin/telao', [
    'title' => 'Telão da sala ' . $room['code'],
    'room' => $room,
    'joinUrl' => RoomService::joinUrl((string) $room['code']),
    'participants' => Participant::listByRoom((int) $room['id']),
], 'layouts/telao');
