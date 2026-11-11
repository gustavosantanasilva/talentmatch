<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Registro de eventos da partida (sincronização e depuração).
 */
final class GameEvent
{
    /**
     * @param array<string,mixed>|null $payload
     */
    public static function log(int $roomId, string $type, ?int $participantId = null, ?array $payload = null): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO game_events (room_id, participant_id, type, payload)
             VALUES (:room_id, :participant_id, :type, :payload)'
        );
        $statement->execute([
            'room_id' => $roomId,
            'participant_id' => $participantId,
            'type' => $type,
            'payload' => $payload === null ? null : json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function recentForRoom(int $roomId, int $limit = 30): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, type, payload, created_at
             FROM game_events
             WHERE room_id = :room_id
             ORDER BY id DESC
             LIMIT :limit'
        );
        $statement->bindValue('room_id', $roomId, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
