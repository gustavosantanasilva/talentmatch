<?php

declare(strict_types=1);

/**
 * Perfil profissional do participante.
 *
 * Disponível a partir da etapa de montagem de perfis (estado PROFILES).
 * Permite salvar um rascunho e finalizar o perfil.
 */

use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Middleware\ParticipantAuth;
use App\Models\Room;
use App\Services\ProfileService;

require dirname(__DIR__) . '/app/bootstrap.php';

$participant = ParticipantAuth::requirePage();
$room = Room::find((int) $participant['room_id']);

if ($room === null) {
    ParticipantAuth::logout();
    flash_set('warning', 'A sala não existe mais.');
    header('Location: ' . base_url('/'));
    exit;
}

$status = (string) $room['status'];
$editable = ProfileService::isEditable($status);

// Antes da etapa de perfis, o participante volta para a sala de espera.
if ($status === 'WAITING') {
    flash_set('info', 'O organizador ainda não iniciou a montagem de perfis.');
    header('Location: ' . base_url('sala.php'));
    exit;
}

$errors = [];
$input = [];

if (Request::isPost()) {
    Csrf::validate();

    if (!$editable) {
        flash_set('warning', 'A edição de perfil não está disponível nesta etapa.');
        header('Location: ' . base_url('perfil.php'));
        exit;
    }

    $input = [
        'area' => Request::string('area'),
        'headline' => Request::string('headline'),
        'skills' => Request::array('skills'),
        'experiences' => Request::array('experiences'),
        'educations' => Request::array('educations'),
    ];

    $complete = Request::string('action') === 'complete';
    $result = ProfileService::persist((int) $participant['id'], $input, $complete);

    if ($result['ok']) {
        flash_set('success', $result['message']);
        header('Location: ' . base_url('perfil.php'));
        exit;
    }

    $errors = $result['errors'];
}

$profileData = $errors === []
    ? ProfileService::viewData((int) $participant['id'])
    : array_merge(ProfileService::clean($input), ['is_complete' => false]);

View::render('participant/perfil', [
    'title' => 'Meu perfil',
    'participant' => $participant,
    'room' => $room,
    'profileData' => $profileData,
    'errors' => $errors,
    'flash' => flash_get(),
    'editable' => $editable,
    'areas' => (array) config('app.areas', []),
    'levels' => (array) config('app.skill_levels', []),
    'roomCode' => (string) $room['code'],
], 'layouts/participant');
