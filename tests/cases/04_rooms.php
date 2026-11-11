<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\Admin;
use App\Models\Room;
use App\Services\RoomService;

section('Salas e regras de estado');

if (!Database::isConnected()) {
    echo "  \033[33m⚠ Banco de dados indisponível — testes de salas ignorados.\033[0m\n";

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
        $pdo->prepare('DELETE FROM rooms WHERE id = :id')->execute(['id' => $id]);
    }
    $createdRooms = [];
};

test('O código gerado tem 6 caracteres sem ambiguidade', function (): void {
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    for ($i = 0; $i < 20; $i++) {
        $code = RoomService::generateCode();
        assert_equals(6, strlen($code), 'O código deve ter 6 caracteres.');
        assert_true(strspn($code, $alphabet) === strlen($code), "Código com caractere inválido: {$code}");
        assert_false(str_contains($code, 'O') || str_contains($code, '0'), 'Sem caracteres ambíguos.');
    }
});

test('createRoom cria uma sala em WAITING', function () use ($adminId, &$createdRooms): void {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];

    assert_equals('WAITING', $room['status']);
    assert_equals($adminId, (int) $room['admin_id']);
    assert_equals(6, strlen((string) $room['code']));
    assert_equals(0, (int) $room['current_job_index']);
    assert_equals('NONE', $room['jobs_source']);
});

test('Duas salas recebem códigos diferentes', function () use ($adminId, &$createdRooms): void {
    $a = RoomService::createRoom($adminId);
    $b = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $a['id'];
    $createdRooms[] = (int) $b['id'];

    assert_true($a['code'] !== $b['code'], 'Os códigos devem ser únicos.');
});

test('findByCode encontra a sala e ignora a caixa', function () use ($adminId, &$createdRooms): void {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];

    $found = Room::findByCode(strtolower((string) $room['code']));
    assert_not_null($found);
    assert_equals((int) $room['id'], (int) $found['id']);
});

test('A URL de entrada aponta para entrar.php com o código', function (): void {
    $url = RoomService::joinUrl('tm8k29');
    assert_true(str_contains($url, 'entrar.php?room=TM8K29'), "URL inesperada: {$url}");
});

test('As transições permitidas seguem o fluxo da partida', function (): void {
    assert_equals(['PROFILES', 'FINISHED'], RoomService::allowedTransitions('WAITING'));
    assert_equals(['GENERATING', 'FINISHED'], RoomService::allowedTransitions('PROFILES'));
    assert_equals(['JOBS', 'FINISHED'], RoomService::allowedTransitions('GENERATING'));
    assert_equals(['RESULTS', 'FINISHED'], RoomService::allowedTransitions('JOBS'));
    assert_equals(['FINISHED'], RoomService::allowedTransitions('RESULTS'));
    assert_equals([], RoomService::allowedTransitions('FINISHED'));
});

test('Uma transição inválida é rejeitada', function () use ($adminId, &$createdRooms): void {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];

    $result = RoomService::transition((int) $room['id'], 'JOBS');
    assert_false($result['ok'], 'WAITING não pode ir direto para JOBS.');

    // O estado no banco permanece inalterado.
    assert_equals('WAITING', Room::find((int) $room['id'])['status']);
});

test('Não é possível iniciar sem participantes', function () use ($adminId, &$createdRooms): void {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];

    $result = RoomService::transition((int) $room['id'], 'PROFILES');
    assert_false($result['ok'], 'O início exige ao menos um participante.');
});

test('O estado é atualizado quando há participante', function () use ($adminId, &$createdRooms): void {
    $room = RoomService::createRoom($adminId);
    $roomId = (int) $room['id'];
    $createdRooms[] = $roomId;

    $pdo = Database::connection();
    $pdo->prepare(
        'INSERT INTO participants (room_id, nickname, token_hash, status)
         VALUES (:room_id, :nickname, :token_hash, "READY")'
    )->execute([
        'room_id' => $roomId,
        'nickname' => 'Teste ' . $roomId,
        'token_hash' => hash('sha256', 'token-' . $roomId),
    ]);

    $result = RoomService::transition($roomId, 'PROFILES');
    assert_true($result['ok'], $result['message']);

    $updated = Room::find($roomId);
    assert_equals('PROFILES', $updated['status']);
    assert_not_null($updated['started_at']);
});

test('O payload público não expõe dados administrativos', function () use ($adminId, &$createdRooms): void {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];

    $public = RoomService::statePayload($room, false);
    assert_false(array_key_exists('join_url', $public), 'O payload público não deve ter join_url.');
    assert_false(array_key_exists('allowed_transitions', $public));
    assert_true(array_key_exists('participants_count', $public));
    assert_true(array_key_exists('status_label', $public));

    $adminView = RoomService::statePayload($room, true);
    assert_true(array_key_exists('join_url', $adminView));
    assert_true(array_key_exists('allowed_transitions', $adminView));
});

test('Os rótulos de estado estão em português', function (): void {
    assert_equals('Sala de espera', RoomService::statusLabel('WAITING'));
    assert_equals('Encerrada', RoomService::statusLabel('FINISHED'));
});

$cleanup();
