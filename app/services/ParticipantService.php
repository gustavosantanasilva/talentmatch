<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Validator;
use App\Models\GameEvent;
use App\Models\Participant;

/**
 * Regras de negócio dos participantes (entrada na sala, token e lista pública).
 */
final class ParticipantService
{
    /** Bytes de entropia do token entregue ao participante. */
    private const TOKEN_BYTES = 32;

    /** Estados da sala que ainda aceitam novos participantes. */
    public const JOINABLE_STATES = ['WAITING', 'PROFILES'];

    public static function canJoin(string $roomStatus): bool
    {
        return in_array($roomStatus, self::JOINABLE_STATES, true);
    }

    /**
     * Entra em uma sala com um apelido.
     *
     * @param array<string,mixed> $room
     * @return array{ok:bool,message:string,code?:string,participant?:array<string,mixed>,token?:string}
     */
    public static function join(array $room, string $nickname): array
    {
        $status = (string) $room['status'];

        if (!self::canJoin($status)) {
            return [
                'ok' => false,
                'code' => 'closed',
                'message' => 'A partida já começou e não está mais aceitando novos participantes.',
            ];
        }

        $nickname = trim($nickname);
        $validator = Validator::make(['nickname' => $nickname], ['nickname' => 'required|nickname']);

        if ($validator->fails()) {
            return ['ok' => false, 'code' => 'validation', 'message' => $validator->firstError()];
        }

        $token = bin2hex(random_bytes(self::TOKEN_BYTES));
        $tokenHash = hash('sha256', $token);

        try {
            $participant = Database::transaction(static function () use ($room, $nickname, $tokenHash): array {
                $created = Participant::create((int) $room['id'], $nickname, $tokenHash);
                GameEvent::log((int) $room['id'], 'PARTICIPANT_JOINED', (int) $created['id'], [
                    'nickname' => $nickname,
                ]);

                return $created;
            });
        } catch (\PDOException $exception) {
            // Violação do índice único (room_id, nickname).
            if ($exception->getCode() === '23000') {
                return [
                    'ok' => false,
                    'code' => 'duplicate',
                    'message' => 'Este apelido já está em uso nesta sala. Escolha outro.',
                ];
            }

            throw $exception;
        }

        return [
            'ok' => true,
            'message' => 'Você entrou na sala.',
            'participant' => $participant,
            'token' => $token,
        ];
    }

    /**
     * Lista pública (apenas apelido, status e horário) — sem tokens.
     *
     * @return array<int,array{nickname:string,status:string,joined_at:string}>
     */
    public static function publicList(int $roomId): array
    {
        $list = [];

        foreach (Participant::listByRoom($roomId) as $participant) {
            $list[] = [
                'nickname' => (string) $participant['nickname'],
                'status' => (string) $participant['status'],
                'joined_at' => (string) $participant['joined_at'],
            ];
        }

        return $list;
    }
}
