<?php

declare(strict_types=1);

/**
 * Entrada do participante — destino do QR Code (`entrar.php?room=CÓDIGO`).
 *
 * Mostra a sala, coleta o apelido e registra o participante com um token
 * aleatório (armazenado no servidor apenas como hash).
 */

use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Middleware\ParticipantAuth;
use App\Models\Participant;
use App\Models\Room;
use App\Services\ParticipantService;

require dirname(__DIR__) . '/app/bootstrap.php';

$code = strtoupper(Request::string('room'));
$room = $code !== '' ? Room::findByCode($code) : null;

// Já está nesta sala? Vai direto para a sala de espera.
$current = ParticipantAuth::current();
if ($room !== null && $current !== null && (int) $current['room_id'] === (int) $room['id']) {
    header('Location: ' . base_url('sala.php'));
    exit;
}

$mode = 'form';
$error = '';
$nickname = '';
$avatar = (string) config('app.default_avatar', '🙂');
$avatarColor = (string) (config('app.avatar_colors', [])[0] ?? '#087F5B');

if ($room === null) {
    $mode = 'notfound';
} elseif (!ParticipantService::canJoin((string) $room['status'])) {
    $mode = 'closed';
} elseif (Request::isPost()) {
    Csrf::validate();

    $nickname = Request::string('nickname');
    $avatar = ParticipantService::normalizeAvatar(Request::string('avatar'));
    $avatarColor = ParticipantService::normalizeAvatarColor(Request::string('avatar_color'));
    $result = ParticipantService::join($room, $nickname, $avatar, $avatarColor);

    if ($result['ok']) {
        ParticipantAuth::login($result['participant'], (string) $result['token']);
        header('Location: ' . base_url('sala.php'));
        exit;
    }

    $error = $result['message'];
}

View::render('participant/entrar', [
    'title' => $room === null ? 'Sala não encontrada' : 'Entrar na sala ' . $room['code'],
    'room' => $room,
    'mode' => $mode,
    'error' => $error,
    'nickname' => $nickname,
    'avatar' => $avatar,
    'avatarColor' => $avatarColor,
    'avatars' => (array) config('app.avatars', []),
    'avatarColors' => (array) config('app.avatar_colors', []),
    'participantsCount' => $room === null ? 0 : Participant::countByRoom((int) $room['id']),
    'roomCode' => $room === null ? null : (string) $room['code'],
], 'layouts/participant');
