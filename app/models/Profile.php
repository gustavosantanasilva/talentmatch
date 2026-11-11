<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Perfil profissional (1:1 com o participante).
 *
 * O perfil guarda apenas dados profissionais fictícios: área, resumo,
 * habilidades, experiências e formações. Nunca dados pessoais sensíveis.
 */
final class Profile
{
    /**
     * @return array<string,mixed>|null
     */
    public static function findByParticipant(int $participantId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT * FROM profiles WHERE participant_id = :participant_id LIMIT 1'
        );
        $statement->execute(['participant_id' => $participantId]);
        $profile = $statement->fetch();

        return $profile === false ? null : $profile;
    }

    /**
     * Busca o perfil ou cria um vazio (sem sobrescrever dados existentes).
     *
     * @return array<string,mixed>
     */
    public static function firstOrCreate(int $participantId): array
    {
        $profile = self::findByParticipant($participantId);

        if ($profile !== null) {
            return $profile;
        }

        $pdo = Database::connection();

        try {
            $pdo->prepare(
                'INSERT INTO profiles (participant_id, area, is_complete) VALUES (:participant_id, "", 0)'
            )->execute(['participant_id' => $participantId]);
        } catch (\PDOException $exception) {
            // Corrida com outro request: o índice único garante apenas um perfil.
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
        }

        $profile = self::findByParticipant($participantId);

        if ($profile === null) {
            throw new \RuntimeException('Falha ao recuperar o perfil do participante.');
        }

        return $profile;
    }

    public static function update(int $profileId, string $area, string $headline, bool $complete): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE profiles
             SET area = :area,
                 headline = :headline,
                 is_complete = :is_complete,
                 completed_at = :completed_at
             WHERE id = :id'
        );
        $statement->execute([
            'area' => $area,
            'headline' => $headline === '' ? null : $headline,
            'is_complete' => $complete ? 1 : 0,
            'completed_at' => $complete ? date('Y-m-d H:i:s') : null,
            'id' => $profileId,
        ]);
    }

    public static function completedCountForRoom(int $roomId): int
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM profiles p
             JOIN participants pa ON pa.id = p.participant_id
             WHERE pa.room_id = :room_id AND p.is_complete = 1'
        );
        $statement->execute(['room_id' => $roomId]);

        return (int) $statement->fetchColumn();
    }
}
