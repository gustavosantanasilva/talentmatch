<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Administradores do painel.
 */
final class Admin
{
    /**
     * @return array<string,mixed>|null
     */
    public static function findByUsername(string $username): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, username, password_hash, display_name, last_login_at, created_at
             FROM admins WHERE username = :username LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $admin = $statement->fetch();

        return $admin === false ? null : $admin;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, username, display_name, last_login_at, created_at
             FROM admins WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $admin = $statement->fetch();

        return $admin === false ? null : $admin;
    }

    public static function touchLogin(int $id): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE admins SET last_login_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public static function updatePassword(int $id, string $plainPassword): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE admins SET password_hash = :hash WHERE id = :id'
        );
        $statement->execute([
            'hash' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'id' => $id,
        ]);
    }

    public static function count(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    }
}
