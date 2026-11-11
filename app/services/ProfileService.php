<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Education;
use App\Models\Experience;
use App\Models\GameEvent;
use App\Models\Participant;
use App\Models\Profile;
use App\Models\Skill;

/**
 * Regras de negócio do perfil profissional.
 *
 * O perfil é composto por dados profissionais fictícios: área, resumo,
 * habilidades, experiências e formações. Toda validação acontece no servidor.
 */
final class ProfileService
{
    public const MAX_SKILLS = 20;

    public const MAX_EXPERIENCES = 10;

    public const MAX_EDUCATIONS = 10;

    /** Níveis aceitos de habilidade. */
    public const LEVELS = ['BASIC', 'INTERMEDIATE', 'ADVANCED'];

    /** O perfil só é editável durante a etapa de montagem de perfis. */
    public static function isEditable(string $roomStatus): bool
    {
        return $roomStatus === 'PROFILES';
    }

    /**
     * Dados do perfil prontos para a view (com valores padrão quando vazio).
     *
     * @return array{area:string,headline:string,is_complete:bool,skills:array<int,array<string,mixed>>,experiences:array<int,array<string,mixed>>,educations:array<int,array<string,mixed>>}
     */
    public static function viewData(int $participantId): array
    {
        $profile = Profile::findByParticipant($participantId);

        if ($profile === null) {
            return [
                'area' => '',
                'headline' => '',
                'is_complete' => false,
                'skills' => [],
                'experiences' => [],
                'educations' => [],
            ];
        }

        $profileId = (int) $profile['id'];

        return [
            'area' => (string) $profile['area'],
            'headline' => (string) ($profile['headline'] ?? ''),
            'is_complete' => (bool) $profile['is_complete'],
            'skills' => Skill::listByProfile($profileId),
            'experiences' => Experience::listByProfile($profileId),
            'educations' => Education::listByProfile($profileId),
        ];
    }

    /**
     * Salva o perfil. Quando $complete, marca como finalizado e o participante
     * passa a "READY".
     *
     * @param array<string,mixed> $input
     * @return array{ok:bool,message:string,errors:array<int,string>}
     */
    public static function persist(int $participantId, array $input, bool $complete): array
    {
        $participant = Participant::find($participantId);

        if ($participant === null) {
            return ['ok' => false, 'message' => 'Participante não encontrado.', 'errors' => []];
        }

        $clean = self::clean($input);
        $errors = self::validate($clean, $complete);

        if ($errors !== []) {
            return ['ok' => false, 'message' => $errors[0], 'errors' => $errors];
        }

        $roomId = (int) $participant['room_id'];

        Database::transaction(static function () use ($participantId, $roomId, $clean, $complete): void {
            $profile = Profile::firstOrCreate($participantId);
            $profileId = (int) $profile['id'];

            Profile::update($profileId, $clean['area'], $clean['headline'], $complete);
            Skill::replaceAll($profileId, $clean['skills']);
            Experience::replaceAll($profileId, $clean['experiences']);
            Education::replaceAll($profileId, $clean['educations']);

            Participant::setStatus($participantId, $complete ? 'READY' : 'PROFILE_PENDING');

            GameEvent::log($roomId, $complete ? 'PROFILE_COMPLETED' : 'PROFILE_SAVED', $participantId, [
                'skills' => count($clean['skills']),
                'experiences' => count($clean['experiences']),
                'educations' => count($clean['educations']),
            ]);
        });

        return [
            'ok' => true,
            'message' => $complete
                ? 'Perfil finalizado com sucesso. Aguarde as próximas etapas.'
                : 'Rascunho do perfil salvo.',
            'errors' => [],
        ];
    }

