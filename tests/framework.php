<?php

declare(strict_types=1);

/**
 * Framework de testes mínimo, sem dependências externas.
 * Executado via linha de comando: php tests/run.php
 */

final class TmTestRunner
{
    /** @var array<int,array{name:string,ok:bool,error:?string}> */
    private array $results = [];

    private string $current = '';

    private int $assertions = 0;

    public function test(string $name, callable $fn): void
    {
        $this->current = $name;
        $before = $this->assertions;

        try {
            $fn();
            $this->results[] = ['name' => $name, 'ok' => true, 'error' => null];
            echo "  \033[32m✓\033[0m {$name}\n";
        } catch (Throwable $exception) {
            $this->results[] = ['name' => $name, 'ok' => false, 'error' => $exception->getMessage()];
            echo "  \033[31m✗\033[0m {$name}\n";
            echo "      \033[31m{$exception->getMessage()}\033[0m\n";
        }

        if ($this->assertions === $before) {
            // Garante que cada teste faça ao menos uma verificação.
            $this->results[count($this->results) - 1]['ok'] = false;
            $this->results[count($this->results) - 1]['error'] = 'Nenhuma asserção executada.';
        }
    }

    public function section(string $title): void
    {
        echo "\n\033[1;36m{$title}\033[0m\n";
    }

    public function fail(string $message): never
    {
        throw new RuntimeException($message);
    }

    public function assertTrue(bool $condition, string $message = 'Esperado verdadeiro.'): void
    {
        $this->assertions++;

        if (!$condition) {
            $this->fail($message);
        }
    }

    public function assertFalse(bool $condition, string $message = 'Esperado falso.'): void
    {
        $this->assertTrue(!$condition, $message);
    }

    public function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($expected != $actual) {
            $detail = $message !== '' ? $message . ' — ' : '';
            $this->fail($detail . 'esperado ' . $this->stringify($expected) . ', recebido ' . $this->stringify($actual));
        }
    }

    public function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($expected !== $actual) {
            $detail = $message !== '' ? $message . ' — ' : '';
            $this->fail($detail . 'esperado ' . $this->stringify($expected) . ', recebido ' . $this->stringify($actual));
        }
    }

    public function assertNotNull(mixed $value, string $message = 'Valor não deveria ser nulo.'): void
    {
        $this->assertTrue($value !== null, $message);
    }

    public function assertCount(int $expected, array $value, string $message = ''): void
    {
        $this->assertSame($expected, count($value), $message !== '' ? $message : 'Contagem divergente.');
    }

    public function assertThrows(callable $fn, string $message = 'Esperava-se uma exceção.'): void
    {
        $this->assertions++;

        try {
            $fn();
        } catch (Throwable) {
            return;
        }

        $this->fail($message);
    }

    public function summary(): int
    {
        $total = count($this->results);
        $failed = array_filter($this->results, static fn (array $r): bool => !$r['ok']);
        $passed = $total - count($failed);

        echo "\n" . str_repeat('─', 64) . "\n";
        printf(
            "  Testes: %d  |  \033[32mPassou: %d\033[0m  |  \033[31mFalhou: %d\033[0m  |  Asserções: %d\n",
            $total,
            $passed,
            count($failed),
            $this->assertions
        );

        if ($failed !== []) {
            echo "\n  Falhas:\n";
            foreach ($failed as $result) {
                echo "   • {$result['name']}\n";
                if (!empty($result['error'])) {
                    echo "     {$result['error']}\n";
                }
            }
        }

        echo str_repeat('─', 64) . "\n";

        return $failed === [] ? 0 : 1;
    }

    private function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: 'array';
        }

        return (string) $value;
    }
}

$GLOBALS['tm_runner'] = new TmTestRunner();

function test(string $name, callable $fn): void
{
    $GLOBALS['tm_runner']->test($name, $fn);
}

function section(string $title): void
{
    $GLOBALS['tm_runner']->section($title);
}

function assert_true(bool $condition, string $message = 'Esperado verdadeiro.'): void
{
    $GLOBALS['tm_runner']->assertTrue($condition, $message);
}

function assert_false(bool $condition, string $message = 'Esperado falso.'): void
{
    $GLOBALS['tm_runner']->assertFalse($condition, $message);
}

function assert_equals(mixed $expected, mixed $actual, string $message = ''): void
{
    $GLOBALS['tm_runner']->assertEquals($expected, $actual, $message);
}

function assert_same(mixed $expected, mixed $actual, string $message = ''): void
{
    $GLOBALS['tm_runner']->assertSame($expected, $actual, $message);
}

function assert_not_null(mixed $value, string $message = 'Valor não deveria ser nulo.'): void
{
    $GLOBALS['tm_runner']->assertNotNull($value, $message);
}

function assert_count(int $expected, array $value, string $message = ''): void
{
    $GLOBALS['tm_runner']->assertCount($expected, $value, $message);
}

function assert_throws(callable $fn, string $message = 'Esperava-se uma exceção.'): void
{
    $GLOBALS['tm_runner']->assertThrows($fn, $message);
}
