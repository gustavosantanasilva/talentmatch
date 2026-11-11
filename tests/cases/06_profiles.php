<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\Admin;
use App\Models\Participant;
use App\Models\Profile;
use App\Services\ParticipantService;
use App\Services\ProfileService;
use App\Services\RoomService;

section('Perfis: habilidades, experiências e formações');

if (!Database::isConnected()) {
    echo "  \033[33m⚠ Banco de dados indisponível — testes de perfis ignorados.\033[0m\n";

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

/** Cria sala + participante e devolve o id do participante. */
$newParticipant = static function () use ($adminId, &$createdRooms): int {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];
    $joined = ParticipantService::join($room, 'Perfil ' . $room['id']);

    return (int) $joined['participant']['id'];
};

$validInput = static fn (): array => [
    'area' => 'software',
    'headline' => '  Estudante de ADS  ',
    'skills' => [
        ['name' => 'PHP', 'level' => 'advanced'],
        ['name' => '   ', 'level' => 'BASIC'],
        ['name' => 'JavaScript', 'level' => 'INTERMEDIATE'],
    ],
    'experiences' => [
        ['title' => 'Projeto escolar', 'description' => 'Site fictício', 'competencies' => 'HTML, CSS'],
    ],
    'educations' => [
        ['course' => '', 'institution' => '', 'description' => ''],
    ],
];

test('O perfil só é editável na etapa de montagem de perfis', function (): void {
    assert_true(ProfileService::isEditable('PROFILES'));
    assert_false(ProfileService::isEditable('WAITING'));
    assert_false(ProfileService::isEditable('GENERATING'));
    assert_false(ProfileService::isEditable('JOBS'));
    assert_false(ProfileService::isEditable('RESULTS'));
    assert_false(ProfileService::isEditable('FINISHED'));
});

test('A limpeza descarta linhas vazias e normaliza as habilidades', function () use ($validInput): void {
    $clean = ProfileService::clean($validInput());

    assert_equals(2, count($clean['skills']), 'A linha vazia de habilidade deve ser descartada.');
    assert_equals('php', $clean['skills'][0]['name_norm']);
    assert_equals('ADVANCED', $clean['skills'][0]['level'], 'O nível deve ser normalizado para maiúsculas.');
    assert_equals('Estudante de ADS', $clean['headline'], 'O resumo deve ser aparado.');
    assert_equals(1, count($clean['experiences']));
    assert_equals(0, count($clean['educations']), 'A formação vazia deve ser descartada.');
});

test('A validação exige área e rejeita valores inválidos', function () use ($validInput): void {
    $clean = ProfileService::clean($validInput());
    assert_equals([], ProfileService::validate($clean, false));

    $noArea = ProfileService::clean(['area' => '', 'headline' => '', 'skills' => [['name' => 'PHP', 'level' => 'BASIC']]]);
    assert_true(ProfileService::validate($noArea, false) !== [], 'A área é obrigatória.');

    $badArea = ProfileService::clean(['area' => 'inexistente', 'skills' => [['name' => 'PHP', 'level' => 'BASIC']]]);
    assert_true(ProfileService::validate($badArea, false) !== [], 'Área fora da lista deve ser recusada.');
});

test('A validação recusa habilidades repetidas', function (): void {
    $clean = ProfileService::clean([
        'area' => 'software',
        'skills' => [
            ['name' => 'PHP', 'level' => 'BASIC'],
            ['name' => 'php', 'level' => 'ADVANCED'],
        ],
    ]);

    $errors = ProfileService::validate($clean, false);
    assert_true($errors !== [], 'Habilidades repetidas devem gerar erro.');
});

test('Finalizar exige ao menos uma habilidade', function (): void {
    $clean = ProfileService::clean(['area' => 'software', 'skills' => []]);
    assert_equals([], ProfileService::validate($clean, false), 'Rascunho pode ficar sem habilidades.');

    $errors = ProfileService::validate($clean, true);
    assert_true($errors !== [], 'A finalização exige habilidades.');
});

test('Salvar o rascunho cria o perfil e as habilidades', function () use ($newParticipant, $validInput): void {
    $participantId = $newParticipant();

    $result = ProfileService::persist($participantId, $validInput(), false);
    assert_true($result['ok'], $result['message']);

    $participant = Participant::find($participantId);
    assert_equals('PROFILE_PENDING', $participant['status']);

    $data = ProfileService::viewData($participantId);
    assert_equals('software', $data['area']);
    assert_equals(2, count($data['skills']));
    assert_equals(1, count($data['experiences']));
    assert_false($data['is_complete']);
});

test('Finalizar marca o perfil e o participante como prontos', function () use ($newParticipant, $validInput): void {
    $participantId = $newParticipant();

    $result = ProfileService::persist($participantId, $validInput(), true);
    assert_true($result['ok'], $result['message']);

    $profile = Profile::findByParticipant($participantId);
    assert_equals(1, (int) $profile['is_complete']);
    assert_not_null($profile['completed_at']);

    assert_equals('READY', Participant::find($participantId)['status']);
    assert_true(ProfileService::viewData($participantId)['is_complete']);
});

test('Salvar novamente substitui as habilidades antigas', function () use ($newParticipant, $validInput): void {
    $participantId = $newParticipant();
    ProfileService::persist($participantId, $validInput(), false);

    ProfileService::persist($participantId, [
        'area' => 'dados',
        'headline' => '',
        'skills' => [['name' => 'Python', 'level' => 'BASIC']],
    ], false);

    $data = ProfileService::viewData($participantId);
    assert_equals('dados', $data['area']);
    assert_equals(1, count($data['skills']));
    assert_equals('Python', $data['skills'][0]['name']);
});

test('O contador de perfis concluídos por sala funciona', function () use ($adminId, &$createdRooms, $validInput): void {
    $room = RoomService::createRoom($adminId);
    $createdRooms[] = (int) $room['id'];

    $a = ParticipantService::join($room, 'Um');
    $b = ParticipantService::join($room, 'Dois');
    ProfileService::persist((int) $a['participant']['id'], $validInput(), true);
    ProfileService::persist((int) $b['participant']['id'], $validInput(), false);

    assert_equals(1, Profile::completedCountForRoom((int) $room['id']));
});

$cleanup();
