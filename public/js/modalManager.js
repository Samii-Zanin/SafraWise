/**
 * SafraWise — Modal Manager
 * ─────────────────────────────────────────────────────────────────
 * Wrapper reutilizável sobre o Bootstrap 5 Modal.
 * Mantém uma instância por modal-id para não recriar objetos.
 *
 * ── USO DECLARATIVO (via HTML, sem escrever JS) ──────────────────
 *
 *   Abrir:   <button data-sw-open="modal-cadastro-peao">Abrir</button>
 *   Fechar:  <button data-sw-close="modal-cadastro-peao">Cancelar</button>
 *   Toggle:  <button data-sw-toggle="modal-cadastro-peao">Toggle</button>
 *
 *   Resetar form ao fechar (opcional — adicione no elemento .modal):
 *   <div id="modal-xyz" class="modal sw-modal" data-sw-reset-on-close>
 *
 * ── USO IMPERATIVO (via JS) ───────────────────────────────────────
 *
 *   ModalManager.open('modal-cadastro-peao')
 *   ModalManager.close('modal-cadastro-peao')
 *   ModalManager.toggle('modal-cadastro-peao')
 *
 *   // Com callbacks executados após o modal abrir/fechar completamente
 *   ModalManager.open('modal-cadastro-peao', {
 *     onOpen:  (modalEl) => console.log('abriu',  modalEl),
 *     onClose: (modalEl) => console.log('fechou', modalEl),
 *   })
 */

const ModalManager = (() => {
  /** @type {Map<string, bootstrap.Modal>} */
  const _instances = new Map();

  /**
   * @param {string|HTMLElement} target
   * @returns {bootstrap.Modal|null}
   */
  function _getInstance(target) {
    const el =
      typeof target === "string" ? document.getElementById(target) : target;

    if (!el) {
      console.warn(`[ModalManager] Elemento não encontrado: "${target}"`);
      return null;
    }

    if (!_instances.has(el.id)) {
      _instances.set(el.id, new bootstrap.Modal(el));
    }

    return _instances.get(el.id);
  }

  function _attachCallbacks(el, { onOpen, onClose } = {}) {
    if (el._swListenersAttached) return;
    el._swListenersAttached = true;

    el.addEventListener("shown.bs.modal", () => onOpen?.(el));
    el.addEventListener("hidden.bs.modal", () => {
      onClose?.(el);

      // Reseta o(s) form(s) dentro do modal se o atributo estiver presente
      if (el.hasAttribute("data-sw-reset-on-close")) {
        el.querySelectorAll("form").forEach((f) => f.reset());
      }
    });
  }

  /**
   * @param {string|HTMLElement} target
   * @param {{ onOpen?: Function, onClose?: Function }} [callbacks]
   */
  function open(target, callbacks = {}) {
    const el =
      typeof target === "string" ? document.getElementById(target) : target;

    if (!el) return;
    _attachCallbacks(el, callbacks);
    _getInstance(el)?.show();
  }

  /**.
   * @param {string|HTMLElement} target
   */
  function close(target) {
    _getInstance(target)?.hide();
  }

  /**
   * @param {string|HTMLElement} target
   */
  function toggle(target) {
    _getInstance(target)?.toggle();
  }


  

  function init() {
    document.querySelectorAll("[data-sw-open]").forEach((btn) => {
      if (btn._swBound) return;
      btn._swBound = true;
      btn.addEventListener("click", () => open(btn.dataset.swOpen));
    });

    document.querySelectorAll("[data-sw-close]").forEach((btn) => {
      if (btn._swBound) return;
      btn._swBound = true;
      btn.addEventListener("click", () => close(btn.dataset.swClose));
    });

    document.querySelectorAll("[data-sw-toggle]").forEach((btn) => {
      if (btn._swBound) return;
      btn._swBound = true;
      btn.addEventListener("click", () => toggle(btn.dataset.swToggle));
    });

    document
      .querySelectorAll(".modal[data-sw-reset-on-close]")
      .forEach((el) => {
        _attachCallbacks(el);
      });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  return { open, close, toggle, init };
})();
