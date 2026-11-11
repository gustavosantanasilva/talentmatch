<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\GameEvent;
use App\Models\Room;
use RuntimeException;

/**
 * Regras de negócio relacionadas às salas.
 */
final class RoomService
{
    /** Alfabeto sem caracteres ambíguos (0/O, 1/I/L). */
    private const CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const CODE_LENGTH = 6;

    private const MAX_ATTEMPTS = 12;

    public static function generateCode(): string
    {
        $max = strlen(self::CODE_ALPHABET) - 1;
        $code = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::CODE_ALPHABET[random_int(0, $max)];
        }

        return $code;
    }

    /**
     * Cria uma sala com código único.
     *
     * @return array<string,mixed>
     */
    public static function createRoom(int $adminId): array
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = self::generateCode();

            if (Room::codeExists($code)) {
                continue;
            }

            try {
                $room = Room::create($adminId, $code);
                GameEvent::log((int) $room['id'], 'ROOM_CREATED', null, ['code' => $code]);

                return $room;
            } catch (\PDOException $exception) {
                // Colisão de código único: tenta novamente.
                if ($exception->getCode() === '23000') {
                    continue;
                }
                throw $exception;
            }
        }

        throw new RuntimeException('Não foi possível gerar um código único para a sala.');
    }

    public static function joinUrl(string $code): string
    {
        return base_url('entrar.php?room=' . strtoupper($code));
    }

    /**
     * Transição de estado com validação de permissão (somente servidor).
     *
     * @return array{ok:bool,message:string}
     */
    public static function transition(int $roomId, string $targetStatus): array
    {
        $room = Room::find($roomId);

        if ($room === null) {
            return ['ok' => false, 'message' => 'Sala não encontrada.'];
        }

        $current = (string) $room['status'];
        $allowed = self::allowedTransitions($current);

        if (!in_array($targetStatus, $allowed, true)) {
            return [
                'ok' => false,
                'message' => "Transição inválida: {$current} → {$targetStatus}.",
            ];
        }

        if ($targetStatus === 'PROFILES' && Room::participantCount($roomId) < 1) {
            return ['ok' => false, 'message' => 'É necessário pelo menos um participante para iniciar.'];
        }

        Database::transaction(static function () use ($roomId, $targetStatus, $current): void {
            Room::updateStatus($roomId, $targetStatus);
            GameEvent::log($roomId, 'STATE_CHANGED', null, [
                'from' => $current,
                'to' => $targetStatus,
            ]);
        });

        return ['ok' => true, 'message' => 'Estado atualizado com sucesso.'];
    }

    /**
     * @return array<int,string>
     */
    public static function allowedTransitions(string $current): array
    {
        return match ($current) {
            'WAITING' => ['PROFILES', 'FINISHED'],
            'PROFILES' => ['GENERATING', 'FINISHED'],
            'GENERATING' => ['JOBS', 'FINISHED'],
            'JOBS' => ['RESULTS', 'FINISHED'],
            'RESULTS' => ['FINISHED'],
            default => [],
        };
    }

    /**
     * Dados públicos/administrativos do estado de uma sala.
     *
     * @param array<string,mixed> $room
     * @return array<string,mixed>
     */
    public static function statePayload(array $room, bool $admin = false): array
    {
        $roomId = (int) $room['id'];

        $payload = [
            'id' => $roomId,
            'code' => (string) $room['code'],
            'status' => (string) $room['status'],
            'status_label' => self::statusLabel((string) $room['status']),
            'current_job_index' => (int) $room['current_job_index'],
            'jobs_source' => (string) $room['jobs_source'],
            'participants_count' => Room::participantCount($roomId),
            'jobs_count' => Room::jobsCount($roomId),
            'created_at' => (string) $room['created_at'],
        ];

        if ($admin) {
            $payload['join_url'] = self::joinUrl((string) $room['code']);
            $payload['started_at'] = $room['started_at'];
            $payload['finished_at'] = $room['finished_at'];
            $payload['allowed_transitions'] = self::allowedTransitions((string) $room['status']);
        }

        return $payload;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'WAITING' => 'Sala de espera',
            'PROFILES' => 'Criação de perfis',
            'GENERATING' => 'Gerando vagas',
            'JOBS' => 'Apresentação de vagas',
            'RESULTS' => 'Resultados',
            'FINISHED' => 'Encerrada',
            default => $status,
        };
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'WAITING' => 'tm-badge--secondary',
            'PROFILES', 'GENERATING' => 'tm-badge--primary',
            'JOBS' => 'tm-badge--gold',
            'RESULTS' => 'tm-badge--success',
            'FINISHED' => 'tm-badge--danger',
            default => '',
        };
    }
}
