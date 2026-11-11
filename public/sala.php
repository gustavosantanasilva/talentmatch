<?php

declare(strict_types=1);

/**
 * Sala de espera do participante.
 *
 * Exige sessão de participante, atualiza o "visto por último" e permite sair
 * da sala enquanto a partida não começa.
 */

use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Middleware\ParticipantAuth;
use App\Models\Participant;
use App\Models\Room;

require dirname(__DIR__) . '/app/bootstrap.php';

$participant = ParticipantAuth::requirePage();
$room = Room::find((int) $participant['room_id']);

if ($room === null) {
    ParticipantAuth::logout();
    flash_set('warning', 'A sala não existe mais.');
    header('Location: ' . base_url('/'));
    exit;
}

$flash = null;

if (Request::isPost()) {
    Csrf::validate();

    if (Request::string('action') === 'leave') {
        ParticipantAuth::logout();
        flash_set('info', 'Você saiu da sala.');
        header('Location: ' . base_url('/'));
        exit;
    }
}

Participant::touch((int) $participant['id']);
$flash ??= flash_get();

View::render('participant/sala', [
    'title' => 'Sala ' . $room['code'],
    'participant' => $participant,
    'room' => $room,
    'participants' => Participant::listByRoom((int) $room['id']),
    'flash' => $flash,
    'roomCode' => (string) $room['code'],
], 'layouts/participant');
