<?php
/**
 * app/views/layouts/toast.php
 * Toast único e canônico do SafraWise — 4 tipos: success, error, warning, info.
 *
 * Espera a variável $toast (array: ['tipo','titulo','mensagem']) já definida.
 * Para popular a partir da sessão use o helper flashToast():
 *
 *   require_once '../app/helpers/ui.php';
 *   $toast = flashToast();
 *   ...
 *   <?php include 'layouts/toast.php'; ?>
 *
 * Estilos: public/css/safrawise.css  ·  Comportamento: public/js/toast.js
 */
$toast = $toast ?? null;
if (!$toast) return;

$tipo = $toast['tipo'] ?? 'info';

$icones = [
  'success' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
  'error'   => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
  'warning' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
  'info'    => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>',
];
$icone = $icones[$tipo] ?? $icones['info'];
?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= htmlspecialchars($tipo) ?>" id="toast-main" style="--toast-duration:5s">
    <div class="toast-icon">
      <svg viewBox="0 0 20 20" fill="currentColor"><?= $icone ?></svg>
    </div>
    <div class="toast-body">
      <div class="toast-title"><?= htmlspecialchars($toast['titulo'] ?? '') ?></div>
      <div class="toast-msg"><?= htmlspecialchars($toast['mensagem'] ?? '') ?></div>
    </div>
    <button class="toast-close" onclick="closeToast('toast-main')" type="button">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
  </div>
</div>