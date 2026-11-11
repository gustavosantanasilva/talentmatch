<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Experiências profissionais (ou projetos equivalentes) de um perfil.
 */
final class Experience
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listByProfile(int $profileId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, title, description, competencies
             FROM experiences
             WHERE profile_id = :profile_id
             ORDER BY id ASC'
        );
        $statement->execute(['profile_id' => $profileId]);

        return $statement->fetchAll();
    }

    /**
     * @param array<int,array{title:string,description:string,competencies:string}> $experiences
     */
    public static function replaceAll(int $profileId, array $experiences): void
    {
        $pdo = Database::connection();
        $pdo->prepare('DELETE FROM experiences WHERE profile_id = :profile_id')
            ->execute(['profile_id' => $profileId]);

        if ($experiences === []) {
            return;
        }

        $statement = $pdo->prepare(
            'INSERT INTO experiences (profile_id, title, description, competencies)
             VALUES (:profile_id, :title, :description, :competencies)'
        );

        foreach ($experiences as $experience) {
            $statement->execute([
                'profile_id' => $profileId,
                'title' => $experience['title'],
                'description' => $experience['description'] === '' ? null : $experience['description'],
                'competencies' => $experience['competencies'] === '' ? null : $experience['competencies'],
            ]);
        }
    }
}
