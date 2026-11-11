<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validação de dados no servidor com mensagens em português.
 *
 * Uso:
 *   $v = Validator::make($data, ['nickname' => 'required|min:2|max:30']);
 *   if ($v->fails()) { ... $v->errors() ... }
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    /** @var array<string,mixed> */
    private array $data;

    /**
     * @param array<string,mixed> $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,string> $rules
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);

        foreach ($rules as $field => $ruleString) {
            $validator->apply($field, $ruleString);
        }

        return $validator;
    }

    private function apply(string $field, string $ruleString): void
    {
        $value = $this->data[$field] ?? null;
        $rules = explode('|', $ruleString);

        $required = in_array('required', $rules, true);
        $present = array_key_exists($field, $this->data)
            && $this->data[$field] !== null
            && $this->data[$field] !== '';

        if (!$present) {
            if ($required) {
                $this->errors[$field] = $this->label($field) . ' é obrigatório.';
            }

            return;
        }

        foreach ($rules as $rule) {
            if ($rule === 'required' || $rule === '') {
                continue;
            }

            [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

            match ($name) {
                'string' => $this->checkString($field, $value),
                'min' => $this->checkMin($field, $value, (int) $param),
                'max' => $this->checkMax($field, $value, (int) $param),
                'int' => $this->checkInt($field, $value),
                'in' => $this->checkIn($field, $value, explode(',', (string) $param)),
                'nickname' => $this->checkNickname($field, (string) $value),
                'email' => $this->checkEmail($field, (string) $value),
                'array' => $this->checkArray($field, $value),
                default => null,
            };

            if (isset($this->errors[$field])) {
                return;
            }
        }
    }

    private function checkString(string $field, mixed $value): void
    {
        if (!is_string($value)) {
            $this->errors[$field] = $this->label($field) . ' deve ser texto.';
        }
    }

    private function checkMin(string $field, mixed $value, int $min): void
    {
        if (is_string($value)) {
            if (mb_strlen(trim($value)) < $min) {
                $this->errors[$field] = $this->label($field) . " deve ter no mínimo {$min} caracteres.";
            }

            return;
        }

        if (is_array($value) && count($value) < $min) {
            $this->errors[$field] = $this->label($field) . " deve ter no mínimo {$min} itens.";
        }

        if (is_numeric($value) && (float) $value < $min) {
            $this->errors[$field] = $this->label($field) . " deve ser maior ou igual a {$min}.";
        }
    }

    private function checkMax(string $field, mixed $value, int $max): void
    {
        if (is_string($value)) {
            if (mb_strlen(trim($value)) > $max) {
                $this->errors[$field] = $this->label($field) . " deve ter no máximo {$max} caracteres.";
            }

            return;
        }

        if (is_array($value) && count($value) > $max) {
            $this->errors[$field] = $this->label($field) . " deve ter no máximo {$max} itens.";
        }

        if (is_numeric($value) && (float) $value > $max) {
            $this->errors[$field] = $this->label($field) . " deve ser menor ou igual a {$max}.";
        }
    }

    private function checkInt(string $field, mixed $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->errors[$field] = $this->label($field) . ' deve ser um número inteiro.';
        }
    }

    /**
     * @param array<int,string> $allowed
     */
    private function checkIn(string $field, mixed $value, array $allowed): void
    {
        if (!in_array((string) $value, $allowed, true)) {
            $this->errors[$field] = $this->label($field) . ' possui um valor inválido.';
        }
    }

    private function checkNickname(string $field, string $value): void
    {
        if (preg_match('/^[\p{L}\p{N} _.\-]{2,30}$/u', trim($value)) !== 1) {
            $this->errors[$field] = $this->label($field)
                . ' deve ter de 2 a 30 caracteres e usar apenas letras, números, espaço, ponto, hífen ou sublinhado.';
        }
    }

    private function checkEmail(string $field, string $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field] = $this->label($field) . ' deve ser um e-mail válido.';
        }
    }

    private function checkArray(string $field, mixed $value): void
    {
        if (!is_array($value)) {
            $this->errors[$field] = $this->label($field) . ' deve ser uma lista.';
        }
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /**
     * @return array<string,string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return (string) (array_values($this->errors)[0] ?? '');
    }

    private function label(string $field): string
    {
        $labels = [
            'nickname' => 'Apelido',
            'area' => 'Área profissional',
            'code' => 'Código da sala',
            'skills' => 'Habilidades',
            'experiences' => 'Experiências',
            'educations' => 'Formações',
            'job_id' => 'Vaga',
            'status' => 'Status',
            'username' => 'Usuário',
            'password' => 'Senha',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}
