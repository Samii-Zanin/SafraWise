
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
    .posse-label { font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px; }
    .posse-proprio { background: #e2eece; color: #4b6321; }
    .posse-arrendado { background: #fef3c7; color: #92400e; }
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
                <th>Nome / Posse</th>
                <th>Propriedade</th>
                <th>Área (ha)</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($talhoes as $t): ?>
                <tr>
                  <td class="fw-medium text-dark">
                    <?= htmlspecialchars($t['nome']) ?><br>
                    <?php if ($t['tipo_posse'] === 'ARRENDADO'): ?>
                        <span class="posse-label posse-arrendado">Arrendado: <?= number_format($t['custo_arrendamento_sacas'], 1, ',', '.') ?> sc/ha</span>
                    <?php else: ?>
                        <span class="posse-label posse-proprio">Próprio</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-muted"><?= htmlspecialchars($t['propriedade_nome']) ?></td>
                  <td class="text-muted"><?= number_format($t['area_hectare'], 2, ',', '.') ?> ha</td>
                  <td><span class="status-badge status-vazio"><?= htmlspecialchars($t['status']) ?></span></td>
                  <td class="text-end">
                    <button class="btn-table-action" onclick="abrirEditarTalhao('<?= $t['id'] ?>', '<?= htmlspecialchars($t['nome'], ENT_QUOTES) ?>', '<?= $t['area_hectare'] ?>', '<?= $t['tipo_posse'] ?>', '<?= $t['custo_arrendamento_sacas'] ?>')">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                    </button>
                    <button type="button" class="btn-table-action text-danger" title="Excluir" onclick="solicitarExclusaoTalhao('<?= $t['id'] ?>', '<?= htmlspecialchars($t['nome'], ENT_QUOTES) ?>')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
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
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Área (hectares)</label>
              <input type="number" step="0.01" min="0.01" name="area_hectare" class="form-control input-safrawise" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo de Posse</label>
              <select name="tipo_posse" class="form-select input-safrawise" onchange="toggleCusto(this)" required>
                <option value="PROPRIO">Próprio</option>
                <option value="ARRENDADO">Arrendado</option>
              </select>
            </div>
          </div>
          <div class="mb-0 d-none" id="div-custo-cadastro">
            <label class="form-label">Custo do Arrendamento (sacas soja/ha)</label>
            <div class="input-group">
               <input type="number" step="0.1" min="0" name="custo_arrendamento_sacas" class="form-control input-safrawise" value="0.0">
               <span class="input-group-text bg-white text-muted">sc/ha</span>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="form-cad-talhao" class="btn btn-success w-100">Criar Talhão</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade sw-modal" id="modal-editar-talhao" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Talhão</h5>
        <button type="button" class="btn-close" data-sw-close="modal-editar-talhao"></button>
      </div>
      <div class="modal-body">
        <form id="form-editar-talhao" method="POST" action="index.php?page=update_talhao">
          <input type="hidden" name="id" id="edit-talhao-id">
          <div class="mb-3">
            <label class="form-label">Propriedade Destino</label>
            <select name="propriedade_id" id="edit-talhao-prop-id" class="form-select input-safrawise" required>
              <?php foreach ($propriedades as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Nome do Talhão</label>
            <input type="text" name="nome" id="edit-talhao-nome" class="form-control input-safrawise" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Área (hectares)</label>
              <input type="number" step="0.01" min="0.01" name="area_hectare" id="edit-talhao-area" class="form-control input-safrawise" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo de Posse</label>
              <select name="tipo_posse" id="edit-talhao-posse" class="form-select input-safrawise" onchange="toggleCusto(this)" required>
                <option value="PROPRIO">Próprio</option>
                <option value="ARRENDADO">Arrendado</option>
              </select>
            </div>
          </div>
          <div class="mb-0 d-none" id="div-custo-edicao">
            <label class="form-label">Custo do Arrendamento (sacas soja/ha)</label>
            <div class="input-group">
               <input type="number" step="0.1" min="0" name="custo_arrendamento_sacas" id="edit-talhao-custo" class="form-control input-safrawise">
               <span class="input-group-text bg-white text-muted">sc/ha</span>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="form-editar-talhao" class="btn btn-success w-100">Salvar Alterações</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade sw-modal" id="modal-confirmar-exclusao-talhao" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title text-danger d-flex align-items-center gap-2 text-nowrap" style="font-family:'DM Sans',sans-serif; font-weight:450;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          Excluir Talhão?
        </h5>
        <button type="button" class="btn-close" data-sw-close="modal-confirmar-exclusao-talhao" aria-label="Fechar"></button>
      </div>
      <div class="modal-body py-3">
        <p class="mb-0 text-black small">Tem certeza que deseja excluir o talhão <strong id="delete-talhao-nome" class="text-dark"></strong>?</p>
      </div>
      <div class="modal-footer border-0 pt-0 d-flex gap-2">
        <button type="button" class="btn-modal-cancel flex-grow-1 py-2 text-center" data-sw-close="modal-confirmar-exclusao-talhao">Cancelar</button>
        <a id="btn-confirmar-deletar-talhao" href="#" class="btn btn-danger flex-grow-1 py-2 d-flex align-items-center justify-content-center small" style="font-weight: 500;">Sim, Excluir</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="../../public/js/toast.js"></script>
<script>
function toggleCusto(selectElement) {
    const form = selectElement.closest('form');
    const divCusto = form.querySelector('[id^="div-custo-"]');
    if (selectElement.value === 'ARRENDADO') {
        divCusto.classList.remove('d-none');
    } else {
        divCusto.classList.add('d-none');
    }
}

function closeToast(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('hide');
  setTimeout(() => el.remove(), 320);
}

function abrirEditarTalhao(id, nome, area, posse, custo) {
    document.getElementById('edit-talhao-id').value = id;
    document.getElementById('edit-talhao-nome').value = nome;
    document.getElementById('edit-talhao-area').value = area;
    
    const selectPosse = document.getElementById('edit-talhao-posse');
    selectPosse.value = posse;
    
    const inputCusto = document.getElementById('edit-talhao-custo');
    inputCusto.value = custo;

    toggleCusto(selectPosse);
    ModalManager.open('modal-editar-talhao');
}

function solicitarExclusaoTalhao(id, nome) {
    document.getElementById('delete-talhao-nome').textContent = nome;
    document.getElementById('btn-confirmar-deletar-talhao').href = `index.php?page=delete_talhao&id=${id}`;
    ModalManager.open('modal-confirmar-exclusao-talhao');
}

document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value < 0) this.value = 0;
    });
});
</script>
</body>
</html>