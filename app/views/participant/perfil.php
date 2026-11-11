<?php
/**
 * Perfil profissional do participante.
 *
 * @var array      $participant
 * @var array      $room
 * @var array      $profileData        Resultado de ProfileService::viewData()
 * @var array      $errors
 * @var array|null $flash
 * @var bool       $editable
 * @var array      $areas       slug => rótulo
 * @var array      $levels      código => rótulo
 */
use App\Core\Csrf;
use App\Services\ProfileService;
use App\Services\RoomService;

$profileData = $profileData ?? ['area' => '', 'headline' => '', 'is_complete' => false, 'skills' => [], 'experiences' => [], 'educations' => []];
$errors = $errors ?? [];
$flash = $flash ?? null;
$editable = $editable ?? false;
$areas = $areas ?? [];
$levels = $levels ?? [];
$status = (string) $room['status'];

$areaLabel = $profileData['area'] !== '' && isset($areas[$profileData['area']]) ? $areas[$profileData['area']] : 'Área não informada';
$levelLabel = static fn (string $code): string => $levels[$code] ?? $code;
?>

<?php if ($flash !== null): ?>
    <div class="tm-alert tm-alert--<?= e($flash['type']) ?> tm-mb-3" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<?php if ($errors !== []): ?>
    <div class="tm-alert tm-alert--danger tm-mb-3" role="alert">
        <strong>Corrija os itens abaixo:</strong>
        <ul style="margin:.5rem 0 0;padding-left:1.1rem">
            <?php foreach ($errors as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="tm-flex-between tm-wrap tm-gap-sm tm-mb-3">
    <div>
        <span class="tm-text-secondary tm-text-sm">Perfil de</span>
        <h1 style="font-size:1.6rem;margin:.1rem 0"><?= e((string) $participant['nickname']) ?></h1>
        <span class="tm-badge <?= e(RoomService::statusBadgeClass($status)) ?>">
            <?= e(RoomService::statusLabel($status)) ?>
        </span>
        <?php if ($profileData['is_complete']): ?>
            <span class="tm-badge tm-badge--success">Perfil finalizado</span>
        <?php endif; ?>
    </div>
    <a class="tm-btn tm-btn--secondary tm-btn--sm" href="<?= e(base_url('sala.php')) ?>">← Voltar para a sala</a>
</div>

<?php if (!$editable): ?>
    <div class="tm-card tm-reveal">
        <div class="tm-card__header">
            <div>
                <h2 class="tm-card__title">Perfil enviado</h2>
                <p class="tm-card__subtitle">A edição fica disponível apenas durante a montagem dos perfis.</p>
            </div>
        </div>

        <div class="tm-flex-between tm-mb-2">
            <span class="tm-text-secondary">Área profissional</span>
            <strong><?= e($areaLabel) ?></strong>
        </div>
        <?php if ($profileData['headline'] !== ''): ?>
            <p class="tm-text-secondary"><?= e($profileData['headline']) ?></p>
        <?php endif; ?>

        <h3 class="tm-card__title tm-mt-3">Habilidades</h3>
        <?php if ($profileData['skills'] === []): ?>
            <p class="tm-text-secondary tm-text-sm tm-mb-0">Nenhuma habilidade informada.</p>
        <?php else: ?>
            <div class="tm-flex tm-gap-sm tm-wrap tm-mt-2">
                <?php foreach ($profileData['skills'] as $skill): ?>
                    <span class="tm-badge tm-badge--primary">
                        <?= e((string) $skill['name']) ?> · <?= e($levelLabel((string) $skill['level'])) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h3 class="tm-card__title tm-mt-3">Experiências</h3>
        <?php if ($profileData['experiences'] === []): ?>
            <p class="tm-text-secondary tm-text-sm tm-mb-0">Nenhuma experiência informada.</p>
        <?php else: ?>
            <?php foreach ($profileData['experiences'] as $experience): ?>
                <div class="tm-mb-2">
                    <strong><?= e((string) $experience['title']) ?></strong>
                    <?php if (!empty($experience['description'])): ?>
                        <div class="tm-text-secondary tm-text-sm"><?= nl2br(e((string) $experience['description'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($experience['competencies'])): ?>
                        <div class="tm-text-secondary tm-text-sm">Competências: <?= e((string) $experience['competencies']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h3 class="tm-card__title tm-mt-3">Formações</h3>
        <?php if ($profileData['educations'] === []): ?>
            <p class="tm-text-secondary tm-text-sm tm-mb-0">Nenhuma formação informada.</p>
        <?php else: ?>
            <?php foreach ($profileData['educations'] as $education): ?>
                <div class="tm-mb-2">
                    <strong><?= e((string) $education['course']) ?></strong>
                    <div class="tm-text-secondary tm-text-sm"><?= e((string) $education['institution']) ?></div>
                    <?php if (!empty($education['description'])): ?>
                        <div class="tm-text-secondary tm-text-sm"><?= e((string) $education['description']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php else: ?>
    <form method="post" action="<?= e(base_url('perfil.php')) ?>" id="profile-form" novalidate>
        <?= Csrf::field() ?>

        <div class="tm-card tm-mb-3">
            <div class="tm-card__header">
                <div>
                    <h2 class="tm-card__title">Dados profissionais</h2>
                    <p class="tm-card__subtitle">Use apenas informações fictícias. Nada de dados pessoais reais.</p>
                </div>
            </div>

            <div class="tm-field">
                <label class="tm-label" for="area">Área profissional *</label>
                <select class="tm-input" id="area" name="area" required>
                    <option value="">Selecione…</option>
                    <?php foreach ($areas as $value => $label): ?>
                        <option value="<?= e((string) $value) ?>" <?= $profileData['area'] === (string) $value ? 'selected' : '' ?>>
                            <?= e((string) $label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="tm-field">
                <label class="tm-label" for="headline">Resumo do perfil (opcional)</label>
                <input class="tm-input" type="text" id="headline" name="headline" maxlength="120"
                       value="<?= e($profileData['headline']) ?>"
                       placeholder="Ex.: Estudante de ADS com foco em front-end">
            </div>
        </div>

        <div class="tm-card tm-mb-3">
            <div class="tm-card__header">
                <div>
                    <h2 class="tm-card__title">Habilidades</h2>
                    <p class="tm-card__subtitle">Até <?= ProfileService::MAX_SKILLS ?> habilidades, com nível de domínio.</p>
                </div>
            </div>

            <div data-tm-rows>
                <?php
                $skillRows = $profileData['skills'] !== [] ? $profileData['skills'] : [['name' => '', 'level' => 'BASIC']];
                foreach ($skillRows as $index => $skill):
                    ?>
                    <div class="tm-row" data-tm-row>
                        <input class="tm-input" type="text" name="skills[<?= (int) $index ?>][name]"
                               value="<?= e((string) $skill['name']) ?>" maxlength="60" placeholder="Ex.: PHP">
                        <select class="tm-input" name="skills[<?= (int) $index ?>][level]">
                            <?php foreach ($levels as $code => $label): ?>
                                <option value="<?= e((string) $code) ?>" <?= (string) $skill['level'] === (string) $code ? 'selected' : '' ?>>
                                    <?= e((string) $label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="tm-btn tm-btn--ghost tm-btn--sm" data-tm-remove-row aria-label="Remover habilidade">Remover</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="tm-btn tm-btn--secondary tm-btn--sm tm-mt-2" data-tm-add-row="tpl-skill">+ Adicionar habilidade</button>
        </div>

        <div class="tm-card tm-mb-3">
            <div class="tm-card__header">
                <div>
                    <h2 class="tm-card__title">Experiências</h2>
                    <p class="tm-card__subtitle">Projetos, trabalhos, monitorias ou atividades voluntárias. Até <?= ProfileService::MAX_EXPERIENCES ?>.</p>
                </div>
            </div>

            <div data-tm-rows>
                <?php
                $experienceRows = $profileData['experiences'] !== [] ? $profileData['experiences'] : [['title' => '', 'description' => '', 'competencies' => '']];
                foreach ($experienceRows as $index => $experience):
                    ?>
                    <div class="tm-row tm-row--stack" data-tm-row>
                        <input class="tm-input" type="text" name="experiences[<?= (int) $index ?>][title]"
                               value="<?= e((string) $experience['title']) ?>" maxlength="120"
                               placeholder="Cargo ou atividade (ex.: Monitoria de programação)">
                        <textarea class="tm-input" name="experiences[<?= (int) $index ?>][description]" rows="2"
                                  maxlength="1000" placeholder="O que você fez"><?= e((string) $experience['description']) ?></textarea>
                        <input class="tm-input" type="text" name="experiences[<?= (int) $index ?>][competencies]"
                               value="<?= e((string) $experience['competencies']) ?>" maxlength="255"
                               placeholder="Competências (ex.: JavaScript, trabalho em equipe)">
                        <button type="button" class="tm-btn tm-btn--ghost tm-btn--sm" data-tm-remove-row aria-label="Remover experiência">Remover</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="tm-btn tm-btn--secondary tm-btn--sm tm-mt-2" data-tm-add-row="tpl-experience">+ Adicionar experiência</button>
        </div>

        <div class="tm-card tm-mb-3">
            <div class="tm-card__header">
                <div>
                    <h2 class="tm-card__title">Formações</h2>
                    <p class="tm-card__subtitle">Cursos, técnico ou graduação. Até <?= ProfileService::MAX_EDUCATIONS ?>.</p>
                </div>
            </div>

            <div data-tm-rows>
                <?php
                $educationRows = $profileData['educations'] !== [] ? $profileData['educations'] : [['course' => '', 'institution' => '', 'description' => '']];
                foreach ($educationRows as $index => $education):
                    ?>
                    <div class="tm-row tm-row--stack" data-tm-row>
                        <input class="tm-input" type="text" name="educations[<?= (int) $index ?>][course]"
                               value="<?= e((string) $education['course']) ?>" maxlength="120"
                               placeholder="Curso (ex.: Técnico em Informática)">
                        <input class="tm-input" type="text" name="educations[<?= (int) $index ?>][institution]"
                               value="<?= e((string) $education['institution']) ?>" maxlength="120"
                               placeholder="Instituição (ex.: Escola Estadual Fictícia)">
                        <input class="tm-input" type="text" name="educations[<?= (int) $index ?>][description]"
                               value="<?= e((string) $education['description']) ?>" maxlength="255"
                               placeholder="Observação (opcional)">
                        <button type="button" class="tm-btn tm-btn--ghost tm-btn--sm" data-tm-remove-row aria-label="Remover formação">Remover</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="tm-btn tm-btn--secondary tm-btn--sm tm-mt-2" data-tm-add-row="tpl-education">+ Adicionar formação</button>
        </div>

        <div class="tm-card">
            <div class="tm-flex-between tm-wrap tm-gap-sm">
                <button type="submit" name="action" value="save" class="tm-btn tm-btn--secondary">Salvar rascunho</button>
                <button type="submit" name="action" value="complete" class="tm-btn tm-btn--primary tm-btn--lg"
                        data-tm-confirm-submit="Finalizar o perfil? Depois de finalizado, a edição só é possível enquanto o organizador mantiver a etapa de perfis.">
                    Finalizar perfil
                </button>
            </div>
        </div>
    </form>

    <template id="tpl-skill">
        <div class="tm-row" data-tm-row>
            <input class="tm-input" type="text" name="skills[__INDEX__][name]" maxlength="60" placeholder="Ex.: PHP">
            <select class="tm-input" name="skills[__INDEX__][level]">
                <?php foreach ($levels as $code => $label): ?>
                    <option value="<?= e((string) $code) ?>"><?= e((string) $label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="tm-btn tm-btn--ghost tm-btn--sm" data-tm-remove-row aria-label="Remover habilidade">Remover</button>
        </div>
    </template>

    <template id="tpl-experience">
        <div class="tm-row tm-row--stack" data-tm-row>
            <input class="tm-input" type="text" name="experiences[__INDEX__][title]" maxlength="120" placeholder="Cargo ou atividade">
            <textarea class="tm-input" name="experiences[__INDEX__][description]" rows="2" maxlength="1000" placeholder="O que você fez"></textarea>
            <input class="tm-input" type="text" name="experiences[__INDEX__][competencies]" maxlength="255" placeholder="Competências">
            <button type="button" class="tm-btn tm-btn--ghost tm-btn--sm" data-tm-remove-row aria-label="Remover experiência">Remover</button>
        </div>
    </template>

    <template id="tpl-education">
        <div class="tm-row tm-row--stack" data-tm-row>
            <input class="tm-input" type="text" name="educations[__INDEX__][course]" maxlength="120" placeholder="Curso">
            <input class="tm-input" type="text" name="educations[__INDEX__][institution]" maxlength="120" placeholder="Instituição">
            <input class="tm-input" type="text" name="educations[__INDEX__][description]" maxlength="255" placeholder="Observação (opcional)">
            <button type="button" class="tm-btn tm-btn--ghost tm-btn--sm" data-tm-remove-row aria-label="Remover formação">Remover</button>
        </div>
    </template>

    <script>
    (function () {
        'use strict';
        var counter = 1000;

        document.querySelectorAll('[data-tm-add-row]').forEach(function (button) {
            button.addEventListener('click', function () {
                var template = document.getElementById(button.getAttribute('data-tm-add-row'));
                if (!template) return;
                var html = template.innerHTML.replace(/__INDEX__/g, String(counter++));
                var holder = document.createElement('div');
                holder.innerHTML = html.trim();
                var row = holder.firstElementChild;
                if (!row) return;
                var container = button.closest('.tm-card').querySelector('[data-tm-rows]');
                if (container) container.appendChild(row);
                var input = row.querySelector('input, textarea, select');
                if (input) input.focus();
            });
        });

        document.addEventListener('click', function (event) {
            var remove = event.target.closest ? event.target.closest('[data-tm-remove-row]') : null;
            if (!remove) return;
            var row = remove.closest('[data-tm-row]');
            if (row) row.remove();
        });

        document.querySelectorAll('[data-tm-confirm-submit]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                if (button.dataset.confirmed === '1') return;
                event.preventDefault();
                TM.confirm(button.getAttribute('data-tm-confirm-submit'), 'Finalizar').then(function (ok) {
                    if (ok) {
                        button.dataset.confirmed = '1';
                        button.click();
                    }
                });
            });
        });
    })();
    </script>
<?php endif; ?>
