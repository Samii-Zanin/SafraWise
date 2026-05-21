<?php
/*
 * app/views/produtos_culturas.php
 *
 * Variáveis esperadas (vindas do controller):
 *   $produtos  — array associativo com os produtos cadastrados
 *   $culturas  — array associativo com as culturas cadastradas
 */

$user  = $_SESSION['user'];
$tipo  = $_SESSION['tipo'];

$iniciais = strtoupper(substr($user['nome'], 0, 1));
if (str_contains($user['nome'], ' ')) {
    $partes   = explode(' ', $user['nome']);
    $iniciais = strtoupper($partes[0][0] . end($partes)[0]);
}

$saudacao = (function () {
    $h = (int) date('H');
    return match (true) {
        $h < 12 => 'Bom dia',
        $h < 18 => 'Boa tarde',
        default => 'Boa noite',
    };
})();

$pagina_atual = 'produtos_culturas';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

$totalProdutos = count($produtos ?? []);
$totalCulturas = count($culturas ?? []);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Produtos & Culturas</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
  <link rel="stylesheet" href="../../public/css/paginacao_tabelas.css">
</head>
<body>

<!-- ════ TOASTS ════ -->
<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= htmlspecialchars($toast['tipo']) ?>" id="toast-main" style="--toast-duration: 5s">
    <div class="toast-icon">
      <?php if ($toast['tipo'] === 'success'): ?>
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
      <?php elseif ($toast['tipo'] === 'error'): ?>
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
      <?php elseif ($toast['tipo'] === 'warning'): ?>
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
      <?php else: ?>
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
      <?php endif; ?>
    </div>
    <div class="toast-body">
      <div class="toast-title"><?= htmlspecialchars($toast['titulo']) ?></div>
      <div class="toast-msg"><?= htmlspecialchars($toast['mensagem']) ?></div>
    </div>
    <button class="toast-close" onclick="closeToast('toast-main')" type="button">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
  </div>
</div>
<?php endif; ?>

