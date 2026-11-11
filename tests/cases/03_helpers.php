<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\Validator;

section('Helpers e validação');

test('normalize_skill remove acentos, caixa e pontuação', function (): void {
    assert_equals('php', normalize_skill('PHP'));
    assert_equals('comunicacao', normalize_skill('Comunicação'));
    assert_equals('c++', normalize_skill('C++'));
    assert_equals('c#', normalize_skill('C#'));
    assert_equals('banco de dados', normalize_skill('  Banco   de   Dados  '));
});

test('truncate_text respeita o limite', function (): void {
    $short = truncate_text('Curto', 20);
    assert_equals('Curto', $short);

    $long = truncate_text(str_repeat('a', 50), 10);
    assert_equals(10, mb_strlen($long), 'O texto truncado deve ter o tamanho do limite.');
    assert_true(str_ends_with($long, '…'));
});

test('json_array nunca retorna erro para entradas inválidas', function (): void {
    assert_equals([], json_array(null));
    assert_equals([], json_array(''));
    assert_equals([], json_array('não é json'));
    assert_equals(['a', 'b'], json_array('["a","b"]'));
});

test('e() escapa caracteres perigosos (anti-XSS)', function (): void {
    $dirty = '<script>alert("x")</script>';
    $clean = e($dirty);
    assert_false(str_contains($clean, '<script>'), 'A tag script não foi escapada.');
    assert_true(str_contains($clean, '&lt;script&gt;'));
});

test('Env retorna valores padrão quando a chave não existe', function (): void {
    assert_equals('padrao', Env::get('CHAVE_INEXISTENTE_XYZ', 'padrao'));
    assert_true(Env::bool('CHAVE_INEXISTENTE_XYZ', true));
    assert_equals(42, Env::int('CHAVE_INEXISTENTE_XYZ', 42));
});

test('O validador aceita apelidos válidos', function (): void {
    $v = Validator::make(['nickname' => 'Ana Silva'], ['nickname' => 'required|nickname']);
    assert_true($v->passes(), $v->firstError());
});

test('O validador rejeita apelidos inválidos', function (): void {
    $v = Validator::make(['nickname' => '<img>'], ['nickname' => 'required|nickname']);
    assert_true($v->fails());
    assert_true(isset($v->errors()['nickname']));
});

test('O validador exige campos obrigatórios', function (): void {
    $v = Validator::make([], ['nickname' => 'required|min:2']);
    assert_true($v->fails());
    assert_true(str_contains($v->firstError(), 'obrigatório'));
});

test('O validador checa valores permitidos (in)', function (): void {
    $ok = Validator::make(['status' => 'APPLIED'], ['status' => 'required|in:APPLIED,SKIPPED']);
    assert_true($ok->passes());

    $bad = Validator::make(['status' => 'SAVED'], ['status' => 'required|in:APPLIED,SKIPPED']);
    assert_true($bad->fails(), 'O estado SAVED não deve ser aceito.');
});
