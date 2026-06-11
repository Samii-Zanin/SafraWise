<?php
require_once '../app/helpers/ui.php';
// (Mesma lógica de iniciais e saudação das outras páginas)
$pagina_atual = 'talhoes';
$toast = flashToast();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>SafraWise — Talhões</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
  <style>
    .card-table-wrapper { background-color: #f8f8f5; border-radius: 20px; border: 1px solid #e5e5de; overflow: hidden; }
    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-vazio { background: #e2e8f0; color: #475569; }
  </style>
</head>
<body>

<?php include 'layouts/toast.php'; ?>

<div class="app-layout">
  <?php include 'layouts/sidebar.php'; ?>
  <?php include 'layouts/ticker.php'; ?>

  <div class="main-content">
    <?php include 'layouts/topbar.php'; ?>

    <div class="page-body">
      <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
          <h2 class="font-titulo fs-3 mb-1">Gestão de Talhões</h2>
          <p class="text-muted small mb-0">Divisões produtivas das suas propriedades.</p>
        </div>
        <button class="btn btn-success" data-sw-open="modal-cadastro-talhao">Novo Talhão</button>
      </div>

      <div class="card-table-wrapper">
        <div class="table-responsive">
          <table class="table table-safrawise mb-0">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Propriedade</th>
                <th>Área (ha)</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($talhoes as $t): ?>
                <tr>
                  <td class="fw-medium text-dark"><?= htmlspecialchars($t['nome']) ?></td>
                  <td class="text-muted"><?= htmlspecialchars($t['propriedade_nome']) ?></td>
                  <td class="text-muted"><?= number_format($t['area_hectare'], 2, ',', '.') ?> ha</td>
                  <td><span class="status-badge status-vazio"><?= $t['status'] ?></span></td>
                  <td class="text-end">
                    <button class="btn-table-action" onclick="abrirEditarTalhao(<?= $t['id'] ?>, '<?= addslashes($t['nome']) ?>', <?= $t['area_hectare'] ?>, <?= $t['propriedade_id'] ?>)">
                      <!-- Ícone Lápis -->
                    </button>
                    <a href="?page=delete_talhao&id=<?= $t['id'] ?>" class="btn-table-action text-danger" onclick="return confirm('Excluir talhão?')">
                      <!-- Ícone Lixeira -->
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL CADASTRO -->
<div class="modal fade sw-modal" id="modal-cadastro-talhao" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Novo Talhão</h5>
        <button type="button" class="btn-close" data-sw-close="modal-cadastro-talhao"></button>
      </div>
      <div class="modal-body">
        <form id="form-cad-talhao" method="POST" action="index.php?page=store_talhao">
          <div class="mb-3">
            <label class="form-label">Propriedade Destino</label>
            <select name="propriedade_id" class="form-select input-safrawise" required>
              <?php foreach ($propriedades as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?> (Dispo: <?= $p['area_produtiva'] ?> ha)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Nome do Talhão</label>
            <input type="text" name="nome" class="form-control input-safrawise" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Área (hectares)</label>
            <input type="number" step="0.01" name="area_hectare" class="form-control input-safrawise" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="form-cad-talhao" class="btn btn-success w-100">Criar Talhão</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EDIÇÃO (Similar ao cadastro, apenas mudando o ID e Action) -->
<div class="modal fade sw-modal" id="modal-editar-talhao" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <!-- Estrutura igual ao de cadastro, trocando IDs para "edit-..." -->
    </div>
  </div>
</div>

<script src="../../public/js/modalManager.js"></script>
<script src="../../public/js/toast.js"></script>
<script>
function abrirEditarTalhao(id, nome, area, propId) {
    ModalManager.open('modal-editar-talhao');
}
</script>
</body>
</html>