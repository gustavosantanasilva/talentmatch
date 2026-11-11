/* ==========================================================================
   TalentMatch — utilitários de frontend
   - Pilha de toasts
   - Requisições fetch com token CSRF e tratamento JSON padronizado
   - Confirmação com modal
   ========================================================================== */
(function () {
    'use strict';

    const TM = window.TM = window.TM || {};

    /* ----------------------------------------------------------------------
       Toasts
       ---------------------------------------------------------------------- */
    function toastStack() {
        let stack = document.querySelector('.tm-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'tm-toast-stack';
            stack.setAttribute('role', 'status');
            stack.setAttribute('aria-live', 'polite');
            document.body.appendChild(stack);
        }
        return stack;
    }

    /**
     * @param {string} message
     * @param {'info'|'success'|'warning'|'error'} type
     * @param {number} timeout
     */
    TM.toast = function (message, type = 'info', timeout = 4200) {
        const el = document.createElement('div');
        el.className = 'tm-toast' + (type !== 'info' ? ' tm-toast--' + type : '');
        el.textContent = message;
        toastStack().appendChild(el);

        window.setTimeout(function () {
            el.style.transition = 'opacity 200ms ease, transform 200ms ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(8px)';
            window.setTimeout(function () { el.remove(); }, 220);
        }, timeout);
    };

    /* ----------------------------------------------------------------------
       CSRF
       ---------------------------------------------------------------------- */
    TM.csrfToken = function () {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content') || '';
        const input = document.querySelector('input[name="csrf_token"]');
        return input ? input.value : '';
    };

    /* ----------------------------------------------------------------------
       Requisições JSON
       ---------------------------------------------------------------------- */
    /**
     * Envia uma requisição e devolve o corpo JSON padronizado.
     * @param {string} url
     * @param {object} [options]
     * @returns {Promise<{success:boolean,message:string,data:any}>}
     */
    TM.request = async function (url, options = {}) {
        const opts = Object.assign({ method: 'GET' }, options);
        opts.headers = Object.assign({
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': TM.csrfToken()
        }, opts.headers || {});

        if (opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(opts.body);
        }

        let response;
        try {
            response = await fetch(url, opts);
        } catch (err) {
            return { success: false, message: 'Falha de conexão. Verifique sua internet.', data: null };
        }

        let payload;
        try {
            payload = await response.json();
        } catch (err) {
            return { success: false, message: 'Resposta inválida do servidor.', data: null };
        }

        return payload;
    };

    TM.get = function (url) { return TM.request(url, { method: 'GET' }); };

    TM.post = function (url, body) {
        return TM.request(url, { method: 'POST', body: body || {} });
    };

    /* ----------------------------------------------------------------------
       Confirmação (Promise)
       ---------------------------------------------------------------------- */
    TM.confirm = function (message, confirmLabel = 'Confirmar') {
        return new Promise(function (resolve) {
            const backdrop = document.createElement('div');
            backdrop.className = 'tm-modal-backdrop';
            backdrop.innerHTML =
                '<div class="tm-modal" role="dialog" aria-modal="true">' +
                '  <h3 class="tm-card__title">Confirmação</h3>' +
                '  <p class="tm-text-secondary">' + message + '</p>' +
                '  <div class="tm-flex tm-gap-sm" style="justify-content:flex-end">' +
                '    <button type="button" class="tm-btn tm-btn--ghost" data-tm-cancel>Cancelar</button>' +
                '    <button type="button" class="tm-btn tm-btn--primary" data-tm-ok>' + confirmLabel + '</button>' +
                '  </div>' +
                '</div>';

            function close(result) {
                backdrop.remove();
                document.removeEventListener('keydown', onKey);
                resolve(result);
            }
            function onKey(e) { if (e.key === 'Escape') close(false); }

            backdrop.querySelector('[data-tm-cancel]').addEventListener('click', function () { close(false); });
            backdrop.querySelector('[data-tm-ok]').addEventListener('click', function () { close(true); });
            backdrop.addEventListener('click', function (e) { if (e.target === backdrop) close(false); });
            document.addEventListener('keydown', onKey);
            document.body.appendChild(backdrop);
            backdrop.querySelector('[data-tm-ok]').focus();
        });
    };

    /* ----------------------------------------------------------------------
       Helpers de UI
       ---------------------------------------------------------------------- */
    TM.escapeHtml = function (value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    };

    TM.formatScore = function (value) {
        return (Math.round(Number(value) * 100) / 100).toFixed(2).replace('.', ',');
    };

    /* ----------------------------------------------------------------------
       Shell administrativo (sidebar mobile) e cópia para a área de transferência
       ---------------------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tm-shell-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const shell = btn.closest('.tm-shell');
                if (shell) shell.classList.toggle('is-open');
            });
        });

        document.querySelectorAll('.tm-shell').forEach(function (shell) {
            shell.addEventListener('click', function (event) {
                if (event.target === shell && shell.classList.contains('is-open')) {
                    shell.classList.remove('is-open');
                }
            });
        });

        document.querySelectorAll('[data-tm-copy]').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const value = btn.getAttribute('data-tm-copy') || '';
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(value);
                    } else {
                        const input = document.createElement('textarea');
                        input.value = value;
                        input.style.position = 'fixed';
                        input.style.opacity = '0';
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        input.remove();
                    }
                    TM.toast('Copiado para a área de transferência.', 'success');
                } catch (err) {
                    TM.toast('Não foi possível copiar automaticamente.', 'warning');
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tm-toast]').forEach(function (el) {
            TM.toast(el.getAttribute('data-tm-toast'), el.getAttribute('data-tm-toast-type') || 'info');
        });
    });
})();
