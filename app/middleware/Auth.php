<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Models\Admin;

/**
 * Autenticação e autorização do painel administrativo.
 *
 * O estado de login fica na sessão; a verificação de permissão é sempre
 * feita no servidor, nunca a partir de dados enviados pelo navegador.
 */
final class Auth
{
    private const SESSION_KEY = 'admin_id';

    private const ATTEMPTS_KEY = 'login_attempts';

    private const LOCK_KEY = 'login_locked_until';

    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 300;

    private const IDLE_SECONDS = 7200;

    /**
     * Tenta autenticar um administrador.
     *
     * @return array{ok:bool,message:string}
     */
    public static function attempt(string $username, string $password): array
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            return ['ok' => false, 'message' => 'Informe usuário e senha.'];
        }

        $lockedUntil = (int) Session::get(self::LOCK_KEY, 0);

        if ($lockedUntil > time()) {
            $remaining = (int) ceil(($lockedUntil - time()) / 60);

            return [
                'ok' => false,
                'message' => "Muitas tentativas. Aguarde cerca de {$remaining} minuto(s).",
            ];
        }

        $admin = Admin::findByUsername($username);

        if ($admin === null || !password_verify($password, (string) $admin['password_hash'])) {
            self::registerFailure();

            return ['ok' => false, 'message' => 'Usuário ou senha inválidos.'];
        }

        // Rehash transparente quando o custo do bcrypt mudar.
        if (password_needs_rehash((string) $admin['password_hash'], PASSWORD_BCRYPT)) {
            Admin::updatePassword((int) $admin['id'], $password);
        }

        self::clearFailures();
        Session::regenerate();
        Session::set(self::SESSION_KEY, (int) $admin['id']);
        Session::set('admin_name', (string) $admin['display_name']);
        Session::set('admin_username', (string) $admin['username']);
        Session::set('admin_last_activity', time());

        Admin::touchLogin((int) $admin['id']);

        return ['ok' => true, 'message' => 'Login realizado com sucesso.'];
    }

    public static function check(): bool
    {
        $id = Session::get(self::SESSION_KEY);

        if ($id === null) {
            return false;
        }

        $lastActivity = (int) Session::get('admin_last_activity', 0);

        if ($lastActivity > 0 && (time() - $lastActivity) > self::IDLE_SECONDS) {
            self::logout();

            return false;
        }

        Session::set('admin_last_activity', time());

        return true;
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return $id === null ? null : (int) $id;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function user(): ?array
    {
        $id = self::id();

        return $id === null ? null : Admin::find($id);
    }

    public static function name(): string
    {
        return (string) Session::get('admin_name', 'Administrador');
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::forget('admin_name');
        Session::forget('admin_username');
        Session::forget('admin_last_activity');
        Session::regenerate();
    }

    /**
     * Exige autenticação para páginas HTML (redireciona ao login).
     */
    public static function requirePage(): void
    {
        if (self::check()) {
            return;
        }

        header('Location: ' . base_url('admin/'));
        exit;
    }

    /**
     * Exige autenticação para endpoints JSON (responde 401).
     */
    public static function requireApi(): void
    {
        if (self::check()) {
            return;
        }

        Response::error('Autenticação necessária. Faça login no painel.', null, 401);
    }

    private static function registerFailure(): void
    {
        $attempts = (int) Session::get(self::ATTEMPTS_KEY, 0) + 1;
        Session::set(self::ATTEMPTS_KEY, $attempts);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Session::set(self::LOCK_KEY, time() + self::LOCK_SECONDS);
            Session::set(self::ATTEMPTS_KEY, 0);
        }
    }

    private static function clearFailures(): void
    {
        Session::forget(self::ATTEMPTS_KEY);
        Session::forget(self::LOCK_KEY);
    }

    /**
     * @return array{attempts:int,max:int,locked_until:int}
     */
    public static function lockInfo(): array
    {
        return [
            'attempts' => (int) Session::get(self::ATTEMPTS_KEY, 0),
            'max' => self::MAX_ATTEMPTS,
            'locked_until' => (int) Session::get(self::LOCK_KEY, 0),
        ];
    }
}
