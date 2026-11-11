<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Env;

section('Ambiente e configuração');

test('O bootstrap carregou os caminhos do projeto', function (): void {
    assert_true(defined('BASE_PATH'), 'BASE_PATH não definido.');
    assert_true(defined('APP_PATH'), 'APP_PATH não definido.');
    assert_true(is_dir(BASE_PATH . '/app'), 'Diretório app/ não encontrado.');
    assert_true(is_dir(BASE_PATH . '/public'), 'Diretório public/ não encontrado.');
});

test('As configurações principais estão disponíveis', function (): void {
    assert_equals('TalentMatch', Config::get('app.name'));
    assert_equals(4, Config::get('app.game.max_jobs'));
    assert_count(6, (array) Config::get('app.game.states'));
});

test('As áreas profissionais estão definidas', function (): void {
    $areas = (array) Config::get('app.areas');
    assert_true(count($areas) >= 8, 'Esperava-se ao menos 8 áreas profissionais.');
    assert_true(array_key_exists('software', $areas));
    assert_true(array_key_exists('dados', $areas));
});

test('Os níveis de habilidade seguem o padrão esperado', function (): void {
    $levels = (array) Config::get('app.skill_levels');
    assert_count(3, $levels);
    assert_true(isset($levels['BASIC'], $levels['INTERMEDIATE'], $levels['ADVANCED']));
});

test('A configuração de banco não expõe segredos no código', function (): void {
    $database = (array) Config::get('database');
    foreach (['host', 'port', 'database', 'username', 'password', 'charset'] as $key) {
        assert_true(array_key_exists($key, $database), "Chave {$key} ausente na configuração de banco.");
    }
    assert_equals(Env::get('DB_HOST'), $database['host']);
});

test('A configuração de IA lê a chave do ambiente', function (): void {
    $ai = (array) Config::get('ai');
    assert_equals('deepseek', $ai['provider']);
    assert_equals('https://api.deepseek.com', $ai['base_url']);
    assert_true(array_key_exists('api_key', $ai), 'A chave de API deve vir do .env.');
});
