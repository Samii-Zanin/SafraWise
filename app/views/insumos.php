<?php
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

$pagina_atual = 'insumos';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

function tipoBadgeStyle(string $tipo): string {
    return match ($tipo) {
        'Herbicida'                => 'background:rgba(64,145,108,.12);color:#2d6a4f',
        'Fungicida'                => 'background:rgba(59,130,246,.12);color:#1d4ed8',
        'Inseticida'               => 'background:rgba(220,50,50,.12);color:#b91c1c',
        'Fertilizante'             => 'background:rgba(212,160,23,.14);color:#92400e',
        'Adubo'                    => 'background:rgba(139,92,246,.12);color:#6d28d9',
        'Nematicida'               => 'background:rgba(14,165,233,.12);color:#0369a1',
        'Regulador de crescimento' => 'background:rgba(249,115,22,.12);color:#c2410c',
        default                    => 'background:rgba(107,143,117,.1);color:#3d5a47',
    };
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Insumos</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
</head>
<body>

<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= htmlspecialchars($toast['tipo']) ?>" id="toast-main" style="--toast-duration: 5s">
    <div class="toast-icon">
      <?php if ($toast['tipo'] === 'success'): ?>
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
      <?php elseif ($toast['tipo'] === 'error'): ?>
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
      <?php elseif ($toast['tipo'] === 'warning'): ?>
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
      <?php else: ?>
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
      <?php endif; ?>
    </div>
    <div class="toast-body">
      <div class="toast-title"><?= htmlspecialchars($toast['titulo']) ?></div>
      <div class="toast-msg"><?= htmlspecialchars($toast['mensagem']) ?></div>
    </div>
    <button class="toast-close" onclick="closeToast('toast-main')" type="button">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
      </svg>
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

      <!-- ── Cabeçalho da página ── -->
      <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
          <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
            Catálogo de Insumos
          </h2>
          <p class="text-muted mb-0 small">
            Gerencie os produtos agrícolas disponíveis para uso nas operações.
          </p>
        </div>

        <button type="button"
                class="btn btn-success d-flex align-items-center gap-2"
                data-sw-open="modal-cadastrar-insumo">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Novo Insumo
        </button>
      </div>

      <!-- ── Cards de resumo ── -->
      <?php
        $total = count($insumos);
        $tipos_contagem = array_count_values(array_column($insumos, 'tipo'));
        arsort($tipos_contagem);
        $tipo_principal = array_key_first($tipos_contagem) ?? '—';
        $tipo_principal_qtd = $tipos_contagem[$tipo_principal] ?? 0;
        $qtd_tipos = count($tipos_contagem);
      ?>
      <div class="row g-3 mb-4">

        <div class="col-md-4">
          <div class="card metric-card verde p-3">
            <div class="d-flex align-items-center gap-3">
              <div class="metric-icon verde">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                </svg>
              </div>
              <div>
                <div class="metric-label">Total de insumos</div>
                <div class="metric-value"><?= $total ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card metric-card dourado p-3">
            <div class="d-flex align-items-center gap-3">
              <div class="metric-icon dourado">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <div class="metric-label">Categorias</div>
                <div class="metric-value"><?= $qtd_tipos ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card metric-card azul p-3">
            <div class="d-flex align-items-center gap-3">
              <div class="metric-icon azul">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <div class="metric-label">Tipo mais usado</div>
                <div class="metric-value" style="font-size:18px; line-height:1.3;">
                  <?= $total > 0 ? htmlspecialchars($tipo_principal) : '—' ?>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ── Tabela principal ── -->
      <div class="card-table-wrapper">
        <div class="table-responsive">
          <table class="table table-safrawise mb-0">
            <thead>
              <tr>
                <th>Produto</th>
                <th>Marca</th>
                <th>Categoria</th>
                <th>Unidade</th>
                <th>Valor / Dose</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($insumos)): ?>
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5"
                         class="mb-3 d-block mx-auto opacity-50">
                      <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                      <line x1="3" y1="6" x2="21" y2="6"/>
                      <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                    Nenhum insumo cadastrado ainda.<br>
                    Clique em <strong>"Novo Insumo"</strong> para adicionar.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($insumos as $insumo): ?>
                  <tr>
                    <td class="fw-medium text-light">
                      <?= htmlspecialchars($insumo['nome']) ?>
                      <?php if (!empty($insumo['descricao'])): ?>
                        <div class="small opacity-50 fw-normal">
                          <?= htmlspecialchars(mb_strimwidth($insumo['descricao'], 0, 60, '…')) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td class="text-light">
                      <?= !empty($insumo['marca'])
                            ? htmlspecialchars($insumo['marca'])
                            : '<span class="opacity-50">—</span>' ?>
                    </td>
                    <td>
                      <?php if (!empty($insumo['tipo'])): ?>
                        <span class="status-pill" style="<?= tipoBadgeStyle($insumo['tipo']) ?>">
                          <?= htmlspecialchars($insumo['tipo']) ?>
                        </span>
                      <?php else: ?>
                        <span class="opacity-50 small">—</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-light">
                      <?= !empty($insumo['unidade_medida'])
                            ? htmlspecialchars($insumo['unidade_medida'])
                            : '<span class="opacity-50">—</span>' ?>
                    </td>
                    <td class="fw-medium" style="color:var(--verde-vivo);">
                      R$ <?= number_format((float) $insumo['valor_por_dose'], 2, ',', '.') ?>
                    </td>
                    <td class="text-end">
                      <div class="d-flex gap-2 justify-content-end">
                        <button class="btn-table-action" title="Editar" type="button"
                                data-sw-edit-insumo
                                data-insumo-id="<?= $insumo['id'] ?>"
                                data-produto-id="<?= $insumo['produto_id'] ?>"
                                data-nome="<?= htmlspecialchars($insumo['nome'], ENT_QUOTES) ?>"
                                data-marca="<?= htmlspecialchars($insumo['marca'] ?? '', ENT_QUOTES) ?>"
                                data-tipo="<?= htmlspecialchars($insumo['tipo'] ?? '', ENT_QUOTES) ?>"
                                data-unidade="<?= htmlspecialchars($insumo['unidade_medida'] ?? '', ENT_QUOTES) ?>"
                                data-descricao="<?= htmlspecialchars($insumo['descricao'] ?? '', ENT_QUOTES) ?>"
                                data-valor="<?= number_format((float) $insumo['valor_por_dose'], 2, '.', '') ?>">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                               stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </button>

                        <button class="btn-table-action" title="Excluir" type="button"
                                style="color:#e05252; border-color:#f5bcbc;"
                                data-sw-delete-insumo
                                data-insumo-id="<?= $insumo['id'] ?>"
                                data-produto-id="<?= $insumo['produto_id'] ?>"
                                data-nome="<?= htmlspecialchars($insumo['nome'], ENT_QUOTES) ?>">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                               stroke="currentColor" stroke-width="2">
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
      </div>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->


