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

    /** Avatar e cor usados quando o valor enviado não é válido. */
    public const DEFAULT_AVATAR = '🙂';

    public static function canJoin(string $roomStatus): bool
    {
        return in_array($roomStatus, self::JOINABLE_STATES, true);
    }

    /**
     * Normaliza o avatar contra a lista configurada.
     */
    public static function normalizeAvatar(string $avatar): string
    {
        $allowed = (array) config('app.avatars', []);

        return in_array($avatar, $allowed, true) ? $avatar : self::DEFAULT_AVATAR;
    }

    /**
     * Normaliza a cor do avatar contra a lista configurada.
     */
    public static function normalizeAvatarColor(string $color): string
    {
        $allowed = (array) config('app.avatar_colors', []);
        $color = strtoupper(trim($color));

        if (in_array($color, $allowed, true)) {
            return $color;
        }

        if (preg_match('/^#[0-9A-F]{6}$/', $color) === 1) {
            return $color;
        }

        return $allowed[0] ?? '#087F5B';
    }

    /**
     * Entra em uma sala com um apelido e avatar.
     *
     * @param array<string,mixed> $room
     * @return array{ok:bool,message:string,code?:string,participant?:array<string,mixed>,token?:string}
     */
    public static function join(array $room, string $nickname, string $avatar = '', string $avatarColor = ''): array
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

        $avatar = self::normalizeAvatar($avatar);
        $avatarColor = self::normalizeAvatarColor($avatarColor);
        $token = bin2hex(random_bytes(self::TOKEN_BYTES));
        $tokenHash = hash('sha256', $token);

        try {
            $participant = Database::transaction(static function () use ($room, $nickname, $avatar, $avatarColor, $tokenHash): array {
                $created = Participant::create((int) $room['id'], $nickname, $tokenHash, $avatar, $avatarColor);
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
     * Atualiza apelido e avatar de um participante.
     *
     * @return array{ok:bool,message:string,participant?:array<string,mixed>}
     */
    public static function updateIdentity(int $participantId, string $nickname, string $avatar, string $avatarColor): array
    {
        $participant = Participant::find($participantId);

        if ($participant === null) {
            return ['ok' => false, 'message' => 'Participante não encontrado.'];
        }

        $nickname = trim($nickname);
        $validator = Validator::make(['nickname' => $nickname], ['nickname' => 'required|nickname']);

        if ($validator->fails()) {
            return ['ok' => false, 'message' => $validator->firstError()];
        }

        $avatar = self::normalizeAvatar($avatar);
        $avatarColor = self::normalizeAvatarColor($avatarColor);

        try {
            Database::transaction(static function () use ($participant, $participantId, $nickname, $avatar, $avatarColor): void {
                if ((string) $participant['nickname'] !== $nickname
                    && Participant::nicknameExists((int) $participant['room_id'], $nickname)) {
                    throw new \RuntimeException('duplicate');
                }

                Participant::updateIdentity($participantId, $nickname, $avatar, $avatarColor);
                GameEvent::log((int) $participant['room_id'], 'PARTICIPANT_UPDATED', $participantId, [
                    'nickname' => $nickname,
                ]);
            });
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() === 'duplicate') {
                return ['ok' => false, 'message' => 'Este apelido já está em uso nesta sala. Escolha outro.'];
            }

            throw $exception;
        }

        return [
            'ok' => true,
            'message' => 'Seu nome e avatar foram atualizados.',
            'participant' => Participant::find($participantId),
        ];
    }

    /**
     * Lista pública (apelido, avatar e horário) — sem tokens.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function publicList(int $roomId): array
    {
        $list = [];

        foreach (Participant::listByRoom($roomId) as $participant) {
            $list[] = [
                'nickname' => (string) $participant['nickname'],
                'avatar' => (string) ($participant['avatar'] ?? self::DEFAULT_AVATAR),
                'avatar_color' => (string) ($participant['avatar_color'] ?? '#087F5B'),
                'status' => (string) $participant['status'],
                'joined_at' => (string) $participant['joined_at'],
            ];
        }

        return $list;
    }
}
