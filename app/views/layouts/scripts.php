<?php
/**
 * app/views/layouts/scripts.php
 * Scripts comuns de rodapé (antes de </body>).
 *
 * Variáveis aceitas (opcionais):
 *   $useLucide  bool   — inclui o Lucide e chama lucide.createIcons()
 *   $extraJs    array  — srcs de JS adicionais da página (ex.: paginacao_tabelas.js)
 *
 * Uso:
 *   <?php $useLucide = true; $extraJs = ['../../public/js/paginacao_tabelas.js']; ?>
 *   <?php include 'layouts/scripts.php'; ?>
 */
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="../../public/js/toast.js"></script>

<?php if (!empty($useLucide)): ?>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>lucide.createIcons();</script>
<?php endif; ?>

<?php foreach ($extraJs ?? [] as $js): ?>
<script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; ?>