<!-- ══════════════════════════════════════════════════
     MODAL — Cadastrar Insumo
══════════════════════════════════════════════════ -->
<div class="modal fade sw-modal"
     id="modal-cadastrar-insumo"
     tabindex="-1"
     aria-labelledby="modal-cadastrar-insumo-label"
     aria-hidden="true"
     data-sw-reset-on-close>

  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modal-cadastrar-insumo-label">Novo Insumo</h5>
          <p class="modal-subtitle mb-0">Preencha os dados do produto agrícola.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-cadastrar-insumo" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <form id="form-cadastrar-insumo" method="POST" action="index.php?page=store_insumo">

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label" for="cadastro-nome">Nome do produto</label>
              <input type="text" class="form-control" id="cadastro-nome" name="nome"
                     placeholder="Ex: Glifosato 480" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="cadastro-marca">Marca</label>
              <input type="text" class="form-control" id="cadastro-marca" name="marca"
                     placeholder="Ex: Roundup">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-5">
              <label class="form-label" for="cadastro-tipo">Categoria</label>
              <select class="form-select" id="cadastro-tipo" name="tipo">
                <option value="">Selecione...</option>
                <option value="Herbicida">Herbicida</option>
                <option value="Fungicida">Fungicida</option>
                <option value="Inseticida">Inseticida</option>
                <option value="Fertilizante">Fertilizante</option>
                <option value="Adubo">Adubo</option>
                <option value="Nematicida">Nematicida</option>
                <option value="Regulador de crescimento">Regulador de crescimento</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="cadastro-unidade">Unidade de medida</label>
              <select class="form-select" id="cadastro-unidade" name="unidade_medida">
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
            <div class="col-md-3">
              <label class="form-label" for="cadastro-valor">Valor por dose (R$)</label>
              <input type="number" class="form-control" id="cadastro-valor" name="valor_por_dose"
                     placeholder="0,00" step="0.01" min="0" required>
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label" for="cadastro-descricao">
              Descrição
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
            </label>
            <textarea class="form-control" id="cadastro-descricao" name="descricao"
                      rows="2" placeholder="Informações adicionais sobre o produto..."></textarea>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-cadastrar-insumo">
          Cancelar
        </button>
        <button type="submit" form="form-cadastrar-insumo"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Salvar insumo
        </button>
      </div>

    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════
     MODAL — Editar Insumo
