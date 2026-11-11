<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Salas (partidas).
 */
final class Room
{
    /** Estados válidos de uma partida, na ordem do fluxo. */
    public const STATES = ['WAITING', 'PROFILES', 'GENERATING', 'JOBS', 'RESULTS', 'FINISHED'];

    public static function isValidState(string $state): bool
    {
        return in_array($state, self::STATES, true);
    }

    public static function codeExists(string $code): bool
    {
        $statement = Database::connection()->prepare('SELECT 1 FROM rooms WHERE code = :code LIMIT 1');
        $statement->execute(['code' => $code]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * @return array<string,mixed>
     */
    public static function create(int $adminId, string $code): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'INSERT INTO rooms (code, status, admin_id) VALUES (:code, "WAITING", :admin_id)'
        );
        $statement->execute(['code' => $code, 'admin_id' => $adminId]);

        $room = self::find((int) $pdo->lastInsertId());

        if ($room === null) {
            throw new \RuntimeException('Falha ao recuperar a sala recém-criada.');
        }

        return $room;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM rooms WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $room = $statement->fetch();

        return $room === false ? null : $room;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function findByCode(string $code): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM rooms WHERE code = :code LIMIT 1');
        $statement->execute(['code' => strtoupper($code)]);
        $room = $statement->fetch();

        return $room === false ? null : $room;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listByAdmin(int $adminId, int $limit = 50): array
    {
        $statement = Database::connection()->prepare(
            'SELECT r.*,
                    (SELECT COUNT(*) FROM participants p WHERE p.room_id = r.id) AS participants_count,
                    (SELECT COUNT(*) FROM jobs j WHERE j.room_id = r.id) AS jobs_count
             FROM rooms r
             WHERE r.admin_id = :admin_id
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT :limit'
        );
        $statement->bindValue('admin_id', $adminId, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function recent(int $limit = 20): array
    {
        $statement = Database::connection()->prepare(
            'SELECT r.*,
                    (SELECT COUNT(*) FROM participants p WHERE p.room_id = r.id) AS participants_count,
                    (SELECT COUNT(*) FROM jobs j WHERE j.room_id = r.id) AS jobs_count
             FROM rooms r
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function participantCount(int $roomId): int
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM participants WHERE room_id = :room_id'
        );
        $statement->execute(['room_id' => $roomId]);

        return (int) $statement->fetchColumn();
    }

    public static function jobsCount(int $roomId): int
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM jobs WHERE room_id = :room_id'
        );
        $statement->execute(['room_id' => $roomId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * Atualiza o estado (e campos opcionais) de uma sala.
     *
     * @param array<string,mixed> $extra
     */
    public static function updateStatus(int $roomId, string $status, array $extra = []): void
    {
        if (!self::isValidState($status)) {
            throw new \InvalidArgumentException("Estado inválido: {$status}");
        }

        $fields = ['status = :status'];
        $params = ['status' => $status, 'id' => $roomId];

        if ($status === 'PROFILES' && !array_key_exists('started_at', $extra)) {
            $extra['started_at'] = date('Y-m-d H:i:s');
        }

        if ($status === 'FINISHED' && !array_key_exists('finished_at', $extra)) {
            $extra['finished_at'] = date('Y-m-d H:i:s');
        }

        $allowed = ['current_job_index', 'jobs_source', 'started_at', 'finished_at'];

        foreach ($extra as $column => $value) {
            if (!in_array($column, $allowed, true)) {
                continue;
            }
            $fields[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $sql = 'UPDATE rooms SET ' . implode(', ', $fields) . ' WHERE id = :id';
        Database::connection()->prepare($sql)->execute($params);
    }

    /**
     * @return array{total:int,waiting:int,active:int,finished:int,participants:int}
     */
    public static function stats(?int $adminId = null): array
    {
        $pdo = Database::connection();
        $where = $adminId === null ? '' : ' WHERE admin_id = :admin_id';
        $params = $adminId === null ? [] : ['admin_id' => $adminId];

        $statement = $pdo->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'WAITING') AS waiting,
                SUM(status IN ('PROFILES','GENERATING','JOBS','RESULTS')) AS active,
                SUM(status = 'FINISHED') AS finished
             FROM rooms{$where}"
        );
        $statement->execute($params);
        $row = $statement->fetch() ?: [];

        $participants = 0;
        if ($adminId === null) {
            $participants = (int) $pdo->query('SELECT COUNT(*) FROM participants')->fetchColumn();
        } else {
            $statement = $pdo->prepare(
                'SELECT COUNT(*) FROM participants p
                 JOIN rooms r ON r.id = p.room_id
                 WHERE r.admin_id = :admin_id'
            );
            $statement->execute(['admin_id' => $adminId]);
            $participants = (int) $statement->fetchColumn();
        }

        return [
            'total' => (int) ($row['total'] ?? 0),
            'waiting' => (int) ($row['waiting'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'finished' => (int) ($row['finished'] ?? 0),
            'participants' => $participants,
        ];
    }
}
