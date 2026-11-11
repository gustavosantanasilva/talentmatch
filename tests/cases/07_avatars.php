<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\Admin;
use App\Models\Participant;
use App\Services\ParticipantService;
use App\Services\RoomService;

section('Avatares: escolha, persistência e atualização');

if (!Database::isConnected()) {
    echo "  \033[33m⚠ Banco de dados indisponível — testes de avatar ignorados.\033[0m\n";

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

$newRoom = static function () use ($adminId, &$createdRooms): array {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];

    return $room;
};

test('A lista de avatares e de cores está configurada', function (): void {
    $avatars = (array) config('app.avatars', []);
    $colors = (array) config('app.avatar_colors', []);

    assert_true(count($avatars) >= 8, 'É preciso haver vários avatares.');
    assert_true(count($colors) >= 4, 'É preciso haver várias cores.');
    assert_true(count(array_unique($avatars)) === count($avatars), 'Os avatares devem ser únicos.');
    assert_true(count(array_unique($colors)) === count($colors), 'As cores devem ser únicas.');
});

test('Um avatar desconhecido cai no avatar padrão', function (): void {
    assert_equals('🙂', ParticipantService::normalizeAvatar('emoji-inexistente'));
    assert_equals('🙂', ParticipantService::normalizeAvatar(''));
    assert_equals('🦊', ParticipantService::normalizeAvatar('🦊'));
});

test('Uma cor desconhecida cai na primeira cor da lista', function (): void {
    $colors = (array) config('app.avatar_colors', []);

    assert_equals($colors[0], ParticipantService::normalizeAvatarColor('não-é-cor'));
    assert_equals($colors[0], ParticipantService::normalizeAvatarColor(''));
    assert_equals('#1368CE', ParticipantService::normalizeAvatarColor('#1368CE'));
    assert_equals('#1368CE', ParticipantService::normalizeAvatarColor('#1368ce'));
    assert_equals('#123456', ParticipantService::normalizeAvatarColor('#123456'));
});

test('Entrar na sala guarda avatar e cor', function () use ($newRoom): void {
    $room = $newRoom();
    $result = ParticipantService::join($room, 'Ana Avatar', '🐼', '#1368CE');

    assert_true($result['ok'], $result['message']);
    assert_equals('🐼', (string) $result['participant']['avatar']);
    assert_equals('#1368CE', (string) $result['participant']['avatar_color']);

    $stored = Participant::find((int) $result['participant']['id']);
    assert_not_null($stored, 'O participante precisa existir no banco.');
    assert_equals('🐼', (string) $stored['avatar']);
    assert_equals('#1368CE', (string) $stored['avatar_color']);
});

test('Entrar sem avatar recebe o avatar padrão', function () use ($newRoom): void {
    $room = $newRoom();
    $result = ParticipantService::join($room, 'Bruno Sem Avatar');

    assert_true($result['ok'], $result['message']);
    assert_equals('🙂', (string) $result['participant']['avatar']);
    assert_equals('#087F5B', (string) $result['participant']['avatar_color']);
});

test('A lista pública expõe avatar e cor, mas nunca o token', function () use ($newRoom): void {
    $room = $newRoom();
    ParticipantService::join($room, 'Carla Avatar', '🦄', '#E21B3C');

    $list = ParticipantService::publicList((int) $room['id']);

    assert_count(1, $list);
    assert_equals('Carla Avatar', $list[0]['nickname']);
    assert_equals('🦄', $list[0]['avatar']);
    assert_equals('#E21B3C', $list[0]['avatar_color']);
    assert_false(array_key_exists('token', $list[0]), 'A lista pública não pode expor tokens.');
    assert_false(array_key_exists('token_hash', $list[0]), 'A lista pública não pode expor o hash do token.');
});

test('Atualizar a identidade troca avatar e cor', function () use ($newRoom): void {
    $room = $newRoom();
    $joined = ParticipantService::join($room, 'Diego Avatar', '🐯', '#087F5B');
    $participantId = (int) $joined['participant']['id'];

    $result = ParticipantService::updateIdentity($participantId, 'Diego Avatar', '🐸', '#FFA602');

    assert_true($result['ok'], $result['message']);
    assert_equals('🐸', (string) $result['participant']['avatar']);
    assert_equals('#FFA602', (string) $result['participant']['avatar_color']);
});

test('Atualizar a identidade com apelido duplicado é recusado', function () use ($newRoom): void {
    $room = $newRoom();
    $first = ParticipantService::join($room, 'Elena Uma');
    $second = ParticipantService::join($room, 'Fabio Dois');

    $result = ParticipantService::updateIdentity(
        (int) $second['participant']['id'],
        'Elena Uma',
        '🐧',
        '#1368CE'
    );

    assert_false($result['ok'], 'O apelido duplicado deveria ser recusado.');
    assert_true(str_contains($result['message'], 'já está em uso'), 'A mensagem deve explicar o conflito.');

    // O participante original permanece intacto.
    $stored = Participant::find((int) $first['participant']['id']);
    assert_equals('Elena Uma', (string) $stored['nickname']);
    assert_equals('🙂', (string) $stored['avatar']);
});

test('Atualizar a identidade mantém o apelido quando nada muda', function () use ($newRoom): void {
    $room = $newRoom();
    $joined = ParticipantService::join($room, 'Gustavo Igual', '🐬', '#0D9488');
    $participantId = (int) $joined['participant']['id'];

    $result = ParticipantService::updateIdentity($participantId, 'Gustavo Igual', '🐬', '#0D9488');

    assert_true($result['ok'], $result['message']);
});

$cleanup();
