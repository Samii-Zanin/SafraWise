<?php
require_once '../app/helpers/ui.php';
$user = $_SESSION['user'];
$tipo = $_SESSION['tipo'];

$pagina_atual = 'propriedades';

$toast = flashToast();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Propriedades</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
      /* Estilo da Tabela Bege Premium */
      .card-table-wrapper {
          background-color: #f8f8f5;
          border-radius: 20px;
          border: 1px solid #e5e5de;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
          overflow: hidden;
      }
      .table-safrawise tbody tr:last-child td {
          border-bottom: 0 !important;
      }
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
          <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
            Minhas Propriedades
          </h2>
          <p class="text-muted mb-0 small">
            Gerencie as fazendas e áreas produtivas registradas em seu nome.
          </p>
        </div>

        <button type="button" class="btn btn-success d-flex align-items-center gap-2" data-sw-open="modal-cadastro-propriedade">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Nova Propriedade
        </button>
      </div>

      <div class="card-table-wrapper">
          <div class="table-responsive">
              <table class="table table-safrawise mb-0 table-clickable">
                  <thead>
                      <tr>
                          <th class="border-0">Nome da Fazenda</th>
                          <th class="border-0">Localização</th>
                          <th class="border-0">Município / UF</th>
                          <th class="border-0">Área Total (ha)</th>
                          <th class="border-0">Área Produtiva (ha)</th>
                          <th class="border-0 text-end">Ações</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (empty($propriedades)): ?>
                          <tr>
                              <td colspan="6" class="text-center py-5 text-muted">
                                  Nenhuma propriedade cadastrada ainda.<br>
                                  Clique em <b>"Nova Propriedade"</b> para começar.
                              </td>
                          </tr>
                      <?php else: ?>
                          <?php foreach ($propriedades as $prop): ?>
                            <tr onclick="window.location.href='index.php?page=detalhes_propriedade&id=<?= $prop['id'] ?>'">
                                <td class="fw-medium text-dark"><?= htmlspecialchars($prop['nome']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($prop['localizacao']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($prop['municipio']) ?> - <?= htmlspecialchars($prop['estado']) ?></td>
                                <td class="text-muted"><?= number_format($prop['area_total'], 2, ',', '.') ?> ha</td>
                                <td class="text-muted"><?= number_format($prop['area_produtiva'], 2, ',', '.') ?> ha</td>

                                <td class="text-end" onclick="event.stopPropagation();">
                                    <!-- O event.stopPropagation() acima impede que clicar nos botões abra os detalhes -->

                                    <button type="button" class="btn-table-action" title="Editar"
                                            onclick="abrirModalEditarPropriedade(
                                                '<?= $prop['id'] ?>',
                                                '<?= htmlspecialchars($prop['nome'], ENT_QUOTES) ?>',
                                                '<?= htmlspecialchars($prop['localizacao'], ENT_QUOTES) ?>',
                                                '<?= htmlspecialchars($prop['municipio'], ENT_QUOTES) ?>',
                                                '<?= htmlspecialchars($prop['estado'], ENT_QUOTES) ?>',
                                                '<?= $prop['area_total'] ?>',
                                                '<?= $prop['area_produtiva'] ?>'
                                            )">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                   <button type="button" 
                                          class="btn-table-action text-danger" 
                                          title="Excluir Propriedade" 
                                          onclick="solicitarExclusaoPropriedade('<?= $prop['id'] ?>', '<?= htmlspecialchars($prop['nome'], ENT_QUOTES) ?>')">
                                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                          <path d="M3 6h18"></path>
                                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                      </svg>
                                  </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>
      </div>

    </div>
  </div>
</div>

<div class="modal fade sw-modal" id="modal-cadastro-propriedade" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Adicionar Propriedade</h5>
          <p class="modal-subtitle mb-0">Insira os dados cadastrais da nova fazenda.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-cadastro-propriedade" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form id="form-cadastro-propriedade" method="POST" action="index.php?page=store_propriedade">
          <div class="mb-3">
            <label class="form-label" for="prop-nome">Nome da Fazenda</label>
            <input type="text" class="form-control" id="prop-nome" name="nome" placeholder="Ex: Fazenda Bela Vista" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="prop-localizacao">Localização (Linha/Rodovia/KM)</label>
            <input type="text" class="form-control" id="prop-localizacao" name="localizacao" placeholder="Ex: Rodovia BR-153, KM 45" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label" for="prop-municipio">Município</label>
              <input type="text" class="form-control" id="prop-municipio" name="municipio" placeholder="Ex: Passo Fundo" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="prop-estado">UF</label>
              <input type="text" class="form-control" id="prop-estado" name="estado" maxlength="2" placeholder="Ex: RS" required>
            </div>
          </div>
          <div class="row g-3 mb-0">
            <div class="col-md-6">
              <label class="form-label" for="prop-total">Área Total (ha)</label>
              <input type="number" step="0.01" class="form-control" id="prop-total" name="area_total" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="prop-produtiva">Área Produtiva (ha)</label>
              <input type="number" step="0.01" class="form-control" id="prop-produtiva" name="area_produtiva" placeholder="0.00" required>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-cadastro-propriedade">Cancelar</button>
        <button type="submit" form="form-cadastro-propriedade" class="btn btn-success">Adicionar propriedade</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade sw-modal" id="modal-editar-propriedade" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Editar Propriedade</h5>
          <p class="modal-subtitle mb-0">Atualize as informações da fazenda.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-editar-propriedade" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form id="form-editar-propriedade" method="POST" action="index.php?page=update_propriedade">
          <input type="hidden" name="id" id="edit-prop-id">

          <div class="mb-3">
            <label class="form-label" for="edit-prop-nome">Nome da Fazenda</label>
            <input type="text" class="form-control" id="edit-prop-nome" name="nome" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="edit-prop-localizacao">Localização</label>
            <input type="text" class="form-control" id="edit-prop-localizacao" name="localizacao" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label" for="edit-prop-municipio">Município</label>
              <input type="text" class="form-control" id="edit-prop-municipio" name="municipio" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="edit-prop-estado">UF</label>
              <input type="text" class="form-control" id="edit-prop-estado" name="estado" maxlength="2" required>
            </div>
          </div>
          <div class="row g-3 mb-0">
            <div class="col-md-6">
              <label class="form-label" for="edit-prop-total">Área Total (ha)</label>
              <input type="number" step="0.01" class="form-control" id="edit-prop-total" name="area_total" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="edit-prop-produtiva">Área Produtiva (ha)</label>
              <input type="number" step="0.01" class="form-control" id="edit-prop-produtiva" name="area_produtiva" required>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-editar-propriedade">Cancelar</button>
        <button type="submit" form="form-editar-propriedade" class="btn btn-success">Salvar Alterações</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade sw-modal" id="modal-confirmar-exclusao-propriedade" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">

      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title text-danger d-flex align-items-center gap-2 text-nowrap" style="font-family:'DM Sans',sans-serif; font-weight:450;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          Excluir Propriedade?
        </h5>
        <button type="button" class="btn-close" data-sw-close="modal-confirmar-exclusao-propriedade" aria-label="Fechar"></button>
      </div>

      <div class="modal-body py-3">
        <p class="mb-0 text-black small">
          Tem certeza que deseja excluir a propriedade <strong id="delete-prop-nome" class="text-dark"></strong>? Esta ação é irreversível e removerá todos os talhões e históricos vinculados a ela.
        </p>
      </div>

      <div class="modal-footer border-0 pt-0 d-flex gap-2">
        <button type="button" class="btn-modal-cancel flex-grow-1 py-2 text-center" data-sw-close="modal-confirmar-exclusao-propriedade">
          Cancelar
        </button>
        <a id="btn-confirmar-deletar-prop" href="#" class="btn btn-danger flex-grow-1 py-2 d-flex align-items-center justify-content-center gap-1 small" style="font-weight: 500;">
          Sim, Excluir
        </a>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="../../public/js/toast.js"></script>

<script>
function abrirModalEditarPropriedade(id, nome, localizacao, municipio, estado, areaTotal, areaProdutiva) {
    document.getElementById('edit-prop-id').value = id;
    document.getElementById('edit-prop-nome').value = nome;
    document.getElementById('edit-prop-localizacao').value = localizacao;
    document.getElementById('edit-prop-municipio').value = municipio;
    document.getElementById('edit-prop-estado').value = estado;
    document.getElementById('edit-prop-total').value = areaTotal;
    document.getElementById('edit-prop-produtiva').value = areaProdutiva;

    if (typeof ModalManager !== 'undefined') {
        ModalManager.open('modal-editar-propriedade');
    }
}

function solicitarExclusaoPropriedade(id, nome) {
    // 1. Atualiza o nome da fazenda no corpo do aviso
    document.getElementById('delete-prop-nome').textContent = nome;
    
    // 2. Aponta o link do botão de confirmação para a rota de exclusão do proprietário controller
    document.getElementById('btn-confirmar-deletar-prop').href = `index.php?page=delete_propriedade&id=${id}`;
    
    // 3. Abre o modal de forma imperativa
    ModalManager.open('modal-confirmar-exclusao-propriedade');
}
</script>
</body>
</html>