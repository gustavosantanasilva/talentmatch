<?php

declare(strict_types=1);

use App\Core\Database;

section('Banco de dados e esquema');

if (!Database::isConnected()) {
    echo "  \033[33m⚠ Banco de dados indisponível — testes de banco ignorados.\033[0m\n";
    echo "    Configure o .env e importe database/schema.sql para executá-los.\n";

    return;
}

$pdo = Database::connection();

test('A conexão PDO está ativa', function () use ($pdo): void {
    $value = $pdo->query('SELECT 1')->fetchColumn();
    assert_equals(1, (int) $value);
});

test('Todas as tabelas esperadas existem', function () use ($pdo): void {
    $expected = [
        'admins', 'rooms', 'participants', 'profiles', 'skills', 'experiences',
        'educations', 'job_catalog', 'jobs', 'job_skills', 'applications',
        'match_results', 'game_events',
    ];

    $statement = $pdo->query('SHOW TABLES');
    $tables = $statement->fetchAll(PDO::FETCH_COLUMN);

    foreach ($expected as $table) {
        assert_true(in_array($table, $tables, true), "Tabela ausente: {$table}");
    }
});

test('As tabelas usam o engine InnoDB', function () use ($pdo): void {
    $rows = $pdo->query("SHOW TABLE STATUS WHERE Engine <> 'InnoDB'")->fetchAll();
    assert_count(0, $rows, 'Todas as tabelas devem usar InnoDB.');
});

test('Existe ao menos um administrador inicial', function () use ($pdo): void {
    $count = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    assert_true($count >= 1, 'Nenhum administrador encontrado no seed.');

    $admin = $pdo->query('SELECT username, password_hash FROM admins LIMIT 1')->fetch();
    assert_not_null($admin);
    assert_true(str_starts_with((string) $admin['password_hash'], '$2y$'), 'A senha deve estar em hash bcrypt.');
});

test('O catálogo de vagas de fallback está populado', function () use ($pdo): void {
    $count = (int) $pdo->query('SELECT COUNT(*) FROM job_catalog WHERE is_active = 1')->fetchColumn();
    assert_true($count >= 10, "Esperava-se ao menos 10 vagas no catálogo, encontrado {$count}.");

    $areas = $pdo->query('SELECT DISTINCT area FROM job_catalog')->fetchAll(PDO::FETCH_COLUMN);
    assert_true(count($areas) >= 5, 'O catálogo deve cobrir várias áreas profissionais.');
});

test('As chaves estrangeiras estão configuradas', function () use ($pdo): void {
    $expectedForeignKeys = [
        'rooms' => 'fk_rooms_admin',
        'participants' => 'fk_participants_room',
        'profiles' => 'fk_profiles_participant',
        'skills' => 'fk_skills_profile',
        'experiences' => 'fk_experiences_profile',
        'educations' => 'fk_educations_profile',
        'jobs' => 'fk_jobs_room',
        'job_skills' => 'fk_job_skills_job',
        'applications' => 'fk_applications_job',
        'match_results' => 'fk_results_job',
        'game_events' => 'fk_events_room',
    ];

    foreach ($expectedForeignKeys as $table => $constraint) {
        $sql = 'SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = :table
                  AND CONSTRAINT_NAME = :name
                  AND CONSTRAINT_TYPE = "FOREIGN KEY"';
        $statement = $pdo->prepare($sql);
        $statement->execute(['table' => $table, 'name' => $constraint]);
        assert_equals(1, (int) $statement->fetchColumn(), "FK ausente: {$constraint} em {$table}.");
    }
});

test('O índice único impede candidaturas duplicadas', function () use ($pdo): void {
    $sql = 'SELECT COUNT(*) FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "applications"
              AND INDEX_NAME = "uq_applications_participant_job"
              AND NON_UNIQUE = 0';
    $rows = (int) $pdo->query($sql)->fetchColumn();
    assert_true($rows >= 1, 'Índice único de candidaturas ausente.');

    // Nenhum índice desta tabela pode ser não-único com o mesmo nome.
    $nonUnique = (int) $pdo->query($sql = 'SELECT COUNT(*) FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "applications"
              AND INDEX_NAME = "uq_applications_participant_job"
              AND NON_UNIQUE = 1')->fetchColumn();
    assert_equals(0, $nonUnique, 'O índice de candidaturas deveria ser único.');
});

test('Os campos JSON do catálogo são válidos', function () use ($pdo): void {
    $rows = $pdo->query('SELECT required_skills, desirable_skills, benefits, activities FROM job_catalog')->fetchAll();
    assert_true($rows !== [], 'Catálogo vazio.');

    foreach ($rows as $row) {
        foreach (['required_skills', 'desirable_skills', 'benefits', 'activities'] as $field) {
            $decoded = json_decode((string) $row[$field], true);
            assert_true(is_array($decoded), "Campo {$field} não contém JSON válido.");
        }
    }
});
