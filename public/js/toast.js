/**
 * public/js/toast.js
 * Comportamento único do toast SafraWise: fechar + auto-dismiss.
 * Estilos vêm de safrawise.css; markup vem de app/views/layouts/toast.php.
 *
 * Substitui as cópias de closeToast() + loop de auto-dismiss espalhadas
 * em cada página. (Pode-se remover a definição de closeToast de modalManager.js.)
 */
function closeToast(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add("hide");
  setTimeout(() => el.remove(), 320);
}

(function initToasts() {
  function arm() {
    document.querySelectorAll(".toast").forEach((t) => {
      const dur =
        parseFloat(getComputedStyle(t).getPropertyValue("--toast-duration")) *
          1000 || 5000;
      setTimeout(() => closeToast(t.id), dur);
    });
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", arm);
  } else {
    arm();
  }
})();
