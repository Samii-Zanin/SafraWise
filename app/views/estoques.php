<?php
/*
 * app/views/estoques.php
 *
 * Variáveis esperadas:
 *   $estoqueInsumos — array de EstoqueInsumosController::getAll()
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

// ── Macro cálculos — Insumos ─────────────────────────────────
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

// ── Macro cálculos — Silos ───────────────────────────────────
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

    /* ── Seções alternáveis ── */
    .view-section { display: none; }
    .view-section.active { display: block; }

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

      <!-- ── Cabeçalho + Toggle ── -->
      <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
          <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
            Estoques
          </h2>
          <p class="text-muted mb-0 small">
            Visualize o estoque de insumos e a produção armazenada nos silos.
          </p>
        </div>

        <!-- Toggle estilo Power BI -->
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
      <div class="view-section active" id="section-insumos">

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
          <div class="ms-auto small text-muted" id="contagem-insumos">
            <?= $totalInsumos ?> registros
          </div>
        </div>

        <!-- Tabela -->
        <div class="card-table-wrapper">
          <div class="table-responsive">
            <table class="table estoques-table mb-0" id="tabela-insumos">
              <thead>
                <tr>
                  <th>Produto</th>
                  <th>Marca</th>
                  <th>Unidade</th>
                  <th>Categoria</th>
                  <th>Data Referência</th>
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
                      <td class="text-muted">
                        <?= !empty($item['data_referencia'])
                            ? date('d/m/Y', strtotime($item['data_referencia']))
                            : '—' ?>
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
            <span class="small text-muted" id="label-insumos">
              <?= count($estoqueInsumos ?? []) ?> registros exibidos
            </span>
            <span class="small text-muted">Estoque de insumos</span>
          </div>
        </div>

      </div><!-- /section-insumos -->


      <!-- ══════════════════════════════════════
           VISÃO: SILOS
      ══════════════════════════════════════ -->
      <div class="view-section" id="section-silos">

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
              $propriedades = array_unique(array_column($estoquesSilos ?? [], 'propriedade'));
              sort($propriedades);
              foreach ($propriedades as $p): ?>
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
              $estados = array_unique(array_column($estoquesSilos ?? [], 'estado'));
              sort($estados);
              foreach ($estados as $e): ?>
                <option value="<?= htmlspecialchars($e) ?>"><?= htmlspecialchars($e) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn-limpar-filtro" onclick="limparFiltros('silos')">
            Limpar filtros
          </button>
          <div class="ms-auto small text-muted" id="contagem-silos">
            <?= $totalSilos ?> registros
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

      </div><!-- /section-silos -->

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="../../public/js/app.js"></script>

<script>
// ── Toast ──────────────────────────────────────────────────────
document.querySelectorAll('.toast').forEach(t => {
  const d = parseFloat(getComputedStyle(t).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(t.id), d);
});

// ── Toggle de visão ────────────────────────────────────────────
function trocarVisao(visao) {
  // Seções
  document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
  document.getElementById('section-' + visao).classList.add('active');

  // Botões
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
</script>

</body>
</html>