<div class="app-layout">

  <?php include 'layouts/sidebar.php'; ?>

  <div class="main-content">

    <?php include 'layouts/ticker.php'; ?>
    <?php include 'layouts/topbar.php'; ?>

    <div class="page-body">

      <!-- ── Cabeçalho ── -->
      <div class="mb-4">
        <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
          Produtos & Culturas
        </h2>
        <p class="text-muted mb-0 small">
          Gerencie os produtos e culturas disponíveis no sistema.
        </p>
      </div>

      <!-- ── Cards totalizadores ── -->
      <div class="row g-3 mb-4">

        <div class="col-md-3 col-sm-6">
          <div class="card metric-card verde h-100">
            <div class="card-body p-4">
              <div class="metric-icon verde mb-3">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="metric-label mb-1">Total de Produtos</div>
              <div class="metric-value mb-2"><?= $totalProdutos ?></div>
              <div class="metric-delta up">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                cadastrados
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 col-sm-6">
          <div class="card metric-card dourado h-100">
            <div class="card-body p-4">
              <div class="metric-icon dourado mb-3">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                  <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                </svg>
              </div>
              <div class="metric-label mb-1">Total de Culturas</div>
              <div class="metric-value mb-2"><?= $totalCulturas ?></div>
              <div class="metric-delta up">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                cadastradas
              </div>
            </div>
          </div>
        </div>

      </div><!-- /row cards -->

      <!-- ══════════════════════════════════════
           SEÇÃO PRODUTOS
      ══════════════════════════════════════ -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
          Produtos
        </h5>
        <button type="button"
                class="btn btn-success btn-sm d-flex align-items-center gap-2"
                data-sw-open="modal-cadastrar-produto">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Novo Produto
        </button>
      </div>

      <div class="card-table-wrapper mb-5">
        <div class="table-responsive">
          <table class="table table-safrawise mb-0" id="tabela-produtos">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Marca</th>
                <th>Unidade</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($produtos)): ?>
                <tr>
                  <td colspan="3" class="text-center py-5 text-muted">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5"
                         class="mb-3 d-block mx-auto opacity-50">
                      <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4z"/>
                    </svg>
                    Nenhum produto cadastrado.<br>
                    Clique em <strong>"Novo Produto"</strong> para adicionar.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($produtos as $produto): ?>
                  <tr>
                    <td class="fw-medium" style="color:var(--texto-escuro);">
                      <?= htmlspecialchars($produto['nome']) ?>
                    </td>
                    <td class="text-muted small">
                      <?= !empty($produto['marca'])
                            ? htmlspecialchars(mb_strimwidth($produto['marca'], 0, 80, '…'))
                            : '<span class="opacity-50">—</span>' ?>
                    </td>
                    <td class="text-muted small">
                      <?= !empty($produto['unidade_medida'])
                            ? htmlspecialchars(mb_strimwidth($produto['unidade_medida'], 0, 80, '…'))
                            : '<span class="opacity-50">—</span>' ?>
                    </td>
                    <td class="text-muted small">
                      <?= !empty($produto['descricao'])
                            ? htmlspecialchars(mb_strimwidth($produto['descricao'], 0, 80, '…'))
                            : '<span class="opacity-50">—</span>' ?>
                    </td>
                    <td class="text-muted small">
                      <?= !empty($produto['tipo'])
                            ? htmlspecialchars(mb_strimwidth($produto['tipo'], 0, 80, '…'))
                            : '<span class="opacity-50">—</span>' ?>
                    </td>
                    <td class="text-end">
                      <div class="d-flex gap-2 justify-content-end">
                        <button class="btn-table-action" title="Editar" type="button"
                            data-sw-edit-produto
                            data-id="<?= $produto['id'] ?>"
                            data-nome="<?= htmlspecialchars($produto['nome'],          ENT_QUOTES) ?>"
                            data-marca="<?= htmlspecialchars($produto['marca']         ?? '', ENT_QUOTES) ?>"
                            data-tipo="<?= htmlspecialchars($produto['tipo']           ?? '', ENT_QUOTES) ?>"
                            data-unidade="<?= htmlspecialchars($produto['unidade_medida'] ?? '', ENT_QUOTES) ?>"
                            data-descricao="<?= htmlspecialchars($produto['descricao'] ?? '', ENT_QUOTES) ?>">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </button>
                        <button class="btn-table-action" title="Excluir" type="button"
                            style="color:#e05252;border-color:#f5bcbc;"
                            data-sw-delete-produto
                            data-id="<?= $produto['id'] ?>"
                            data-nome="<?= htmlspecialchars($produto['nome'], ENT_QUOTES) ?>">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div> 
        <div class="table-footer centralizado">
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">Linhas por página:</span>
          <select class="perpage-select" id="perpage-produtos"
                  onchange="paginators.produtos.setPerPage(+this.value)">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="99999">Todas</option>
          </select>
          <span class="small text-muted" id="info-produtos"></span>
        </div>
        <div class="d-flex align-items-center gap-1" id="pages-produtos"></div>
      </div>
      </div>

      <!-- ══════════════════════════════════════
           SEÇÃO CULTURAS
      ══════════════════════════════════════ -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
          Culturas
        </h5>
        <button type="button"
                class="btn btn-success btn-sm d-flex align-items-center gap-2"
                data-sw-open="modal-cadastrar-cultura">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Nova Cultura
        </button>
      </div>

      <div class="card-table-wrapper">
        <div class="table-responsive">
          <table class="table table-safrawise mb-0" id="tabela-culturas">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($culturas)): ?>
                <tr>
                  <td colspan="3" class="text-center py-5 text-muted">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5"
                         class="mb-3 d-block mx-auto opacity-50">
                      <path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                      <path d="M12 8v4l3 3"/>
                    </svg>
                    Nenhuma cultura cadastrada.<br>
                    Clique em <strong>"Nova Cultura"</strong> para adicionar.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($culturas as $cultura): ?>
                  <tr>
                    <td class="fw-medium" style="color:var(--texto-escuro);">
                      <?= htmlspecialchars($cultura['nome']) ?>
                    </td>
                    <td class="text-muted small">
                      <?= !empty($cultura['variedade'])
                            ? htmlspecialchars(mb_strimwidth($cultura['variedade'], 0, 80, '…'))
                            : '<span class="opacity-50">—</span>' ?>
                    </td>
                    <td class="text-end">
                      <div class="d-flex gap-2 justify-content-end">
                        <button class="btn-table-action" title="Editar" type="button"
                                data-sw-edit-cultura
                                data-id="<?= $cultura['id'] ?>"
                                data-nome="<?= htmlspecialchars($cultura['nome'], ENT_QUOTES) ?>"
                                data-variedade="<?= htmlspecialchars($cultura['variedade'] ?? '', ENT_QUOTES) ?>">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </button>
                        <button class="btn-table-action" title="Excluir" type="button"
                                style="color:#e05252;border-color:#f5bcbc;"
                                data-sw-delete-cultura
                                data-id="<?= $cultura['id'] ?>"
                                data-nome="<?= htmlspecialchars($cultura['nome'], ENT_QUOTES) ?>"
                                data-variedade="<?= htmlspecialchars($cultura['variedade'] ?? '', ENT_QUOTES) ?>">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="table-footer flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">Linhas por página:</span>
            <select class="perpage-select" id="perpage-culturas"
                    onchange="paginators.culturas.setPerPage(+this.value)">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="99999">Todas</option>
            </select>
            <span class="small text-muted" id="info-culturas"></span>
          </div>
          <div class="d-flex align-items-center gap-1" id="pages-culturas"></div>
        </div>
      </div>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->


