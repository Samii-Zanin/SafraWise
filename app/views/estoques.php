<?php
/*
 * app/views/estoques.php
 *
 * Variáveis esperadas:
 *   $estoqueInsumos — array de EstoqueInsumosController::getAll()
 *   $movimentacoesInsumos — array de OperacoesFinanceirasController::getMovimentacoesEstoqueInsumos()
 *   $estoquesSilos  — array de SiloController::getAll()
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

$pagina_atual = 'estoques';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

$totalInsumos      = count($estoqueInsumos ?? []);
$valorTotalInsumos = array_sum(array_column($estoqueInsumos ?? [], 'valor_total_em_produto'));

$maiorQtdInsumo = null;
$maiorQtdValor  = 0;
foreach ($estoqueInsumos ?? [] as $item) {
    if ((float)$item['quantidade'] > $maiorQtdValor) {
        $maiorQtdValor  = (float)$item['quantidade'];
        $maiorQtdInsumo = $item['nome'];
    }
}




$totalSilos    = count($estoquesSilos ?? []);
$totalKgSilos  = array_sum(array_column($estoquesSilos ?? [], 'total_kg'));

$maiorCultura    = null;
$maiorKgCultura  = 0;
foreach ($estoquesSilos ?? [] as $item) {
    if ((float)$item['total_kg'] > $maiorKgCultura) {
        $maiorKgCultura = (float)$item['total_kg'];
        $maiorCultura   = $item['cultura'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Estoques</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
    /* ── Toggle de visão ── */
    .view-toggle {
      display: inline-flex;
      background: #eef2ee;
      border-radius: 12px;
      padding: 4px;
      gap: 4px;
    }

    .view-toggle-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      border: none;
      border-radius: 9px;
      background: transparent;
      font-size: 13px;
      font-weight: 600;
      color: var(--texto-suave);
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap;
    }

    .view-toggle-btn.active {
      background: var(--verde-escuro);
      color: #fff;
      box-shadow: 0 2px 10px rgba(15, 35, 24, 0.25);
    }

    .view-toggle-btn svg { opacity: 0.6; }
    .view-toggle-btn.active svg { opacity: 1; }

    /* ── Cards macro ── */
    .macro-card {
      background: var(--branco);
      border: 1px solid #e2ece5;
      border-radius: var(--bs-border-radius-lg);
      padding: 22px 24px;
      position: relative;
      overflow: hidden;
      transition: box-shadow 0.2s;
    }

    .macro-card:hover { box-shadow: 0 4px 20px rgba(15, 35, 24, 0.08); }

    .macro-card::after {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      border-radius: 2px 2px 0 0;
    }

    .macro-card.verde::after  { background: var(--verde-vivo); }
    .macro-card.dourado::after { background: var(--dourado); }
    .macro-card.azul::after   { background: #3b82f6; }

    .macro-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.7px;
      color: var(--texto-suave);
      margin-bottom: 6px;
    }

    .macro-value {
      font-family: 'DM Serif Display', Georgia, serif;
      font-size: 26px;
      color: var(--texto-escuro);
      line-height: 1.1;
    }

    .macro-sub {
      font-size: 12px;
      color: var(--texto-suave);
      margin-top: 4px;
    }

    /* ── Filtros ── */
    .filtro-bar {
      background: var(--branco);
      border: 1px solid #e2ece5;
      border-radius: var(--bs-border-radius-lg);
      padding: 16px 20px;
      display: flex;
      align-items: flex-end;
      gap: 16px;
      flex-wrap: wrap;
    }

    .filtro-bar .form-label {
      margin-bottom: 4px;
      font-size: 10px;
    }

    .filtro-bar .form-control,
    .filtro-bar .form-select {
      font-size: 13px;
      min-width: 150px;
    }

    .btn-limpar-filtro {
      background: transparent;
      border: 1.5px solid #dde8e0;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 500;
      color: var(--texto-suave);
      padding: 8px 16px;
      cursor: pointer;
      transition: all 0.15s;
      white-space: nowrap;
    }

    .btn-limpar-filtro:hover {
      border-color: var(--verde-vivo);
      color: var(--verde-vivo);
    }

    /* ── Tabelas ── */
    .estoques-table thead th {
      background: #f5f9f6;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: var(--texto-suave);
      padding: 13px 18px;
      border-bottom: 1px solid #e2ece5;
      white-space: nowrap;
    }

    .estoques-table tbody td {
      padding: 13px 18px;
      vertical-align: middle;
      font-size: 13.5px;
      border-bottom: 1px solid #f0f5f1;
    }

    .estoques-table tbody tr:last-child td { border-bottom: none; }
    .estoques-table tbody tr:hover td     { background: #f7fbf8; }

    .td-valor {
      font-family: 'DM Serif Display', Georgia, serif;
      font-size: 15px;
      color: var(--verde-vivo);
    }

    .tipo-badge {
      font-size: 10px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      white-space: nowrap;
    }

    /* Rodapé da tabela */
    .table-footer {
      background: #f9fcfa;
      border-top: 1px solid #e2ece5;
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    /* ── Cards de decisão ── */
    .decisao-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      padding: 28px 16px;
      border: 2px solid #e2ece5;
      border-radius: 16px;
      background: var(--branco);
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: center;
      width: 100%;
    }
    .decisao-card:hover {
      border-color: var(--verde-vivo);
      background: #f0faf4;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(64,145,108,.15);
    }
    .decisao-card.azul:hover {
      border-color: #3b82f6;
      background: #eff6ff;
      box-shadow: 0 6px 20px rgba(59,130,246,.15);
    }
    .decisao-icon {
      width: 52px; height: 52px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .decisao-icon svg { width: 26px; height: 26px; }
    .decisao-icon.verde { background: rgba(64,145,108,.12); color: var(--verde-vivo); }
    .decisao-icon.azul  { background: rgba(59,130,246,.10); color: #3b82f6; }
    .decisao-titulo {
      font-family: 'DM Serif Display', Georgia, serif;
      font-size: 15px;
      color: var(--texto-escuro);
      line-height: 1.2;
    }
    .decisao-desc { font-size: 12px; color: var(--texto-suave); line-height: 1.4; }
    
    /* ── Caixa do valor/dose calculado ── */
    .dose-preview {
      background: #f0faf4;
      border: 1px solid #c3e0cc;
      border-radius: 10px;
      padding: 10px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
    .dose-preview-label { font-size: 11px; font-weight: 700; text-transform: uppercase;
                          letter-spacing: .6px; color: var(--texto-suave); }
    .dose-preview-value { font-family: 'DM Serif Display', Georgia, serif;
                          font-size: 18px; color: var(--verde-vivo); }
  </style>
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

      <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
          <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
            Estoques
          </h2>
          <p class="text-muted mb-0 small">
            Visualize o estoque de insumos e a produção armazenada nos silos.
          </p>
        </div>

        <div class="view-toggle" role="group" aria-label="Selecionar visão">
          <button class="view-toggle-btn active" id="btn-insumos" onclick="trocarVisao('insumos')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            Estoque de Insumos
          </button>
          <button class="view-toggle-btn" id="btn-silos" onclick="trocarVisao('silos')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Silos
          </button>
        </div>
      </div>

      <!-- ══════════════════════════════════════
           VISÃO: ESTOQUE DE INSUMOS
      ══════════════════════════════════════ -->
      <div class="view-section d-block" id="section-insumos">

        <!-- Cards macro -->
        <div class="row g-3 mb-4">
          <div class="col-md-4 col-sm-6">
            <div class="macro-card verde">
              <div class="macro-label">Valor total em estoque</div>
              <div class="macro-value">
                R$ <?= number_format($valorTotalInsumos, 2, ',', '.') ?>
              </div>
              <div class="macro-sub"><?= $totalInsumos ?> itens cadastrados</div>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="macro-card dourado">
              <div class="macro-label">Produto em maior quantidade</div>
              <div class="macro-value" style="font-size:20px;">
                <?= $maiorQtdInsumo ? htmlspecialchars($maiorQtdInsumo) : '—' ?>
              </div>
              <div class="macro-sub">
                <?= $maiorQtdInsumo
                    ? number_format($maiorQtdValor, 2, ',', '.') . ' unidades'
                    : 'Nenhum item no estoque' ?>
              </div>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="macro-card azul">
              <div class="macro-label">Itens no estoque</div>
              <div class="macro-value"><?= $totalInsumos ?></div>
              <div class="macro-sub">registros cadastrados</div>
            </div>
          </div>
        </div>

        <!-- Filtros -->
        <div class="filtro-bar mb-3" id="filtros-insumos">
          <div>
            <label class="form-label">Buscar produto</label>
            <input type="text" class="form-control form-control-sm"
                   id="busca-insumo" placeholder="Nome ou marca..."
                   oninput="filtrarTabela('tabela-insumos', this.value, [0,1])">
          </div>
          <div>
            <label class="form-label">Categoria</label>
            <select class="form-select form-select-sm" id="filtro-tipo-insumo"
                    onchange="filtrarPorSelect('tabela-insumos', this.value, 3)">
              <option value="">Todas</option>
              <?php
              $tipos = array_unique(array_column($estoqueInsumos ?? [], 'tipo'));
              sort($tipos);
              foreach ($tipos as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn-limpar-filtro" onclick="limparFiltros('insumos')">
            Limpar filtros
          </button>
          <div class="ms-auto d-flex align-items-center gap-2">
            <button type="button"
                    class="btn btn-success btn-sm d-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#modal-decisao-entrada">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2.5"
                   stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>
              </svg>
              Registrar Entrada
            </button>

            <button type="button"
                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
                    data-sw-open="modal-saida-insumo">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2.5"
                   stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/>
              </svg>
              Registrar Saída
            </button>

            <span class="small text-muted ps-2 border-start" id="contagem-insumos">
              <?= $totalInsumos ?> registros
            </span>
          </div>
        </div>

        <!-- Tabela -->
        <div class="card-table-wrapper">
          <div class="table-responsive">
            <table class="table estoques-table mb-0" id="tabela-insumos">
              <caption class="caption-top text-center fw-bold">
                        Estoque de Insumos
              </caption>
              <thead>
                <tr>
                  <th>Propriedade</th>
                  <th>Produto</th>
                  <th>Marca</th>
                  <th>Unidade</th>
                  <th>Categoria</th>
                  <th>Quantidade</th>
                  <th>Valor / Dose</th>
                  <th>Valor Total</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($estoqueInsumos)): ?>
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="1.5"
                           class="mb-3 d-block mx-auto opacity-50">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                      </svg>
                      Nenhum item no estoque de insumos.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($estoqueInsumos as $item): ?>
                    <tr data-busca="<?= strtolower(htmlspecialchars($item['nome'] . ' ' . $item['marca'])) ?>"
                    data-tipo="<?= htmlspecialchars($item['tipo'] ?? '') ?>">
                    <td class="fw-medium" style="color:var(--texto-escuro);">
                      <?= htmlspecialchars($item['propriedade_nome']) ?>
                    </td>
                    <td class="fw-medium" style="color:var(--texto-escuro);">
                      <?= htmlspecialchars($item['nome']) ?>
                      </td>
                      <td class="text-muted"><?= htmlspecialchars($item['marca'] ?? '—') ?></td>
                      <td class="text-muted"><?= htmlspecialchars($item['unidade_medida'] ?? '—') ?></td>
                      <td>
                        <?php if (!empty($item['tipo'])): ?>
                          <span class="tipo-badge"
                                style="background:rgba(64,145,108,.1);color:var(--verde-accent);">
                            <?= htmlspecialchars($item['tipo']) ?>
                          </span>
                        <?php else: ?>
                          <span class="text-muted opacity-50">—</span>
                        <?php endif; ?>
                      </td>
                      <td><?= number_format((float)$item['quantidade'], 2, ',', '.') ?></td>
                      <td class="text-muted">
                        R$ <?= number_format((float)$item['valor_por_dose'], 2, ',', '.') ?>
                      </td>
                      <td class="td-valor">
                        R$ <?= number_format((float)$item['valor_total_em_produto'], 2, ',', '.') ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
                          
          
          <div class="table-footer">
            <span class="small text-muted" id="label-movimentacoes_insumos">
              <?= count($estoqueInsumos ?? []) ?> registros exibidos
            </span>
            <span class="small text-muted">Estoques de Insumos</span>
          </div>
        </div>

        <br>
        
        <!-- TABELA DE MOVIMNETAÇÕES DE ESTOQUE -->
        <div class="card-table-wrapper">
          <div class="table-responsive">
            <table class="table estoques-table mb-0" id="tabela-insumos">
              <caption class="caption-top text-center fw-bold">
                         Movimentações de Estoque de Insumos
              </caption>
              <thead>
                <tr>
                  <th>Tipo de Operação</th>
                  <th>Valor da Operação</th>
                  <th>Produto</th>
                  <th>Quantidade</th>
                  <th>Data da Execução</th>
                  <th>Safra Vinculada</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($movimentacoesInsumos)): ?>
                  <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="1.5"
                           class="mb-3 d-block mx-auto opacity-50">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                      </svg>
                      Nenhum item no estoque de insumos.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($movimentacoesInsumos as $item): ?>
                    <tr data-busca="<?= strtolower(htmlspecialchars($item['tipo_operacao'] . ' ' . $item['nome_produto'])) ?>"
                    data-tipo="<?= htmlspecialchars($item['tipo_operacao'] ?? '') ?>">
                    <td class="fw-medium" style="color:var(--texto-escuro);">
                      <?= htmlspecialchars($item['tipo_operacao']) ?>
                    </td>
                    <td class="fw-medium" style="color:var(--texto-escuro);">
                      R$<?= htmlspecialchars($item['valor_operacao'])?> total <br>
                      R$<?= htmlspecialchars($item['valor_unitario'])?> a unidade
                    </td>
                    <td class="text-muted">
                    <?= htmlspecialchars($item['nome_produto'] ?? '—') ?> <br>
                    <?= htmlspecialchars($item['tipo'] ?? '—') ?>
                  </td>
                      <td class="text-muted">
                        <?= htmlspecialchars($item['quantidade'] ?? '—') ?> unidades </td>
                      <td class="text-muted">
                        <?= htmlspecialchars($item['data_operacao'] ?? '—') ?></td>
                      <td class="text-muted">
                        <?= htmlspecialchars($item['talhao_nome'] ?? '—') ?> <br>
                        <?= htmlspecialchars($item['cultura_plantada'] ?? '—') ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="table-footer">
            <span class="small text-muted" id="label-movimentacoes_insumos">
              <?= count($movimentacoesInsumos ?? []) ?> registros exibidos
            </span>
            <span class="small text-muted">Movimentações de Estoque</span>
          </div>
        </div>

      </div><!-- /section-insumos -->


      <!-- ══════════════════════════════════════
           TELA: SILOS
      ══════════════════════════════════════ -->
      <div class="view-section d-none"  id="section-silos">

        <!-- Cards macro -->
        <div class="row g-3 mb-4">
          <div class="col-md-4 col-sm-6">
            <div class="macro-card verde">
              <div class="macro-label">Total armazenado</div>
              <div class="macro-value">
                <?= number_format($totalKgSilos, 0, ',', '.') ?> kg
              </div>
              <div class="macro-sub"><?= $totalSilos ?> registros de armazenagem</div>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="macro-card dourado">
              <div class="macro-label">Cultura em maior quantidade</div>
              <div class="macro-value" style="font-size:20px;">
                <?= $maiorCultura ? htmlspecialchars($maiorCultura) : '—' ?>
              </div>
              <div class="macro-sub">
                <?= $maiorCultura
                    ? number_format($maiorKgCultura, 0, ',', '.') . ' kg armazenados'
                    : 'Nenhum registro nos silos' ?>
              </div>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="macro-card azul">
              <div class="macro-label">Registros de armazenagem</div>
              <div class="macro-value"><?= $totalSilos ?></div>
              <div class="macro-sub">entre propriedades e culturas</div>
            </div>
          </div>
        </div>

        <!-- Filtros -->
        <div class="filtro-bar mb-3" id="filtros-silos">
          <div>
            <label class="form-label">Buscar cultura</label>
            <input type="text" class="form-control form-control-sm"
                   id="busca-silo" placeholder="Nome da cultura..."
                   oninput="filtrarTabela('tabela-silos', this.value, [0])">
          </div>
          <div>
            <label class="form-label">Propriedade</label>
            <select class="form-select form-select-sm" id="filtro-propriedade-silo"
                    onchange="filtrarPorSelect('tabela-silos', this.value, 1)">
              <option value="">Todas</option>
              <?php
              $silosPropriedades = array_unique(array_column($estoquesSilos ?? [], 'propriedade'));
              sort($silosPropriedades);
              foreach ($silosPropriedades as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label">Estado</label>
            <select class="form-select form-select-sm" id="filtro-estado-silo"
                    onchange="filtrarPorSelect('tabela-silos', this.value, 3)">
              <option value="">Todos</option>
              <?php
              $silosEstados = array_unique(array_column($estoquesSilos ?? [], 'estado'));
              sort($silosEstados);
              foreach ($silosEstados as $e): ?>
                <option value="<?= htmlspecialchars($e) ?>"><?= htmlspecialchars($e) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn-limpar-filtro" onclick="limparFiltros('silos')">
            Limpar filtros
          </button>
          <div class="ms-auto d-flex align-items-center gap-2">
            <button type="button"
                    class="btn btn-success btn-sm d-flex align-items-center gap-1"
                    data-sw-open="modal-entrada-insumo">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2.5"
                   stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>
              </svg>
              Registrar Entrada
            </button>

            <button type="button"
                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#modal-decisao-saida-cereal">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2.5"
                  stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <polyline points="19 12 12 19 5 12"/>
              </svg>
              Registrar Saída
            </button>

            <span class="small text-muted ps-2 border-start" id="contagem-insumos">
              <?= $totalSilos ?> registros
            </span>
          </div>
        </div>

        <!-- Tabela -->
        <div class="card-table-wrapper">
          <div class="table-responsive">
            <table class="table estoques-table mb-0" id="tabela-silos">
              <thead>
                <tr>
                  <th>Cultura</th>
                  <th>Propriedade</th>
                  <th>Município</th>
                  <th>Estado</th>
                  <th>Total Armazenado</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($estoquesSilos)): ?>
                  <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="1.5"
                           class="mb-3 d-block mx-auto opacity-50">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                      </svg>
                      Nenhum registro de armazenagem nos silos.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($estoquesSilos as $item): ?>
                    <tr data-busca="<?= strtolower(htmlspecialchars($item['cultura'])) ?>"
                        data-propriedade="<?= htmlspecialchars($item['propriedade'] ?? '') ?>"
                        data-estado="<?= htmlspecialchars($item['estado'] ?? '') ?>">
                      <td class="fw-medium" style="color:var(--texto-escuro);">
                        <?= htmlspecialchars($item['cultura']) ?>
                      </td>
                      <td class="text-muted"><?= htmlspecialchars($item['propriedade'] ?? '—') ?></td>
                      <td class="text-muted"><?= htmlspecialchars($item['municipio'] ?? '—') ?></td>
                      <td>
                        <span class="tipo-badge"
                              style="background:rgba(59,130,246,.1);color:#1d4ed8;">
                          <?= htmlspecialchars($item['estado'] ?? '—') ?>
                        </span>
                      </td>
                      <td class="td-valor">
                        <?= number_format((float)$item['total_kg'], 0, ',', '.') ?> kg
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="table-footer">
            <span class="small text-muted" id="label-silos">
              <?= count($estoquesSilos ?? []) ?> registros exibidos
            </span>
            <span class="small text-muted">Armazenagem nos silos</span>
          </div>
        </div>
      
      <br>

        <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
          <h6 class="mb-0"
              style="font-family:'DM Serif Display',Georgia,serif;color:var(--texto-escuro);">
            Histórico de Movimentações
          </h6>
          <span class="tipo-badge"
                style="background:rgba(239,68,68,.1);color:#dc2626;">
            Saídas e Vendas de Cereais
          </span>
        </div>

        <div class="card-table-wrapper">
          <div class="table-responsive">
            <table class="table estoques-table mb-0" id="tabela-mov-silos">
              <thead>
                <tr>
                  <th>Tipo</th>
                  <th>Produto</th>
                  <th>Quantidade</th>
                  <th>Valor Total</th>
                  <th>Valor / kg</th>
                  <th>Data</th>
                  <th>Safra</th>
                  <th>Descrição</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($movimentacoesSilos)): ?>
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="1.5"
                          class="mb-3 d-block mx-auto opacity-50">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                      </svg>
                      Nenhuma movimentação registrada.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($movimentacoesSilos as $mov): ?>
                    <tr>
                      <td>
                        <?php
                        $isVenda = $mov['tipo_operacao'] === 'VENDA DE CEREAIS';
                        $badgeBg = $isVenda
                            ? 'rgba(64,145,108,.1)'
                            : 'rgba(239,68,68,.08)';
                        $badgeColor = $isVenda
                            ? 'var(--verde-accent)'
                            : '#dc2626';
                        ?>
                        <span class="tipo-badge"
                              style="background:<?= $badgeBg ?>;color:<?= $badgeColor ?>;">
                          <?= $isVenda ? 'Venda' : 'Saída' ?>
                        </span>
                      </td>
                      <td class="fw-medium" style="color:var(--texto-escuro);">
                        <?= htmlspecialchars($mov['nome_produto'] ?? '—') ?>
                      </td>
                      <td>
                        <?= number_format((float)$mov['quantidade'], 2, ',', '.') ?> kg
                      </td>
                      <td class="<?= $isVenda ? 'td-valor' : 'text-muted' ?>">
                        <?= $isVenda
                            ? 'R$ ' . number_format((float)$mov['valor_operacao'], 2, ',', '.')
                            : '—' ?>
                      </td>
                      <td class="text-muted">
                        <?= $mov['valor_unitario'] > 0
                            ? 'R$ ' . number_format((float)$mov['valor_unitario'], 2, ',', '.')
                            : '—' ?>
                      </td>
                      <td class="text-muted">
                        <?= !empty($mov['data_operacao'])
                            ? date('d/m/Y', strtotime($mov['data_operacao']))
                            : '—' ?>
                      </td>
                      <td class="text-muted small">
                        <?= !empty($mov['cultura_plantada'])
                            ? htmlspecialchars($mov['cultura_plantada'])
                              . ' — ' . htmlspecialchars($mov['talhao_nome'] ?? '')
                            : '—' ?>
                      </td>
                      <td class="text-muted small">
                        <?= !empty($mov['descricao'])
                            ? htmlspecialchars(mb_strimwidth($mov['descricao'], 0, 60, '…'))
                            : '—' ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="table-footer" style="justify-content:center;gap:16px;">
            <div class="d-flex align-items-center gap-2">
              <span class="small text-muted">Linhas por página:</span>
              <select class="perpage-select" id="perpage-mov-silos"
                      onchange="paginators.movSilos.setPerPage(+this.value)">
                <option value="3">3</option>
                <option value="7">7</option>
                <option value="10">10</option>
                <option value="20">20</option>
              </select>
              <span class="small text-muted" id="info-mov-silos"></span>
            </div>
            <div class="d-flex align-items-center gap-1" id="pages-mov-silos"></div>
          </div>
        </div>

      </div><!-- /section-silos -->

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->


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
                <option value="Regulador de crescimento">Semente</option>
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



<!-- ── MODAL 1: Decisão ─────────────────────────────── -->
<div class="modal fade sw-modal" id="modal-decisao-entrada"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
 
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Registrar Entrada de Insumo</h5>
          <p class="modal-subtitle mb-0">
            Esse registro refere-se a uma operação de compra?
          </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
 
      <div class="modal-body pb-4">
        <div class="row g-3">
 
          <div class="col-6">
            <button class="decisao-card" onclick="abrirModalCompra()">
              <div class="decisao-icon verde">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2
                       6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6
                       4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="decisao-titulo">Sim, registrar compra</div>
              <div class="decisao-desc">
                Registra operação financeira<br>e atualiza o estoque
              </div>
            </button>
          </div>
 
          <div class="col-6">
            <button class="decisao-card azul" onclick="abrirModalEntradaSimples()">
              <div class="decisao-icon azul">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2
                       0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="decisao-titulo">Não, só entrada</div>
              <div class="decisao-desc">
                Apenas adiciona quantidade<br>ao estoque existente
              </div>
            </button>
          </div>
 
        </div>
      </div>
 
    </div>
  </div>
</div>
 
 
<!-- ── MODAL 2: Compra de Insumo ─────────────────────── -->
<div class="modal fade sw-modal" id="modal-compra-insumo"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
 
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Registrar Compra de Insumo</h5>
          <p class="modal-subtitle mb-0">
            A operação será lançada como <strong style="color:var(--verde-claro);">COMPRA DE INSUMOS</strong>
            e o estoque será atualizado automaticamente.
          </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
 
      <div class="modal-body">
        <form id="form-compra-insumo" method="POST"
              action="index.php?page=store_compra_insumo">
 
          <input type="hidden" name="tipo_operacao" value="COMPRA DE INSUMOS">
 
          <!-- Linha 1: Produto + Propriedade -->
          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label" for="compra-produto">Produto</label>
              <select class="form-select" id="compra-produto" name="produto_id" required>
                <option value="">Selecione o produto...</option>
                <?php foreach ($produtos ?? [] as $p): ?>
                  <option value="<?= (int)$p['id'] ?>">
                    <?= htmlspecialchars($p['nome']) ?>
                    <?= !empty($p['marca']) ? '— ' . htmlspecialchars($p['marca']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="mt-1">
                <a href="?page=produtos_culturas" class="small"
                   style="color:var(--texto-suave); font-size:11px;">
                  ↗ Não encontrei meu produto — cadastrar agora
                </a>
              </div>
            </div>
            <div class="col-md-5">
              <label class="form-label" for="compra-propriedade">Propriedade de destino</label>
              <select class="form-select" id="compra-propriedade" name="propriedade_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($propriedades ?? [] as $prop): ?>
                  <option value="<?= (int)$prop['id'] ?>">
                    <?= htmlspecialchars($prop['nome']) ?>
                    <?= !empty($prop['municipio']) ? '— ' . htmlspecialchars($prop['municipio']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
 
          <!-- Linha 2: Valor + Quantidade + Valor/dose calculado -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label" for="compra-valor">Valor total da compra (R$)</label>
              <input type="number" class="form-control" id="compra-valor"
                     name="valor_operacao" placeholder="0,00"
                     step="0.01" min="0.01" required
                     oninput="calcularValorPorDose()">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="compra-quantidade">
                Quantidade
                <span class="fw-normal text-muted ms-1" style="font-size:10px;" id="compra-unidade-label"></span>
              </label>
              <input type="number" class="form-control" id="compra-quantidade"
                     name="quantidade" placeholder="0"
                     step="0.001" min="0.001" required
                     oninput="calcularValorPorDose()">
            </div>
            <div class="col-md-4 d-flex flex-column justify-content-end">
              <label class="form-label">Valor por dose calculado</label>
              <div class="dose-preview">
                <span class="dose-preview-label">R$ / unidade</span>
                <span class="dose-preview-value" id="dose-calculada">—</span>
              </div>
            </div>
          </div>
 
          <!-- Linha 3: Safra (opcional) -->
          <div class="mb-3">
            <label class="form-label" for="compra-safra">
              Safra
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional — para atribuir o custo)</span>
            </label>
            <select class="form-select" id="compra-safra" name="safra_id">
              <option value="">Sem vínculo com safra</option>
              <?php foreach ($safras ?? [] as $s): ?>
                <option value="<?= (int)$s['id'] ?>">
                  <?= htmlspecialchars($s['cultura']) ?>
                  — <?= htmlspecialchars($s['nome_talhao']) ?>
                  (<?= date('d/m/Y', strtotime($s['data_inicio'])) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
 
          <!-- Linha 4: Descrição -->
          <div class="mb-0">
            <label class="form-label" for="compra-descricao">
              Descrição
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
            </label>
            <textarea class="form-control" id="compra-descricao" name="descricao"
                      rows="2"
                      placeholder="Ex: Compra de herbicida para safra verão..."></textarea>
          </div>
 
        </form>
      </div>
 
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel"
                data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="form-compra-insumo"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Registrar compra e atualizar estoque
        </button>
      </div>
 
    </div>
  </div>
</div>


<!-- ── MODAL DECISÃO: Saída de Cereal ──────────────────── -->
<div class="modal fade sw-modal" id="modal-decisao-saida-cereal"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Registrar Saída de Cereal</h5>
          <p class="modal-subtitle mb-0">
            Esta saída refere-se a uma venda?
          </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pb-4">
        <div class="row g-3">
          <div class="col-6">
            <button class="decisao-card" onclick="abrirModalVendaCereal()">
              <div class="decisao-icon verde">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2
                       6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0
                       01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="decisao-titulo">Sim, registrar venda</div>
              <div class="decisao-desc">
                Registra valor, descontos<br>e atualiza o silo
              </div>
            </button>
          </div>
          <div class="col-6">
            <button class="decisao-card azul" onclick="abrirModalSaidaSimples()">
              <div class="decisao-icon azul">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1
                       1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0
                       111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                    clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="decisao-titulo">Não, só saída</div>
              <div class="decisao-desc">
                Apenas debita quantidade<br>do silo sem valor financeiro
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ── MODAL: Saída Simples de Cereal ──────────────────── -->
<div class="modal fade sw-modal" id="modal-saida-simples-cereal"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Saída de Estoque — Cereal</h5>
          <p class="modal-subtitle mb-0">Debita quantidade do silo sem registro financeiro.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="form-saida-simples-cereal" method="POST"
              action="index.php?page=saida_simples_cereal">

          <div class="mb-3">
            <label class="form-label">Cereal</label>
            <select class="form-select" name="produto_id" required>
              <option value="">Selecione o cereal...</option>
              <?php foreach ($cereais ?? [] as $c): ?>
                <option value="<?= (int)$c['id'] ?>">
                  <?= htmlspecialchars($c['nome']) ?>
                  <?= !empty($c['unidade_medida'])
                      ? '(' . htmlspecialchars($c['unidade_medida']) . ')'
                      : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Propriedade de origem</label>
            <select class="form-select" name="propriedade_id" required>
              <option value="">Selecione...</option>
              <?php foreach ($propriedades ?? [] as $prop): ?>
                <option value="<?= (int)$prop['id'] ?>">
                  <?= htmlspecialchars($prop['nome']) ?>
                  <?= !empty($prop['municipio'])
                      ? '— ' . htmlspecialchars($prop['municipio'])
                      : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Quantidade a debitar (kg)</label>
            <input type="number" class="form-control" name="quantidade"
                   placeholder="0" step="0.001" min="0.001" required>
          </div>

          <div class="mb-0">
            <label class="form-label">
              Descrição
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
            </label>
            <textarea class="form-control" name="descricao" rows="2"
                      placeholder="Ex: Consumo interno, perda, transferência..."></textarea>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel"
                data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="form-saida-simples-cereal"
                class="btn btn-outline-danger d-flex align-items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <polyline points="19 12 12 19 5 12"/>
          </svg>
          Registrar saída
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── MODAL 3: Venda de Cereal ─────────────────────── -->
<div class="modal fade sw-modal" id="modal-venda-cereal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header" style="background:#1a1a2e;">
        <div>
          <h5 class="modal-title">Registrar Venda de Cereal</h5>
          <p class="modal-subtitle mb-0">
            Operação lançada como <strong style="color:#fca5a5;">VENDA DE CEREAIS</strong>
            — silo atualizado automaticamente.
          </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="form-venda-cereal" method="POST" action="index.php?page=venda_cereal">

          <input type="hidden" name="valor_operacao" id="venda-valor-operacao">

          <!-- Cereal + Propriedade -->
          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label">Cereal vendido</label>
              <select class="form-select" id="venda-produto" name="produto_id" required>
                <option value="">Selecione o cereal...</option>
                <?php foreach ($cereais ?? [] as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"
                          data-nome="<?= htmlspecialchars($c['nome']) ?>">
                    <?= htmlspecialchars($c['nome']) ?>
                    <?= !empty($c['unidade_medida']) ? '(' . htmlspecialchars($c['unidade_medida']) . ')' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label">Propriedade de origem</label>
              <select class="form-select" name="propriedade_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($propriedades ?? [] as $prop): ?>
                  <option value="<?= (int)$prop['id'] ?>">
                    <?= htmlspecialchars($prop['nome']) ?>
                    <?= !empty($prop['municipio']) ? '— ' . htmlspecialchars($prop['municipio']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Quantidade + Valor/saca + Valor bruto -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Quantidade vendida (kg)</label>
              <input type="number" class="form-control" id="venda-qtd"
                     name="quantidade" placeholder="0" step="0.001" min="0.001"
                     required oninput="calcularVenda()">
            </div>
            <div class="col-md-4">
              <label class="form-label">Valor por saca (R$/sc 60 kg)</label>
              <input type="number" class="form-control" id="venda-vl-saca"
                     placeholder="0,00" step="0.01" min="0.01"
                     oninput="calcularVenda()">
            </div>
            <div class="col-md-4">
              <label class="form-label">
                Valor bruto (R$)
                <span class="fw-normal text-muted" style="font-size:10px;">editável</span>
              </label>
              <input type="number" class="form-control" id="venda-vl-bruto"
                     placeholder="0,00" step="0.01" min="0"
                     oninput="aplicarDesconto()">
            </div>
          </div>

          <!-- Desconto -->
          <div class="row g-3 mb-3">
            <div class="col-md-5">
              <label class="form-label">Tipo de desconto</label>
              <select class="form-select" id="venda-desc-tipo"
                      onchange="onTipoDescontoChange()">
                <option value="nenhum">Nenhum</option>
                <option value="percentual">% do total</option>
                <option value="sacas">Quantidade de sacas</option>
              </select>
            </div>
            <div class="col-md-4" id="venda-desc-wrap" style="display:none;">
              <label class="form-label" id="venda-desc-label">Valor</label>
              <input type="number" class="form-control" id="venda-desc-input"
                     placeholder="0" step="0.01" min="0"
                     oninput="aplicarDesconto()">
            </div>
          </div>

          <!-- Resumo financeiro -->
          <div class="mb-3 p-3"
               style="background:#fff5f5;border:1px solid #fecaca;border-radius:12px;">
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span>Total de sacas</span>
              <span id="rv-sacas" class="fw-semibold text-dark">—</span>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span>Valor bruto</span>
              <span id="rv-bruto" class="fw-semibold text-dark">R$ 0,00</span>
            </div>
            <div class="d-flex justify-content-between small mb-1"
                 id="rv-desc-linha" style="display:none !important;">
              <span id="rv-desc-label" class="text-muted">Desconto</span>
              <span id="rv-desc-val" class="fw-semibold text-danger">− R$ 0,00</span>
            </div>
            <hr style="border-color:#fecaca;margin:8px 0;">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold" style="color:#dc2626;">Valor final</span>
              <span id="rv-final"
                    style="font-family:'DM Serif Display',Georgia,serif;
                           font-size:22px;color:#dc2626;">
                R$ 0,00
              </span>
            </div>
          </div>

          <!-- Safra -->
          <div class="mb-3">
            <label class="form-label">
              Safra
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
            </label>
            <select class="form-select" name="safra_id">
              <option value="">Sem vínculo com safra</option>
              <?php foreach ($safras ?? [] as $s): ?>
                <option value="<?= (int)$s['id'] ?>">
                  <?= htmlspecialchars($s['cultura']) ?>
                  — <?= htmlspecialchars($s['nome_talhao']) ?>
                  (<?= date('d/m/Y', strtotime($s['data_inicio'])) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Descrição -->
          <div class="mb-0">
            <label class="form-label">
              Descrição
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">
                (preenchida automaticamente, editável)
              </span>
            </label>
            <textarea class="form-control" id="venda-descricao"
                      name="descricao" rows="2"
                      placeholder="Detalhes da venda..."></textarea>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel"
                data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="form-venda-cereal"
                class="btn btn-danger d-flex align-items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <polyline points="19 12 12 19 5 12"/>
          </svg>
          Registrar venda e atualizar silo
        </button>
      </div>

    </div>
  </div>
</div>
 
 
<!-- ── MODAL 3: Entrada Simples ──────────────────────── -->
<div class="modal fade sw-modal" id="modal-entrada-simples"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
 
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Entrada de Estoque</h5>
          <p class="modal-subtitle mb-0">Adiciona quantidade a um insumo existente.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
 
      <div class="modal-body">
        <form id="form-entrada-simples" method="POST"
              action="index.php?page=store_entrada_insumo">
 
          <div class="mb-3">
            <label class="form-label" for="entrada-insumo">Insumo</label>
            <select class="form-select" id="entrada-insumo" name="insumo_id" required>
              <option value="">Selecione o insumo...</option>
              <?php foreach ($insumos ?? [] as $ins): ?>
                <option value="<?= (int)$ins['id'] ?>">
                  <?= htmlspecialchars($ins['nome']) ?>
                  <?= !empty($ins['marca']) ? '— ' . htmlspecialchars($ins['marca']) : '' ?>
                  (<?= htmlspecialchars($ins['unidade_medida'] ?? '') ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <div class="mt-1">
              <a href="?page=insumos" class="small"
                 style="color:var(--texto-suave); font-size:11px;">
                ↗ Meu insumo não está listado — gerenciar insumos
              </a>
            </div>
          </div>
 
          <div class="mb-3">
            <label class="form-label" for="entrada-propriedade">Propriedade</label>
            <select class="form-select" id="entrada-propriedade"
                    name="propriedade_id" required>
              <option value="">Selecione...</option>
              <?php foreach ($propriedades ?? [] as $prop): ?>
                <option value="<?= (int)$prop['id'] ?>">
                  <?= htmlspecialchars($prop['nome']) ?>
                  <?= !empty($prop['municipio']) ? '— ' . htmlspecialchars($prop['municipio']) : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
 
          <div class="mb-0">
            <label class="form-label" for="entrada-quantidade">Quantidade a adicionar</label>
            <input type="number" class="form-control" id="entrada-quantidade"
                   name="quantidade" placeholder="0" step="0.001" min="0.001" required>
          </div>
 
        </form>
      </div>
 
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel"
                data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="form-entrada-simples"
                class="btn btn-primary d-flex align-items-center gap-2"
                style="background:#3b82f6; border-color:#3b82f6;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="19" x2="12" y2="5"/>
            <polyline points="5 12 12 5 19 12"/>
          </svg>
          Adicionar ao estoque
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
document.querySelectorAll('.toast').forEach(t => {
  const d = parseFloat(getComputedStyle(t).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(t.id), 10);
});

// ── Toggle de visão ────────────────────────────────────────────
function trocarVisao(visao) {
  document.querySelectorAll('.view-section').forEach(s => {
    s.classList.remove('d-block');
    s.classList.add('d-none');
  });
  const secao = document.getElementById('section-' + visao);
  secao.classList.remove('d-none');
  secao.classList.add('d-block');

  document.querySelectorAll('.view-toggle-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('btn-' + visao).classList.add('active');
}

// ── Filtro por texto ────────────────────────────────────────────
// colunas: array com os índices de <td> onde buscar
function filtrarTabela(tabelaId, termo, colunas) {
  const t      = termo.toLowerCase().trim();
  const linhas = document.querySelectorAll(`#${tabelaId} tbody tr[data-busca]`);
  let visiveis = 0;

  linhas.forEach(tr => {
    const texto  = tr.dataset.busca;
    const bate   = !t || texto.includes(t);
    const visivel = bate && verificarSelects(tr);
    tr.style.display = visivel ? '' : 'none';
    if (visivel) visiveis++;
  });

  atualizarContagem(tabelaId, visiveis);
}

// ── Filtro por select ─────────────────────────────────────────
function filtrarPorSelect(tabelaId, valor, colIdx) {
  const linhas = document.querySelectorAll(`#${tabelaId} tbody tr`);
  let visiveis = 0;

  linhas.forEach(tr => {
    const visivel = verificarSelects(tr) && verificarBusca(tr);
    tr.style.display = visivel ? '' : 'none';
    if (visivel) visiveis++;
  });

  atualizarContagem(tabelaId, visiveis);
}

function verificarSelects(tr) {
  const tabelaId = tr.closest('table').id;

  if (tabelaId === 'tabela-insumos') {
    const filtroTipo = document.getElementById('filtro-tipo-insumo')?.value ?? '';
    if (filtroTipo && tr.dataset.tipo !== filtroTipo) return false;
  }

  if (tabelaId === 'tabela-silos') {
    const filtroProp   = document.getElementById('filtro-propriedade-silo')?.value ?? '';
    const filtroEstado = document.getElementById('filtro-estado-silo')?.value ?? '';
    if (filtroProp   && tr.dataset.propriedade !== filtroProp)   return false;
    if (filtroEstado && tr.dataset.estado       !== filtroEstado) return false;
  }

  return true;
}

function verificarBusca(tr) {
  const tabelaId = tr.closest('table').id;

  if (tabelaId === 'tabela-insumos') {
    const termo = document.getElementById('busca-insumo')?.value.toLowerCase().trim() ?? '';
    return !termo || (tr.dataset.busca ?? '').includes(termo);
  }

  if (tabelaId === 'tabela-silos') {
    const termo = document.getElementById('busca-silo')?.value.toLowerCase().trim() ?? '';
    return !termo || (tr.dataset.busca ?? '').includes(termo);
  }

  return true;
}

// ── Limpar filtros ────────────────────────────────────────────
function limparFiltros(visao) {
  if (visao === 'insumos') {
    document.getElementById('busca-insumo').value         = '';
    document.getElementById('filtro-tipo-insumo').value   = '';
  } else {
    document.getElementById('busca-silo').value                 = '';
    document.getElementById('filtro-propriedade-silo').value    = '';
    document.getElementById('filtro-estado-silo').value         = '';
  }

  const tabelaId = visao === 'insumos' ? 'tabela-insumos' : 'tabela-silos';
  document.querySelectorAll(`#${tabelaId} tbody tr`).forEach(tr => tr.style.display = '');
  atualizarContagem(tabelaId, document.querySelectorAll(`#${tabelaId} tbody tr`).length);
}

// ── Contagem de registros ─────────────────────────────────────
function atualizarContagem(tabelaId, n) {
  const label = document.getElementById(
    tabelaId === 'tabela-insumos' ? 'label-insumos' : 'label-silos'
  );
  if (label) label.textContent = `${n} registro${n !== 1 ? 's' : ''} exibido${n !== 1 ? 's' : ''}`;
}

// ── Fluxo de decisão ──────────────────────────────────────────
function abrirModalCompra() {
  bootstrap.Modal.getInstance(
    document.getElementById('modal-decisao-entrada')
  )?.hide();
 
  setTimeout(() => {
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById('modal-compra-insumo')
    ).show();
  }, 300); // aguarda a animação de fechar
}
 
function abrirModalEntradaSimples() {
  bootstrap.Modal.getInstance(
    document.getElementById('modal-decisao-entrada')
  )?.hide();
 
  setTimeout(() => {
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById('modal-entrada-simples')
    ).show();
  }, 300);
}


// ── Decisão saída cereal ──────────────────────────────────────
function abrirModalVendaCereal() {
  bootstrap.Modal.getInstance(
    document.getElementById('modal-decisao-saida-cereal')
  )?.hide();
  setTimeout(() => {
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById('modal-venda-cereal')
    ).show();
  }, 300);
}

function abrirModalSaidaSimples() {
  bootstrap.Modal.getInstance(
    document.getElementById('modal-decisao-saida-cereal')
  )?.hide();
  setTimeout(() => {
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById('modal-saida-simples-cereal')
    ).show();
  }, 300);
}
// Limpa saída simples ao fechar
document.getElementById('modal-saida-simples-cereal')
  ?.addEventListener('hidden.bs.modal', () => {
    document.getElementById('form-saida-simples-cereal')?.reset();
  });
 
// ── Cálculo em tempo real do valor/dose ───────────────────────
function calcularValorPorDose() {
  const valor = parseFloat(document.getElementById('compra-valor').value)     || 0;
  const qtd   = parseFloat(document.getElementById('compra-quantidade').value) || 0;
  const el    = document.getElementById('dose-calculada');
 
  if (valor > 0 && qtd > 0) {
    const dose = valor / qtd;
    el.textContent = dose.toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 4
    });
    el.style.color = 'var(--verde-vivo)';
  } else {
    el.textContent = '—';
    el.style.color = 'var(--texto-suave)';
  }
}

const SACA_KG = 60;
const fmtBRL  = v => 'R$ ' + Number(v).toLocaleString('pt-BR', {
  minimumFractionDigits: 2, maximumFractionDigits: 2
});

function calcularVenda() {
  const qtd  = parseFloat(document.getElementById('venda-qtd').value)     || 0;
  const vsc  = parseFloat(document.getElementById('venda-vl-saca').value) || 0;
  const sacas = qtd / SACA_KG;
  const bruto = sacas * vsc;

  document.getElementById('venda-vl-bruto').value = bruto > 0 ? bruto.toFixed(2) : '';
  aplicarDesconto();
}

function onTipoDescontoChange() {
  const tipo  = document.getElementById('venda-desc-tipo').value;
  const wrap  = document.getElementById('venda-desc-wrap');
  const label = document.getElementById('venda-desc-label');
  const input = document.getElementById('venda-desc-input');

  if (tipo === 'nenhum') {
    wrap.style.display = 'none';
  } else {
    wrap.style.display = '';
    label.textContent  = tipo === 'percentual' ? 'Percentual (%)' : 'Sacas a descontar';
    input.placeholder  = tipo === 'percentual' ? 'Ex: 5' : 'Ex: 3';
    input.step         = tipo === 'percentual' ? '0.1'   : '1';
    input.value        = '';
  }
  aplicarDesconto();
}

function aplicarDesconto() {
  const qtd   = parseFloat(document.getElementById('venda-qtd').value)      || 0;
  const vsc   = parseFloat(document.getElementById('venda-vl-saca').value)  || 0;
  const bruto = parseFloat(document.getElementById('venda-vl-bruto').value) || 0;
  const tipo  = document.getElementById('venda-desc-tipo').value;
  const dVal  = parseFloat(document.getElementById('venda-desc-input')?.value) || 0;
  const sacas = qtd / SACA_KG;

  let desconto = 0;
  let final    = bruto;
  let nota     = 'Sem descontos aplicados';
  let lDesc    = '';

  if (tipo === 'percentual' && dVal > 0) {
    desconto = bruto * (dVal / 100);
    final    = bruto - desconto;
    nota     = `Desconto de ${dVal}% aplicado`;
    lDesc    = `Desconto (${dVal}%)`;
  } else if (tipo === 'sacas' && dVal > 0) {
    const kgDesc = dVal * SACA_KG;
    desconto     = dVal * vsc;
    final        = bruto - desconto;
    nota         = `Desconto de ${dVal} sacas (${kgDesc}kg) do total`;
    lDesc        = `Desconto (${dVal} sacas)`;
  }

  final = Math.max(0, final);

  // Atualiza resumo
  document.getElementById('rv-sacas').textContent =
    sacas > 0 ? sacas.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' sc' : '—';
  document.getElementById('rv-bruto').textContent = fmtBRL(bruto);
  document.getElementById('rv-final').textContent = fmtBRL(final);

  const linhaDesc = document.getElementById('rv-desc-linha');
  if (desconto > 0) {
    linhaDesc.style.removeProperty('display');
    document.getElementById('rv-desc-label').textContent = lDesc;
    document.getElementById('rv-desc-val').textContent   = '− ' + fmtBRL(desconto);
  } else {
    linhaDesc.style.display = 'none';
  }

  // Campo oculto enviado ao controller
  document.getElementById('venda-valor-operacao').value = final.toFixed(2);

  // Atualiza descrição preservando texto do usuário
  const ta   = document.getElementById('venda-descricao');
  const base = (ta.dataset.base || '').trim();
  ta.value   = base ? `${base}\n${nota}` : nota;
}

// Preserva o texto que o usuário digitar além da nota automática
document.getElementById('venda-descricao')?.addEventListener('input', function () {
  const lines = this.value.split('\n');
  const last  = lines[lines.length - 1];
  if (/^(Sem descontos|Desconto de)/i.test(last) && lines.length > 1)
    this.dataset.base = lines.slice(0, -1).join('\n').trim();
  else if (!/^(Sem descontos|Desconto de)/i.test(last))
    this.dataset.base = this.value;
});

// Limpa ao fechar
document.getElementById('modal-venda-cereal')?.addEventListener('hidden.bs.modal', () => {
  document.getElementById('form-venda-cereal')?.reset();
  document.getElementById('venda-vl-bruto').value    = '';
  document.getElementById('venda-valor-operacao').value = '';
  document.getElementById('rv-sacas').textContent    = '—';
  document.getElementById('rv-bruto').textContent    = 'R$ 0,00';
  document.getElementById('rv-final').textContent    = 'R$ 0,00';
  document.getElementById('rv-desc-linha').style.display = 'none';
  document.getElementById('venda-desc-wrap').style.display = 'none';
  const ta = document.getElementById('venda-descricao');
  ta.value = '';
  ta.dataset.base = '';
});

// Limpa o form ao fechar
document.getElementById('modal-venda-cereal')
  ?.addEventListener('hidden.bs.modal', () => {
    document.getElementById('form-venda-cereal')?.reset();
    document.getElementById('venda-preco-kg').textContent = '—';
  });
 
// ── Limpa os formulários quando os modais fecham ──────────────
['modal-compra-insumo', 'modal-entrada-simples'].forEach(id => {
  document.getElementById(id)?.addEventListener('hidden.bs.modal', () => {
    document.getElementById(id).querySelector('form')?.reset();
    const dose = document.getElementById('dose-calculada');
    if (dose) { dose.textContent = '—'; dose.style.color = 'var(--texto-suave)'; }
  });
});

</script>

</body>
</html>