    /**
     * Normaliza a entrada do formulário e descarta linhas totalmente vazias.
     *
     * @param array<string,mixed> $input
     * @return array{area:string,headline:string,skills:array<int,array{name:string,name_norm:string,level:string}>,experiences:array<int,array{title:string,description:string,competencies:string}>,educations:array<int,array{course:string,institution:string,description:string}>}
     */
    public static function clean(array $input): array
    {
        $area = trim((string) ($input['area'] ?? ''));
        $headline = trim((string) ($input['headline'] ?? ''));

        $skills = [];
        foreach ((array) ($input['skills'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $level = strtoupper(trim((string) ($row['level'] ?? 'BASIC')));

            if ($name === '') {
                continue;
            }

            $skills[] = [
                'name' => $name,
                'name_norm' => normalize_skill($name),
                'level' => $level,
            ];
        }

        $experiences = [];
        foreach ((array) ($input['experiences'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            $competencies = trim((string) ($row['competencies'] ?? ''));

            if ($title === '' && $description === '' && $competencies === '') {
                continue;
            }

            $experiences[] = [
                'title' => $title,
                'description' => $description,
                'competencies' => $competencies,
            ];
        }

        $educations = [];
        foreach ((array) ($input['educations'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $course = trim((string) ($row['course'] ?? ''));
            $institution = trim((string) ($row['institution'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));

            if ($course === '' && $institution === '' && $description === '') {
                continue;
            }

            $educations[] = [
                'course' => $course,
                'institution' => $institution,
                'description' => $description,
            ];
        }

        return [
            'area' => $area,
            'headline' => $headline,
            'skills' => $skills,
            'experiences' => $experiences,
            'educations' => $educations,
        ];
    }

    /**
     * @param array{area:string,headline:string,skills:array<int,array{name:string,name_norm:string,level:string}>,experiences:array<int,array{title:string,description:string,competencies:string}>,educations:array<int,array{course:string,institution:string,description:string}>} $clean
     * @return array<int,string>
     */
    public static function validate(array $clean, bool $complete): array
    {
        $errors = [];
        $areas = array_keys((array) config('app.areas', []));

        if ($clean['area'] === '') {
            $errors[] = 'Selecione a sua área profissional.';
        } elseif (!in_array($clean['area'], $areas, true)) {
            $errors[] = 'Área profissional inválida.';
        }

        if (mb_strlen($clean['headline']) > 120) {
            $errors[] = 'O resumo do perfil deve ter no máximo 120 caracteres.';
        }

        if (count($clean['skills']) > self::MAX_SKILLS) {
            $errors[] = 'Informe no máximo ' . self::MAX_SKILLS . ' habilidades.';
        }

        $seen = [];
        foreach ($clean['skills'] as $skill) {
            $length = mb_strlen($skill['name']);

            if ($length < 2 || $length > 60) {
                $errors[] = 'Cada habilidade deve ter entre 2 e 60 caracteres.';
            }

            if (!in_array($skill['level'], self::LEVELS, true)) {
                $errors[] = 'Nível de habilidade inválido.';
            }

            if ($skill['name_norm'] !== '') {
                if (isset($seen[$skill['name_norm']])) {
                    $errors[] = 'A habilidade "' . $skill['name'] . '" está repetida.';
                }
                $seen[$skill['name_norm']] = true;
            }
        }

        if (count($clean['experiences']) > self::MAX_EXPERIENCES) {
            $errors[] = 'Informe no máximo ' . self::MAX_EXPERIENCES . ' experiências.';
        }

        foreach ($clean['experiences'] as $experience) {
            $titleLength = mb_strlen($experience['title']);

            if ($titleLength < 2 || $titleLength > 120) {
                $errors[] = 'Cada experiência deve ter um título de 2 a 120 caracteres.';
            }

            if (mb_strlen($experience['description']) > 1000) {
                $errors[] = 'A descrição de uma experiência deve ter no máximo 1000 caracteres.';
            }

            if (mb_strlen($experience['competencies']) > 255) {
                $errors[] = 'As competências de uma experiência devem ter no máximo 255 caracteres.';
            }
        }

        if (count($clean['educations']) > self::MAX_EDUCATIONS) {
            $errors[] = 'Informe no máximo ' . self::MAX_EDUCATIONS . ' formações.';
        }

        foreach ($clean['educations'] as $education) {
            if (mb_strlen($education['course']) < 2 || mb_strlen($education['course']) > 120) {
                $errors[] = 'Cada formação deve ter um curso de 2 a 120 caracteres.';
            }

            if (mb_strlen($education['institution']) < 2 || mb_strlen($education['institution']) > 120) {
                $errors[] = 'Cada formação deve ter uma instituição de 2 a 120 caracteres.';
            }

            if (mb_strlen($education['description']) > 255) {
                $errors[] = 'A descrição de uma formação deve ter no máximo 255 caracteres.';
            }
        }

        if ($complete && $clean['skills'] === []) {
            $errors[] = 'Inclua ao menos uma habilidade para finalizar o perfil.';
        }

        return array_values(array_unique($errors));
    }
}