<!-- ══════════════════════════════
     MODALS — PRODUTO
══════════════════════════════ -->

<!-- Cadastrar Produto -->
<div class="modal fade sw-modal" id="modal-cadastrar-produto"
     tabindex="-1" aria-labelledby="lbl-cadastrar-produto"
     aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="lbl-cadastrar-produto">Novo Produto</h5>
          <p class="modal-subtitle mb-0">Preencha os dados do produto.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-cadastrar-produto" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form id="form-cadastrar-produto" method="POST" action="index.php?page=store_produto">
          <div class="mb-3">
            <label class="form-label" for="cp-nome">Nome do produto</label>
            <input type="text" class="form-control" id="cp-nome" name="nome"
                   placeholder="Ex: Soja, Milho, Trigo..." required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="cp-marca">Marca do produto</label>
            <input type="text" class="form-control" id="cp-marca" name="marca"
                   placeholder="Ex: Monsanto, Bayer, Syngenta...">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="cadastro-tipo">Categoria</label>
              <select class="form-select" id="cadastro-tipo" name="tipo">
                <option value="">Selecione...</option>
                <option value="Herbicida">Herbicida</option>
                <option value="Fungicida">Fungicida</option>
                <option value="Inseticida">Inseticida</option>
                <option value="Fertilizante">Fertilizante</option>
                <option value="Adubo">Adubo</option>
                <option value="Nematicida">Nematicida</option>
                <option value="Cereal">Cereal</option>
                <option value="Regulador de crescimento">Regulador de crescimento</option>
                <option value="Semente">Semente</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="cadastro-unidade">Unidade de medida</label>
              <select class="form-select" id="cadastro-unidade" name="un_medida">
                <option value="">Selecione...</option>
                <option value="L">L — Litro</option>
                <option value="mL">mL — Mililitro</option>
                <option value="kg">kg — Quilograma</option>
                <option value="g">g — Grama</option>
                <option value="t">t — Tonelada</option>
                <option value="sc">sc — Saca</option>
                <option value="cx">cx — Caixa</option>
                <option value="un">un — Unidade</option>
              </select>
            </div>
            </div>
          <div class="mb-0">
            <label class="form-label" for="cp-descricao">
              Descrição
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
            </label>
            <textarea class="form-control" id="cp-descricao" name="descricao"
                      rows="3" placeholder="Informações adicionais..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-cadastrar-produto">Cancelar</button>
        <button type="submit" form="form-cadastrar-produto"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Salvar produto
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Editar Produto -->
<div class="modal fade sw-modal" id="modal-editar-produto"
     tabindex="-1" aria-labelledby="lbl-editar-produto" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="lbl-editar-produto">Editar Produto</h5>
          <p class="modal-subtitle mb-0">Atualize os dados do produto.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-editar-produto" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
  <form id="form-editar-produto" method="POST" action="index.php?page=update_produto">
    <input type="hidden" id="ep-id" name="produto_id"> <!-- controller espera produto_id -->

    <div class="row g-3 mb-3">
      <div class="col-md-8">
        <label class="form-label" for="ep-nome">Nome do produto</label>
        <input type="text" class="form-control" id="ep-nome" name="nome" required>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="ep-marca">Marca</label>
        <input type="text" class="form-control" id="ep-marca" name="marca" required>
      </div>
    </div>

    <div class="row g-3 mb-">
      <div class="col-md-6">
        <label class="form-label" for="ep-tipo">Categoria</label>
        <select class="form-select" id="ep-tipo" name="tipo">
          <option value="">Selecione...</option>
          <option value="Herbicida">Herbicida</option>
          <option value="Fungicida">Fungicida</option>
          <option value="Inseticida">Inseticida</option>
          <option value="Fertilizante">Fertilizante</option>
          <option value="Adubo">Adubo</option>
          <option value="Nematicida">Nematicida</option>
          <option value="Regulador de crescimento">Regulador de crescimento</option>
          <option value="Semente">Semente</option>
          <option value="Outro">Outro</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="ep-unidade">Unidade de medida</label>
        <select class="form-select" id="ep-unidade" name="un_medida">
          <option value="">Selecione...</option>
          <option value="L">L — Litro</option>
          <option value="mL">mL — Mililitro</option>
          <option value="kg">kg — Quilograma</option>
          <option value="g">g — Grama</option>
          <option value="t">t — Tonelada</option>
          <option value="sc">sc — Saca</option>
          <option value="cx">cx — Caixa</option>
          <option value="un">un — Unidade</option>
        </select>
      </div>
    </div>

    <div class="mb-0">
      <label class="form-label" for="ep-descricao">Descrição</label>
      <textarea class="form-control" id="ep-descricao" name="descricao" rows="2"></textarea>
    </div>

  </form>
