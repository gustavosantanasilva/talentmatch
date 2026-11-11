<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Habilidades de um perfil.
 */
final class Skill
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listByProfile(int $profileId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, name, name_norm, level
             FROM skills
             WHERE profile_id = :profile_id
             ORDER BY id ASC'
        );
        $statement->execute(['profile_id' => $profileId]);

        return $statement->fetchAll();
    }

    /**
     * Substitui todas as habilidades do perfil.
     *
     * @param array<int,array{name:string,name_norm:string,level:string}> $skills
     */
    public static function replaceAll(int $profileId, array $skills): void
    {
        $pdo = Database::connection();
        $pdo->prepare('DELETE FROM skills WHERE profile_id = :profile_id')
            ->execute(['profile_id' => $profileId]);

        if ($skills === []) {
            return;
        }

        $statement = $pdo->prepare(
            'INSERT INTO skills (profile_id, name, name_norm, level)
             VALUES (:profile_id, :name, :name_norm, :level)'
        );

        foreach ($skills as $skill) {
            $statement->execute([
                'profile_id' => $profileId,
                'name' => $skill['name'],
                'name_norm' => $skill['name_norm'],
                'level' => $skill['level'],
            ]);
        }
    }
}
