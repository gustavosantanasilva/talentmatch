<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Participantes de uma sala.
 *
 * O token de acesso nunca é armazenado em texto puro: apenas o hash SHA-256
 * (coluna `token_hash`). Nenhuma consulta pública devolve esse hash.
 */
final class Participant
{
    /** Estados possíveis de um participante. */
    public const STATUSES = ['WAITING', 'PROFILE_PENDING', 'READY'];

    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::STATUSES, true);
    }

    /**
     * Cria um participante já com o hash do token.
     *
     * @return array<string,mixed>
     */
    public static function create(int $roomId, string $nickname, string $tokenHash, string $avatar = '🙂', string $avatarColor = '#087F5B'): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'INSERT INTO participants (room_id, nickname, avatar, avatar_color, token_hash, status)
             VALUES (:room_id, :nickname, :avatar, :avatar_color, :token_hash, "WAITING")'
        );
        $statement->execute([
            'room_id' => $roomId,
            'nickname' => $nickname,
            'avatar' => $avatar,
            'avatar_color' => $avatarColor,
            'token_hash' => $tokenHash,
        ]);

        $participant = self::find((int) $pdo->lastInsertId());

        if ($participant === null) {
            throw new \RuntimeException('Falha ao recuperar o participante recém-criado.');
        }

        return $participant;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, room_id, nickname, avatar, avatar_color, status, is_active, joined_at, last_seen
             FROM participants WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $participant = $statement->fetch();

        return $participant === false ? null : $participant;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function findByTokenHash(string $tokenHash): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, room_id, nickname, avatar, avatar_color, status, is_active, joined_at, last_seen
             FROM participants WHERE token_hash = :token_hash LIMIT 1'
        );
        $statement->execute(['token_hash' => $tokenHash]);
        $participant = $statement->fetch();

        return $participant === false ? null : $participant;
    }

    public static function nicknameExists(int $roomId, string $nickname): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT 1 FROM participants WHERE room_id = :room_id AND nickname = :nickname LIMIT 1'
        );
        $statement->execute(['room_id' => $roomId, 'nickname' => $nickname]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * Lista pública dos participantes (sem token).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function listByRoom(int $roomId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, nickname, avatar, avatar_color, status, is_active, joined_at
             FROM participants
             WHERE room_id = :room_id
             ORDER BY joined_at ASC, id ASC'
        );
        $statement->execute(['room_id' => $roomId]);

        return $statement->fetchAll();
    }

    public static function countByRoom(int $roomId): int
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM participants WHERE room_id = :room_id'
        );
        $statement->execute(['room_id' => $roomId]);

        return (int) $statement->fetchColumn();
    }

    public static function touch(int $id): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE participants SET last_seen = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        if (!self::isValidStatus($status)) {
            throw new \InvalidArgumentException("Status inválido de participante: {$status}");
        }

        $statement = Database::connection()->prepare(
            'UPDATE participants SET status = :status WHERE id = :id'
        );
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * Atualiza apelido e avatar do participante.
     */
    public static function updateIdentity(int $id, string $nickname, string $avatar, string $avatarColor): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE participants
             SET nickname = :nickname, avatar = :avatar, avatar_color = :avatar_color
             WHERE id = :id'
        );
        $statement->execute([
            'nickname' => $nickname,
            'avatar' => $avatar,
            'avatar_color' => $avatarColor,
            'id' => $id,
        ]);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'WAITING' => 'Na sala de espera',
            'PROFILE_PENDING' => 'Montando o perfil',
            'READY' => 'Perfil pronto',
            default => $status,
        };
    }
}