══════════════════════════════════════════════════ -->
<div class="modal fade sw-modal"
     id="modal-editar-insumo"
     tabindex="-1"
     aria-labelledby="modal-editar-insumo-label"
     aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modal-editar-insumo-label">Editar Insumo</h5>
          <p class="modal-subtitle mb-0">Atualize os dados do produto agrícola.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-editar-insumo" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <form id="form-editar-insumo" method="POST" action="index.php?page=update_insumo">

          <input type="hidden" id="edit-insumo-id" name="insumo_id">
          <input type="hidden" id="edit-produto-id" name="produto_id">

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label" for="edit-nome">Nome do produto</label>
              <input type="text" class="form-control" id="edit-nome" name="nome" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="edit-marca">Marca</label>
              <input type="text" class="form-control" id="edit-marca" name="marca">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-5">
              <label class="form-label" for="edit-tipo">Categoria</label>
              <select class="form-select" id="edit-tipo" name="tipo">
                <option value="">Selecione...</option>
                <option value="Herbicida">Herbicida</option>
                <option value="Fungicida">Fungicida</option>
                <option value="Inseticida">Inseticida</option>
                <option value="Fertilizante">Fertilizante</option>
                <option value="Adubo">Adubo</option>
                <option value="Nematicida">Nematicida</option>
                <option value="Regulador de crescimento">Regulador de crescimento</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="edit-unidade">Unidade de medida</label>
              <select class="form-select" id="edit-unidade" name="unidade_medida">
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
            <div class="col-md-3">
              <label class="form-label" for="edit-valor">Valor por dose (R$)</label>
              <input type="number" class="form-control" id="edit-valor" name="valor_por_dose"
                     step="0.01" min="0" required>
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label" for="edit-descricao">Descrição</label>
            <textarea class="form-control" id="edit-descricao" name="descricao" rows="2"></textarea>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-editar-insumo">
          Cancelar
        </button>
        <button type="submit" form="form-editar-insumo"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          Salvar alterações
        </button>
      </div>

    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════
     MODAL — Confirmar Exclusão
══════════════════════════════════════════════════ -->
<div class="modal fade sw-modal"
     id="modal-excluir-insumo"
     tabindex="-1"
     aria-labelledby="modal-excluir-insumo-label"
     aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header" style="background:#3d1515;">
        <div>
          <h5 class="modal-title" id="modal-excluir-insumo-label">Excluir Insumo</h5>
          <p class="modal-subtitle mb-0">Esta ação não pode ser desfeita.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-excluir-insumo" aria-label="Fechar"></button>
      </div>

      <div class="modal-body" style="background:var(--branco);">
        <div class="d-flex gap-3 align-items-start">
          <div style="width:44px;height:44px;border-radius:12px;background:rgba(220,50,50,.1);
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#e05252;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
              <path d="M10 11v6M14 11v6"/>
              <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
          </div>
          <div>
            <p class="mb-1" style="color:var(--texto-escuro); font-weight:500;">
              Tem certeza que deseja excluir
              <strong id="delete-nome-display"></strong>?
            </p>
            <p class="mb-0 small text-muted">
              O insumo e seu registro de produto serão removidos permanentemente do catálogo.
              Esta ação falhará caso o insumo esteja vinculado a operações agrícolas.
            </p>
          </div>
        </div>
      </div>

      <div class="modal-footer" style="background:#f5f9f6;">
        <form id="form-excluir-insumo" method="POST" action="index.php?page=delete_insumo">
          <input type="hidden" id="delete-insumo-id" name="insumo_id">
          <input type="hidden" id="delete-produto-id" name="produto_id">
        </form>

        <button type="button" class="btn-modal-cancel" data-sw-close="modal-excluir-insumo">
          Cancelar
        </button>
        <button type="submit" form="form-excluir-insumo"
                class="btn d-flex align-items-center gap-2"
                style="background:#e05252;border-color:#e05252;color:#fff;
                       border-radius:14px;font-weight:600;padding:10px 20px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
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

<script>
/* ── Toast auto-close ── */
function closeToast(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('hide');
  setTimeout(() => el.remove(), 320);
}

document.querySelectorAll('.toast').forEach(toast => {
  const duration = parseFloat(getComputedStyle(toast).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(toast.id), duration);
});

/* ── Editar insumo: popula o modal com os dados da linha ── */
document.querySelectorAll('[data-sw-edit-insumo]').forEach(btn => {
  btn.addEventListener('click', function () {
    const d = this.dataset;
    document.getElementById('edit-insumo-id').value  = d.insumoId;
    document.getElementById('edit-produto-id').value = d.produtoId;
    document.getElementById('edit-nome').value        = d.nome;
    document.getElementById('edit-marca').value       = d.marca;
    document.getElementById('edit-descricao').value   = d.descricao;
    document.getElementById('edit-valor').value       = d.valor;

    const selectTipo    = document.getElementById('edit-tipo');
    const selectUnidade = document.getElementById('edit-unidade');

    for (const opt of selectTipo.options) {
      opt.selected = opt.value === d.tipo;
    }
    for (const opt of selectUnidade.options) {
      opt.selected = opt.value === d.unidade;
    }

    ModalManager.open('modal-editar-insumo');
  });
});

/* ── Excluir insumo: popula o modal de confirmação ── */
document.querySelectorAll('[data-sw-delete-insumo]').forEach(btn => {
  btn.addEventListener('click', function () {
    const d = this.dataset;
    document.getElementById('delete-insumo-id').value   = d.insumoId;
    document.getElementById('delete-produto-id').value  = d.produtoId;
    document.getElementById('delete-nome-display').textContent = d.nome;
    ModalManager.open('modal-excluir-insumo');
  });
});
</script>

</body>
</html>