</div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-editar-produto">Cancelar</button>
        <button type="submit" form="form-editar-produto"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          Salvar alterações
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Excluir Produto -->
<div class="modal fade sw-modal" id="modal-excluir-produto"
     tabindex="-1" aria-labelledby="lbl-excluir-produto" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background:#3d1515;">
        <div>
          <h5 class="modal-title" id="lbl-excluir-produto">Excluir Produto</h5>
          <p class="modal-subtitle mb-0">Esta ação não pode ser desfeita.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-excluir-produto" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex gap-3 align-items-start">
          <div style="width:44px;height:44px;border-radius:12px;background:rgba(220,50,50,.1);
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#e05252;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
              <path d="M10 11v6M14 11v6"/>
              <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
          </div>
          <div>
            <p class="mb-1" style="color:var(--texto-escuro);font-weight:500;">
              Tem certeza que deseja excluir <strong id="del-produto-nome"></strong>?
            </p>
            <p class="mb-0 small text-muted">
              O produto será removido permanentemente. Insumos vinculados a ele também serão afetados.
            </p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <form id="form-excluir-produto" method="POST" action="index.php?page=delete_produto">
          <input type="hidden" id="del-produto-id" name="produto_id">
        </form>
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-excluir-produto">Cancelar</button>
        <button type="submit" form="form-excluir-produto"
                class="btn d-flex align-items-center gap-2"
                style="background:#e05252;border-color:#e05252;color:#fff;border-radius:14px;font-weight:600;padding:10px 20px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
          </svg>
          Confirmar exclusão
        </button>
      </div>
    </div>
  </div>
</div>


<!-- ══════════════════════════════
     MODALS — CULTURA
══════════════════════════════ -->

