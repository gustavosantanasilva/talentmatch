<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Models\Participant;

/**
 * Autenticação dos participantes.
 *
 * A identidade vive na sessão (para navegação entre páginas) e/ou no
 * cabeçalho `X-Participant-Token` (para chamadas de API). O token enviado
 * pelo navegador é comparado apenas com o hash armazenado no banco.
 */
final class ParticipantAuth
{
    private const SESSION_ID = 'participant_id';

    private const SESSION_TOKEN = 'participant_token';

    private const SESSION_ROOM = 'participant_room_id';

    /**
     * Registra a sessão do participante após entrar na sala.
     *
     * @param array<string,mixed> $participant
     */
    public static function login(array $participant, string $token): void
    {
        Session::regenerate();
        Session::set(self::SESSION_ID, (int) $participant['id']);
        Session::set(self::SESSION_TOKEN, $token);
        Session::set(self::SESSION_ROOM, (int) $participant['room_id']);
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_ID);
        Session::forget(self::SESSION_TOKEN);
        Session::forget(self::SESSION_ROOM);
        Session::regenerate();
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_ID);

        return $id === null ? null : (int) $id;
    }

    public static function roomId(): ?int
    {
        $roomId = Session::get(self::SESSION_ROOM);

        return $roomId === null ? null : (int) $roomId;
    }

    public static function token(): string
    {
        return (string) Session::get(self::SESSION_TOKEN, '');
    }

    /**
     * Participante atual, validado no banco. Encerra a sessão se não existir.
     *
     * @return array<string,mixed>|null
     */
    public static function current(): ?array
    {
        $id = self::id();

        if ($id === null) {
            return null;
        }

        $participant = Participant::find($id);

        if ($participant === null) {
            self::logout();

            return null;
        }

        return $participant;
    }

    public static function check(): bool
    {
        return self::current() !== null;
    }

    /**
     * Resolve o participante pelo cabeçalho de token ou pela sessão.
     *
     * @return array<string,mixed>|null
     */
    public static function resolve(): ?array
    {
        $token = (string) ($_SERVER['HTTP_X_PARTICIPANT_TOKEN'] ?? '');

        if ($token !== '') {
            $participant = Participant::findByTokenHash(hash('sha256', $token));

            if ($participant !== null) {
                return $participant;
            }
        }

        return self::current();
    }

    /**
     * Exige um participante autenticado para páginas HTML.
     *
     * @return array<string,mixed>
     */
    public static function requirePage(): array
    {
        $participant = self::current();

        if ($participant === null) {
            flash_set('warning', 'Entre em uma sala para continuar.');
            header('Location: ' . base_url('/'));
            exit;
        }

        return $participant;
    }

    /**
     * Exige um participante autenticado para endpoints JSON.
     *
     * @return array<string,mixed>
     */
    public static function requireApi(): array
    {
        $participant = self::resolve();

        if ($participant === null) {
            Response::error('Participante não autenticado. Entre na sala novamente.', null, 401);
        }

        return $participant;
    }
}
