<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Formações acadêmicas de um perfil.
 */
final class Education
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listByProfile(int $profileId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, course, institution, description
             FROM educations
             WHERE profile_id = :profile_id
             ORDER BY id ASC'
        );
        $statement->execute(['profile_id' => $profileId]);

        return $statement->fetchAll();
    }

    /**
     * @param array<int,array{course:string,institution:string,description:string}> $educations
     */
    public static function replaceAll(int $profileId, array $educations): void
    {
        $pdo = Database::connection();
        $pdo->prepare('DELETE FROM educations WHERE profile_id = :profile_id')
            ->execute(['profile_id' => $profileId]);

        if ($educations === []) {
            return;
        }

        $statement = $pdo->prepare(
            'INSERT INTO educations (profile_id, course, institution, description)
             VALUES (:profile_id, :course, :institution, :description)'
        );

        foreach ($educations as $education) {
            $statement->execute([
                'profile_id' => $profileId,
                'course' => $education['course'],
                'institution' => $education['institution'],
                'description' => $education['description'] === '' ? null : $education['description'],
            ]);
        }
    }
}