<!-- Cadastrar Cultura -->
<div class="modal fade sw-modal" id="modal-cadastrar-cultura"
     tabindex="-1" aria-labelledby="lbl-cadastrar-cultura"
     aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="lbl-cadastrar-cultura">Nova Cultura</h5>
          <p class="modal-subtitle mb-0">Preencha os dados da cultura.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-cadastrar-cultura" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form id="form-cadastrar-cultura" method="POST" action="index.php?page=store_cultura">
          <div class="mb-3">
            <label class="form-label" for="cc-nome">Nome da cultura</label>
            <input type="text" class="form-control" id="cc-nome" name="nome"
                   placeholder="Ex: Soja Convencional, Milho Safrinha..." required>
          </div>
          <div class="mb-0">
            <label class="form-label" for="cc-variadade">
              Variedade ou descrição
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
            </label>
            <textarea class="form-control" id="cc-variadade" name="variedade"
                      rows="3" placeholder="Variedade ou descrição (opcional)"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-cadastrar-cultura">Cancelar</button>
        <button type="submit" form="form-cadastrar-cultura"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Salvar cultura
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Editar Cultura -->
<div class="modal fade sw-modal" id="modal-editar-cultura"
     tabindex="-1" aria-labelledby="lbl-editar-cultura" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="lbl-editar-cultura">Editar Cultura</h5>
          <p class="modal-subtitle mb-0">Atualize os dados da cultura.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-editar-cultura" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form id="form-editar-cultura" method="POST" action="index.php?page=update_cultura">
          <input type="hidden" id="ec-id" name="cultura_id">
          <div class="mb-3">
            <label class="form-label" for="ec-nome">Nome da cultura</label>
            <input type="text" class="form-control" id="ec-nome" name="nome" required>
          </div>
          <div class="mb-0">
            <label class="form-label" for="ec-descricao">Variedade ou descrição</label>
            <textarea class="form-control" id="ec-descricao" name="variedade" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-editar-cultura">Cancelar</button>
        <button type="submit" form="form-editar-cultura"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          Salvar alterações
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Excluir Cultura -->
<div class="modal fade sw-modal" id="modal-excluir-cultura"
     tabindex="-1" aria-labelledby="lbl-excluir-cultura" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background:#3d1515;">
        <div>
          <h5 class="modal-title" id="lbl-excluir-cultura">Excluir Cultura</h5>
          <p class="modal-subtitle mb-0">Esta ação não pode ser desfeita.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-excluir-cultura" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex gap-3 align-items-start">
          <div style="width:44px;height:44px;border-radius:12px;background:rgba(220,50,50,.1);
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#e05252;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
              <path d="M10 11v6M14 11v6"/>
              <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
          </div>
          <div>
            <p class="mb-1" style="color:var(--texto-escuro);font-weight:500;">
              Tem certeza que deseja excluir <strong id="del-cultura-nome"></strong>?
            </p>
            <p class="mb-0 small text-muted">
              A cultura será removida permanentemente. Talhões vinculados a ela podem ser afetados.
            </p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <form id="form-excluir-cultura" method="POST" action="index.php?page=delete_cultura">
          <input type="hidden" id="del-cultura-id" name="cultura_id">
        </form>
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-excluir-cultura">Cancelar</button>
        <button type="submit" form="form-excluir-cultura"
                class="btn d-flex align-items-center gap-2"
                style="background:#e05252;border-color:#e05252;color:#fff;border-radius:14px;font-weight:600;padding:10px 20px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
          </svg>
          Confirmar exclusão
        </button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="../../public/js/paginacao_tabelas.js"></script>

<script>
// ── Toast ──────────────────────────────────────────────────────
function closeToast(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('hide');
  setTimeout(() => el.remove(), 320);
}
document.querySelectorAll('.toast').forEach(t => {
  const d = parseFloat(getComputedStyle(t).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(t.id), d);
});

document.querySelectorAll('[data-sw-edit-produto]').forEach(btn => {
  btn.addEventListener('click', function () {
    const d = this.dataset;

    document.getElementById('ep-id').value       = d.id;
    document.getElementById('ep-nome').value     = d.nome;
    document.getElementById('ep-marca').value    = d.marca;
    document.getElementById('ep-descricao').value = d.descricao;

    // Selects precisam ter a option correta marcada
    const selectTipo    = document.getElementById('ep-tipo');
    const selectUnidade = document.getElementById('ep-unidade');

    for (const opt of selectTipo.options)    opt.selected = opt.value === d.tipo;
    for (const opt of selectUnidade.options) opt.selected = opt.value === d.unidade;

    ModalManager.open('modal-editar-produto');
  });
});

document.querySelectorAll('[data-sw-delete-produto]').forEach(btn => {
  btn.addEventListener('click', function () {
    document.getElementById('del-produto-id').value         = this.dataset.id;
    document.getElementById('del-produto-nome').textContent = this.dataset.nome;
    ModalManager.open('modal-excluir-produto');
  });
});

// ── Editar Cultura ─────────────────────────────────────────────
document.querySelectorAll('[data-sw-edit-cultura]').forEach(btn => {
  btn.addEventListener('click', function () {
    document.getElementById('ec-id').value        = this.dataset.id;
    document.getElementById('ec-nome').value      = this.dataset.nome;
    document.getElementById('ec-descricao').value = this.dataset.variedade;
    ModalManager.open('modal-editar-cultura');
  });
});

// ── Excluir Cultura ────────────────────────────────────────────
document.querySelectorAll('[data-sw-delete-cultura]').forEach(btn => {
  btn.addEventListener('click', function () {
    document.getElementById('del-cultura-id').value          = this.dataset.id;
    document.getElementById('del-cultura-nome').textContent  = this.dataset.nome;
    ModalManager.open('modal-excluir-cultura');
  });
});
</script>

</body>
</html>