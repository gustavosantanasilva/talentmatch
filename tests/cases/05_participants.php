<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\Admin;
use App\Models\Participant;
use App\Models\Room;
use App\Services\ParticipantService;
use App\Services\RoomService;

section('Participantes: entrada, apelido e token');

if (!Database::isConnected()) {
    echo "  \033[33m⚠ Banco de dados indisponível — testes de participantes ignorados.\033[0m\n";

    return;
}

$admin = Admin::findByUsername('admin');
assert_not_null($admin, 'O administrador de teste precisa existir.');
$adminId = (int) $admin['id'];

/** @var array<int,int> $createdRooms */
$createdRooms = [];

$cleanup = static function () use (&$createdRooms): void {
    $pdo = Database::connection();
    foreach ($createdRooms as $id) {
        // ON DELETE CASCADE remove os participantes vinculados.
        $pdo->prepare('DELETE FROM rooms WHERE id = :id')->execute(['id' => $id]);
    }
    $createdRooms = [];
};

$newRoom = static function () use ($adminId, &$createdRooms): array {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];

    return $room;
};

test('A sala só aceita entrada em WAITING ou PROFILES', function (): void {
    assert_true(ParticipantService::canJoin('WAITING'));
    assert_true(ParticipantService::canJoin('PROFILES'));
    assert_false(ParticipantService::canJoin('GENERATING'));
    assert_false(ParticipantService::canJoin('JOBS'));
    assert_false(ParticipantService::canJoin('RESULTS'));
    assert_false(ParticipantService::canJoin('FINISHED'));
});

test('Entrar na sala cria participante e devolve um token', function () use ($newRoom): void {
    $room = $newRoom();
    $result = ParticipantService::join($room, 'Ana');

    assert_true($result['ok'], $result['message']);
    assert_true(isset($result['token']) && strlen((string) $result['token']) === 64);
    assert_equals('Ana', $result['participant']['nickname']);
    assert_equals('WAITING', $result['participant']['status']);
    assert_equals((int) $room['id'], (int) $result['participant']['room_id']);
});

test('O token é guardado apenas como hash SHA-256', function () use ($newRoom): void {
    $room = $newRoom();
    $result = ParticipantService::join($room, 'Bruno');
    $token = (string) $result['token'];

    $stored = Database::connection()->prepare(
        'SELECT token_hash FROM participants WHERE id = :id'
    );
    $stored->execute(['id' => (int) $result['participant']['id']]);
    $hash = (string) $stored->fetchColumn();

    assert_equals(hash('sha256', $token), $hash, 'O hash armazenado deve corresponder ao token.');
    assert_true($hash !== $token, 'O token em texto puro não pode ser armazenado.');

    $found = Participant::findByTokenHash(hash('sha256', $token));
    assert_not_null($found);
    assert_equals((int) $result['participant']['id'], (int) $found['id']);
});

test('O apelido é único dentro da sala', function () use ($newRoom): void {
    $room = $newRoom();

    $first = ParticipantService::join($room, 'Carla');
    assert_true($first['ok'], $first['message']);

    $duplicate = ParticipantService::join($room, 'carla');
    assert_false($duplicate['ok'], 'Apelidos iguais (mesmo com caixa diferente) não são permitidos.');
    assert_equals('duplicate', $duplicate['code'] ?? '');
});

test('Apelidos inválidos são rejeitados', function () use ($newRoom): void {
    $room = $newRoom();

    $tooShort = ParticipantService::join($room, 'A');
    assert_false($tooShort['ok']);
    assert_equals('validation', $tooShort['code'] ?? '');

    $empty = ParticipantService::join($room, '   ');
    assert_false($empty['ok']);
});

test('Uma sala encerrada não aceita novos participantes', function () use ($newRoom): void {
    $room = $newRoom();
    Database::connection()
        ->prepare('UPDATE rooms SET status = "FINISHED" WHERE id = :id')
        ->execute(['id' => (int) $room['id']]);

    $result = ParticipantService::join(Room::find((int) $room['id']), 'Diego');
    assert_false($result['ok']);
    assert_equals('closed', $result['code'] ?? '');
});

test('A lista pública não expõe tokens', function () use ($newRoom): void {
    $room = $newRoom();
    ParticipantService::join($room, 'Elisa');
    ParticipantService::join($room, 'Fábio');

    $list = ParticipantService::publicList((int) $room['id']);
    assert_count(2, $list);

    foreach ($list as $entry) {
        assert_true(array_key_exists('nickname', $entry));
        assert_false(array_key_exists('token_hash', $entry));
        assert_false(array_key_exists('id', $entry));
    }

    assert_equals(2, Participant::countByRoom((int) $room['id']));
});

test('Os rótulos de status do participante estão em português', function (): void {
    assert_equals('Na sala de espera', Participant::statusLabel('WAITING'));
    assert_equals('Perfil pronto', Participant::statusLabel('READY'));
});

$cleanup();
