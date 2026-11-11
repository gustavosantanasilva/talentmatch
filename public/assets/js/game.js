/* ==========================================================================
   TalentMatch — interações do jogo (Game Kit)
   - Seletor de avatar e cor
   - Grade de participantes ao vivo (lobby estilo Kahoot)
   - Atualização de nome/avatar do participante
   ========================================================================== */
(function () {
    'use strict';

    const TM = window.TM = window.TM || {};

    const AVATAR_FALLBACK = '🙂';
    const COLOR_FALLBACK = '#087F5B';
    const TILE_PALETTE = ['#1368CE', '#E21B3C', '#FFA602', '#26890C', '#864CBF', '#0D9488'];

    function escapeHtml(value) {
        return TM.escapeHtml ? TM.escapeHtml(value) : String(value == null ? '' : value);
    }

    function normalizeColor(color) {
        const value = String(color || '').trim();
        return /^#[0-9A-Fa-f]{6}$/.test(value) ? value.toUpperCase() : COLOR_FALLBACK;
    }

    function tileColor(participant, index) {
        const custom = String(participant.avatar_color || '').trim();
        if (/^#[0-9A-Fa-f]{6}$/.test(custom)) {
            return custom.toUpperCase();
        }

        return TILE_PALETTE[index % TILE_PALETTE.length];
    }

    function statusLabel(status) {
        const labels = {
            WAITING: 'na sala',
            PROFILE_PENDING: 'no perfil',
            READY: 'pronto'
        };

        return labels[status] || 'na sala';
    }

    /* ----------------------------------------------------------------------
       Seletor de avatar
       ---------------------------------------------------------------------- */
    function initAvatarPicker(root) {
        const picker = root || document;
        picker.querySelectorAll('[data-gk-avatar-picker]').forEach(function (group) {
            const hidden = group.querySelector('input[type="hidden"]');
            const options = Array.prototype.slice.call(group.querySelectorAll('.gk-avatar-option'));

            if (!hidden || options.length === 0) {
                return;
            }

            const select = function (option) {
                const value = option.getAttribute('data-value') || AVATAR_FALLBACK;
                hidden.value = value;
                options.forEach(function (item) {
                    item.classList.toggle('is-selected', item === option);
                    item.setAttribute('aria-pressed', item === option ? 'true' : 'false');
                });
                const preview = document.querySelector('[data-gk-avatar-preview]');
                if (preview) {
                    preview.textContent = value;
                }
            };

            options.forEach(function (option) {
                option.setAttribute('aria-pressed', option.classList.contains('is-selected') ? 'true' : 'false');
                option.addEventListener('click', function () { select(option); });
            });

            if (!options.some(function (option) { return option.classList.contains('is-selected'); })) {
                const current = options.find(function (option) { return option.getAttribute('data-value') === hidden.value; });
                select(current || options[0]);
            }
        });
    }

    /* ----------------------------------------------------------------------
       Seletor de cor
       ---------------------------------------------------------------------- */
    function initColorPicker(root) {
        const picker = root || document;
        picker.querySelectorAll('[data-gk-color-picker]').forEach(function (group) {
            const hidden = group.querySelector('input[type="hidden"]');
            const options = Array.prototype.slice.call(group.querySelectorAll('.gk-color-option'));

            if (!hidden || options.length === 0) {
                return;
            }

            const select = function (option) {
                const value = option.getAttribute('data-value') || COLOR_FALLBACK;
                hidden.value = value;
                options.forEach(function (item) {
                    item.classList.toggle('is-selected', item === option);
                    item.setAttribute('aria-pressed', item === option ? 'true' : 'false');
                });
                const preview = document.querySelector('[data-gk-avatar-preview]');
                if (preview) {
                    preview.style.background = value;
                }
            };

            options.forEach(function (option) {
                option.setAttribute('aria-pressed', option.classList.contains('is-selected') ? 'true' : 'false');
                option.addEventListener('click', function () { select(option); });
            });

            if (!options.some(function (option) { return option.classList.contains('is-selected'); })) {
                const current = options.find(function (option) { return option.getAttribute('data-value') === hidden.value; });
                select(current || options[0]);
            }
        });
    }

    /* ----------------------------------------------------------------------
       Grade de participantes
       ---------------------------------------------------------------------- */
    /**
     * Renderiza a grade de participantes.
     * @param {HTMLElement} container
     * @param {Array} participants
     * @param {object} [options]
     * @param {number[]} [options.previousIds] ids já exibidos (para animar novos)
     */
    function renderPlayers(container, participants, options) {
        const opts = options || {};

        if (!container) {
            return [];
        }

        const previousIds = opts.previousIds || [];
        const list = Array.isArray(participants) ? participants : [];
        const nextIds = [];

        if (list.length === 0) {
            container.innerHTML =
                '<li class="gk-tile gk-tile--empty" style="grid-column:1/-1">' +
                '<span class="gk-empty__icon" aria-hidden="true">📱</span>' +
                '<span>Ninguém entrou ainda.<br>Compartilhe o QR Code!</span>' +
                '</li>';
            return nextIds;
        }

        container.innerHTML = list.map(function (participant, index) {
            const id = Number(participant.id);
            nextIds.push(id);
            const isNew = previousIds.length > 0 && previousIds.indexOf(id) === -1;
            const color = tileColor(participant, index);
            const name = escapeHtml(participant.nickname || 'Participante');
            const avatar = escapeHtml(participant.avatar || AVATAR_FALLBACK);
            const status = statusLabel(participant.status);

            return (
                '<li class="gk-tile' + (isNew ? ' gk-tile--new' : '') + '" style="--gk-tile-bg:' + color + '">' +
                '<span class="gk-tile__avatar" aria-hidden="true">' + avatar + '</span>' +
                '<span class="gk-tile__name" title="' + name + '">' + name + '</span>' +
                '<span class="gk-tile__status">' + status + '</span>' +
                '</li>'
            );
        }).join('');

        return nextIds;
    }

    /* ----------------------------------------------------------------------
       Polling da lista de participantes
       ---------------------------------------------------------------------- */
    /**
     * Inicia o acompanhamento ao vivo da sala.
     * @param {object} config
    * @param {string} config.code
     * @param {string} config.endpoint
     * @param {HTMLElement} config.container
     * @param {HTMLElement} [config.count]
     * @param {HTMLElement} [config.status]
     * @param {number} [config.interval]
     * @param {Function} [config.onStatusChange]
     * @returns {{stop: Function}}
     */
    function watchParticipants(config) {
        let previousIds = [];
        let currentStatus = null;
        let timer = null;

        const interval = config.interval || 3000;

        async function tick() {
            const payload = await TM.get(config.endpoint + '?code=' + encodeURIComponent(config.code));
            if (!payload || !payload.success || !payload.data) {
                return;
            }

            const data = payload.data;

            if (currentStatus === null) {
                currentStatus = data.status;
            }

            if (data.status !== currentStatus) {
                if (typeof config.onStatusChange === 'function') {
                    config.onStatusChange(data.status);
                }
                window.location.reload();
                return;
            }

            previousIds = renderPlayers(config.container, data.participants || [], {
                previousIds: previousIds,
            });

            if (config.count) {
                config.count.textContent = String(data.participants_count || (data.participants || []).length);
            }

            if (config.status && data.status_label) {
                config.status.textContent = data.status_label;
            }
        }

        tick();
        timer = window.setInterval(tick, interval);

        function stop() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        window.addEventListener('beforeunload', stop);

        return { stop: stop };
    }

    /* ----------------------------------------------------------------------
       Atualização de nome e avatar
       ---------------------------------------------------------------------- */
    function initIdentityForm() {
        document.querySelectorAll('[data-gk-identity-form]').forEach(function (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const nickname = form.querySelector('[name="nickname"]');
                const avatar = form.querySelector('[name="avatar"]');
                const color = form.querySelector('[name="avatar_color"]');
                const button = form.querySelector('button[type="submit"]');
                const original = button ? button.textContent : '';

                if (!nickname || !avatar || !color) {
                    return;
                }

                if (button) {
                    button.disabled = true;
                    button.textContent = 'Salvando...';
                }

                const payload = await TM.post(form.getAttribute('action'), {
                    nickname: nickname.value,
                    avatar: avatar.value,
                    avatar_color: color.value,
                });

                if (button) {
                    button.disabled = false;
                    button.textContent = original;
                }

                if (!payload.success) {
                    TM.toast(payload.message || 'Não foi possível salvar.', 'error');
                    return;
                }

                TM.toast(payload.message || 'Atualizado!', 'success');

                const data = payload.data || {};
                document.querySelectorAll('[data-gk-my-name]').forEach(function (el) {
                    el.textContent = data.nickname || '';
                });
                document.querySelectorAll('[data-gk-my-avatar]').forEach(function (el) {
                    el.textContent = data.avatar || AVATAR_FALLBACK;
                });
                document.querySelectorAll('[data-gk-my-color]').forEach(function (el) {
                    el.style.background = normalizeColor(data.avatar_color);
                });
            });
        });
    }

    /* ----------------------------------------------------------------------
       Inicialização
       ---------------------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        initAvatarPicker(document);
        initColorPicker(document);
        initIdentityForm();
    });

    TM.initAvatarPicker = initAvatarPicker;
    TM.initColorPicker = initColorPicker;
    TM.renderPlayers = renderPlayers;
    TM.watchParticipants = watchParticipants;
    TM.tileColor = tileColor;
    TM.normalizeColor = normalizeColor;
})();
