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
    <div class="gk-alert gk-alert--<?= e($flash['type'] === 'danger' ? 'danger' : $flash['type']) ?> gk-mb-3" role="status">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>

<?php if ($errors !== []): ?>
    <div class="gk-alert gk-alert--danger gk-mb-3" role="alert">
        <strong>Corrija os itens abaixo:</strong>
        <ul class="gk-mb-0" style="margin:.5rem 0 0;padding-left:1.1rem">
            <?php foreach ($errors as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="gk-toolbar gk-mb-3">
    <div>
        <span class="gk-eyebrow">Perfil de</span>
        <h1 class="gk-title-lg gk-mt-1 gk-mb-0"><?= e((string) $participant['nickname']) ?></h1>
        <div class="gk-flex-center gk-gap-sm gk-mt-1 gk-wrap">
            <span class="gk-badge <?= e(RoomService::statusBadgeClass($status)) ?>"><?= e(RoomService::statusLabel($status)) ?></span>
            <?php if ($profileData['is_complete']): ?>
                <span class="gk-badge gk-badge--green">Perfil finalizado</span>
            <?php endif; ?>
        </div>
    </div>
    <a class="gk-btn gk-btn--white gk-btn--sm" href="<?= e(base_url('sala.php')) ?>">← Voltar para a sala</a>
</div>

<?php if (!$editable): ?>
    <div class="gk-card gk-anim-pop">
        <h2 class="gk-card__title gk-mb-2">Perfil enviado</h2>
        <p class="gk-card__subtitle gk-mb-3">A edição fica disponível apenas durante a montagem dos perfis.</p>

        <div class="gk-flex-between gk-mb-2">
            <span class="gk-text-muted">Área profissional</span>
            <strong><?= e($areaLabel) ?></strong>
        </div>
        <?php if ($profileData['headline'] !== ''): ?>
            <p class="gk-text-muted"><?= e($profileData['headline']) ?></p>
        <?php endif; ?>

        <h3 class="gk-card__title gk-mt-3">Habilidades</h3>
        <?php if ($profileData['skills'] === []): ?>
            <p class="gk-text-muted gk-text-sm gk-mb-0">Nenhuma habilidade informada.</p>
        <?php else: ?>
            <div class="gk-flex gk-gap-sm gk-wrap gk-mt-2">
                <?php foreach ($profileData['skills'] as $skill): ?>
                    <span class="gk-skill-chip">
                        <?= e((string) $skill['name']) ?>
                        <span class="gk-level"><?= e($levelLabel((string) $skill['level'])) ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h3 class="gk-card__title gk-mt-3">Experiências</h3>
        <?php if ($profileData['experiences'] === []): ?>
            <p class="gk-text-muted gk-text-sm gk-mb-0">Nenhuma experiência informada.</p>
        <?php else: ?>
            <?php foreach ($profileData['experiences'] as $experience): ?>
                <div class="gk-mb-2">
                    <strong><?= e((string) $experience['title']) ?></strong>
                    <?php if (!empty($experience['description'])): ?>
                        <div class="gk-text-muted gk-text-sm"><?= nl2br(e((string) $experience['description'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($experience['competencies'])): ?>
                        <div class="gk-text-muted gk-text-sm">Competências: <?= e((string) $experience['competencies']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h3 class="gk-card__title gk-mt-3">Formações</h3>
        <?php if ($profileData['educations'] === []): ?>
            <p class="gk-text-muted gk-text-sm gk-mb-0">Nenhuma formação informada.</p>
        <?php else: ?>
            <?php foreach ($profileData['educations'] as $education): ?>
                <div class="gk-mb-2">
                    <strong><?= e((string) $education['course']) ?></strong>
                    <div class="gk-text-muted gk-text-sm"><?= e((string) $education['institution']) ?></div>
                    <?php if (!empty($education['description'])): ?>
                        <div class="gk-text-muted gk-text-sm"><?= e((string) $education['description']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php else: ?>
    <form method="post" action="<?= e(base_url('perfil.php')) ?>" id="profile-form" novalidate>
        <?= Csrf::field() ?>

        <div class="gk-card gk-mb-3 gk-anim-slide">
            <span class="gk-eyebrow">Etapa 1</span>
            <h2 class="gk-card__title gk-mt-1 gk-mb-2">Dados profissionais</h2>
            <p class="gk-card__subtitle gk-mb-3">Use apenas informações fictícias. Nada de dados pessoais reais.</p>

            <div class="gk-field">
                <label class="gk-label" for="area">Área profissional *</label>
                <select class="gk-input" id="area" name="area" required>
                    <option value="">Selecione…</option>
                    <?php foreach ($areas as $value => $label): ?>
                        <option value="<?= e((string) $value) ?>" <?= $profileData['area'] === (string) $value ? 'selected' : '' ?>>
                            <?= e((string) $label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="gk-field gk-mb-0">
                <label class="gk-label" for="headline">Resumo do perfil (opcional)</label>
                <input class="gk-input" type="text" id="headline" name="headline" maxlength="120"
                       value="<?= e($profileData['headline']) ?>"
                       placeholder="Ex.: Estudante de ADS com foco em front-end">
            </div>
        </div>

        <div class="gk-card gk-mb-3 gk-anim-slide gk-delay-1">
            <span class="gk-eyebrow">Etapa 2</span>
            <h2 class="gk-card__title gk-mt-1 gk-mb-2">Habilidades</h2>
            <p class="gk-card__subtitle gk-mb-3">Até <?= ProfileService::MAX_SKILLS ?> habilidades, com nível de domínio.</p>

            <div data-tm-rows>
                <?php
                $skillRows = $profileData['skills'] !== [] ? $profileData['skills'] : [['name' => '', 'level' => 'BASIC']];
                foreach ($skillRows as $index => $skill):
                    ?>
                    <div class="gk-row" data-tm-row>
                        <input class="gk-input" type="text" name="skills[<?= (int) $index ?>][name]"
                               value="<?= e((string) $skill['name']) ?>" maxlength="60" placeholder="Ex.: PHP">
                        <select class="gk-input" name="skills[<?= (int) $index ?>][level]">
                            <?php foreach ($levels as $code => $label): ?>
                                <option value="<?= e((string) $code) ?>" <?= (string) $skill['level'] === (string) $code ? 'selected' : '' ?>>
                                    <?= e((string) $label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="gk-btn gk-btn--ghost gk-btn--sm" data-tm-remove-row aria-label="Remover habilidade">Remover</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="gk-btn gk-btn--white gk-btn--sm gk-mt-2" data-tm-add-row="tpl-skill">+ Adicionar habilidade</button>
        </div>

        <div class="gk-card gk-mb-3 gk-anim-slide gk-delay-2">
            <span class="gk-eyebrow">Etapa 3</span>
            <h2 class="gk-card__title gk-mt-1 gk-mb-2">Experiências</h2>
            <p class="gk-card__subtitle gk-mb-3">Projetos, trabalhos, monitorias ou atividades voluntárias. Até <?= ProfileService::MAX_EXPERIENCES ?>.</p>

            <div data-tm-rows>
                <?php
                $experienceRows = $profileData['experiences'] !== [] ? $profileData['experiences'] : [['title' => '', 'description' => '', 'competencies' => '']];
                foreach ($experienceRows as $index => $experience):
                    ?>
                    <div class="gk-row gk-row--stack" data-tm-row>
                        <input class="gk-input" type="text" name="experiences[<?= (int) $index ?>][title]"
                               value="<?= e((string) $experience['title']) ?>" maxlength="120"
                               placeholder="Cargo ou atividade (ex.: Monitoria de programação)">
                        <textarea class="gk-input" name="experiences[<?= (int) $index ?>][description]" rows="2"
                                  maxlength="1000" placeholder="O que você fez"><?= e((string) $experience['description']) ?></textarea>
                        <input class="gk-input" type="text" name="experiences[<?= (int) $index ?>][competencies]"
                               value="<?= e((string) $experience['competencies']) ?>" maxlength="255"
                               placeholder="Competências (ex.: JavaScript, trabalho em equipe)">
                        <button type="button" class="gk-btn gk-btn--ghost gk-btn--sm" data-tm-remove-row aria-label="Remover experiência">Remover</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="gk-btn gk-btn--white gk-btn--sm gk-mt-2" data-tm-add-row="tpl-experience">+ Adicionar experiência</button>
        </div>

        <div class="gk-card gk-mb-3 gk-anim-slide gk-delay-3">
            <span class="gk-eyebrow">Etapa 4</span>
            <h2 class="gk-card__title gk-mt-1 gk-mb-2">Formações</h2>
            <p class="gk-card__subtitle gk-mb-3">Cursos, técnico ou graduação. Até <?= ProfileService::MAX_EDUCATIONS ?>.</p>

            <div data-tm-rows>
                <?php
                $educationRows = $profileData['educations'] !== [] ? $profileData['educations'] : [['course' => '', 'institution' => '', 'description' => '']];
                foreach ($educationRows as $index => $education):
                    ?>
                    <div class="gk-row gk-row--stack" data-tm-row>
                        <input class="gk-input" type="text" name="educations[<?= (int) $index ?>][course]"
                               value="<?= e((string) $education['course']) ?>" maxlength="120"
                               placeholder="Curso (ex.: Técnico em Informática)">
                        <input class="gk-input" type="text" name="educations[<?= (int) $index ?>][institution]"
                               value="<?= e((string) $education['institution']) ?>" maxlength="120"
                               placeholder="Instituição (ex.: Escola Estadual Fictícia)">
                        <input class="gk-input" type="text" name="educations[<?= (int) $index ?>][description]"
                               value="<?= e((string) $education['description']) ?>" maxlength="255"
                               placeholder="Observação (opcional)">
                        <button type="button" class="gk-btn gk-btn--ghost gk-btn--sm" data-tm-remove-row aria-label="Remover formação">Remover</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="gk-btn gk-btn--white gk-btn--sm gk-mt-2" data-tm-add-row="tpl-education">+ Adicionar formação</button>
        </div>

        <div class="gk-card">
            <div class="gk-flex gk-gap-sm gk-wrap gk-flex-between">
                <button type="submit" name="action" value="save" class="gk-btn gk-btn--white">Salvar rascunho</button>
                <button type="submit" name="action" value="complete" class="gk-btn gk-btn--primary gk-btn--lg"
                        data-tm-confirm-submit="Finalizar o perfil? Depois de finalizado, a edição só é possível enquanto o organizador mantiver a etapa de perfis.">
                    Finalizar perfil
                </button>
            </div>
        </div>
    </form>

    <template id="tpl-skill">
        <div class="gk-row" data-tm-row>
            <input class="gk-input" type="text" name="skills[__INDEX__][name]" maxlength="60" placeholder="Ex.: PHP">
            <select class="gk-input" name="skills[__INDEX__][level]">
                <?php foreach ($levels as $code => $label): ?>
                    <option value="<?= e((string) $code) ?>"><?= e((string) $label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="gk-btn gk-btn--ghost gk-btn--sm" data-tm-remove-row aria-label="Remover habilidade">Remover</button>
        </div>
    </template>

    <template id="tpl-experience">
        <div class="gk-row gk-row--stack" data-tm-row>
            <input class="gk-input" type="text" name="experiences[__INDEX__][title]" maxlength="120" placeholder="Cargo ou atividade">
            <textarea class="gk-input" name="experiences[__INDEX__][description]" rows="2" maxlength="1000" placeholder="O que você fez"></textarea>
            <input class="gk-input" type="text" name="experiences[__INDEX__][competencies]" maxlength="255" placeholder="Competências">
            <button type="button" class="gk-btn gk-btn--ghost gk-btn--sm" data-tm-remove-row aria-label="Remover experiência">Remover</button>
        </div>
    </template>

    <template id="tpl-education">
        <div class="gk-row gk-row--stack" data-tm-row>
            <input class="gk-input" type="text" name="educations[__INDEX__][course]" maxlength="120" placeholder="Curso">
            <input class="gk-input" type="text" name="educations[__INDEX__][institution]" maxlength="120" placeholder="Instituição">
            <input class="gk-input" type="text" name="educations[__INDEX__][description]" maxlength="255" placeholder="Observação (opcional)">
            <button type="button" class="gk-btn gk-btn--ghost gk-btn--sm" data-tm-remove-row aria-label="Remover formação">Remover</button>
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
                var container = button.closest('.gk-card').querySelector('[data-tm-rows]');
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
