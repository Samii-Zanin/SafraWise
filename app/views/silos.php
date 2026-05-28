<?php
/*
 * app/views/silos.php
 *
 * Variáveis esperadas:
 *   $silos        — array de SiloController::getAllSilos()
 *   $culturas     — array de CulturaController::getDistintas()
 *   $propriedades — array de PropriedadeController::getAll()
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

$pagina_atual = 'silos';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

// ── Macro cálculos ───────────────────────────────────────────
$totalSilos       = count($silos ?? []);
$capacidadeTotal  = array_sum(array_column($silos ?? [], 'capacidade_kg'));
$quantidadeTotal  = array_sum(array_column($silos ?? [], 'quantidade_kg'));
$ocupacaoMedia    = $capacidadeTotal > 0
    ? round(($quantidadeTotal / $capacidadeTotal) * 100, 1)
    : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Silos</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
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
    .macro-card:hover { box-shadow: 0 4px 20px rgba(15,35,24,.08); }
    .macro-card::after {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      border-radius: 2px 2px 0 0;
    }
    .macro-card.verde::after   { background: var(--verde-vivo); }
    .macro-card.dourado::after { background: var(--dourado); }
    .macro-card.azul::after    { background: #3b82f6; }

    .macro-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .7px;
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

    /* ── Barra de ocupação ── */
    .ocupacao-wrap { min-width: 130px; }
    .ocupacao-bar-bg {
      height: 6px;
      border-radius: 99px;
      background: #e8f0ea;
      margin-top: 5px;
      overflow: hidden;
    }
    .ocupacao-bar-fill {
      height: 100%;
      border-radius: 99px;
      transition: width .4s ease;
    }
    .ocupacao-bar-fill.baixo  { background: var(--verde-vivo); }
    .ocupacao-bar-fill.medio  { background: var(--dourado); }
    .ocupacao-bar-fill.alto   { background: #ef4444; }

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
    .filtro-bar .form-label { margin-bottom: 4px; font-size: 10px; }
    .filtro-bar .form-control,
    .filtro-bar .form-select { font-size: 13px; min-width: 150px; }
    .btn-limpar-filtro {
      background: transparent;
      border: 1.5px solid #dde8e0;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 500;
      color: var(--texto-suave);
      padding: 8px 16px;
      cursor: pointer;
      transition: all .15s;
      white-space: nowrap;
    }
    .btn-limpar-filtro:hover { border-color: var(--verde-vivo); color: var(--verde-vivo); }

    /* ── Tabela ── */
    .silos-table thead th {
      background: #f5f9f6;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .6px;
      color: var(--texto-suave);
      padding: 13px 18px;
      border-bottom: 1px solid #e2ece5;
      white-space: nowrap;
    }
    .silos-table tbody td {
      padding: 13px 18px;
      vertical-align: middle;
      font-size: 13.5px;
      border-bottom: 1px solid #f0f5f1;
    }
    .silos-table tbody tr:last-child td { border-bottom: none; }
    .silos-table tbody tr:hover td     { background: #f7fbf8; }

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
      letter-spacing: .4px;
      white-space: nowrap;
    }
    .table-footer {
      background: #f9fcfa;
      border-top: 1px solid #e2ece5;
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    /* ── Botões de ação na linha ── */
    .btn-row-action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 30px;
      height: 30px;
      border-radius: 8px;
      border: 1px solid transparent;
      background: transparent;
      cursor: pointer;
      transition: all .15s;
      color: var(--texto-suave);
    }
    .btn-row-action:hover.editar  { background: #eef7f1; border-color: #c3e0cc; color: var(--verde-vivo); }
    .btn-row-action:hover.remover { background: #fef2f2; border-color: #fecaca; color: #ef4444; }
  </style>
</head>
<body>

<!-- ════ TOASTS ════ -->
<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= htmlspecialchars($toast['tipo']) ?>" id="toast-main" style="--toast-duration:5s">
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

      <!-- ── Cabeçalho ── -->
      <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
          <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
            Silos
          </h2>
          <p class="text-muted mb-0 small">
            Gerencie os silos de armazenagem vinculados às suas propriedades.
          </p>
        </div>
        <button type="button"
                class="btn btn-success d-flex align-items-center gap-2"
                data-sw-open="modal-cadastrar-silo">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Novo Silo
        </button>
      </div>

      <!-- ── Cards macro ── -->
      <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
          <div class="macro-card verde">
            <div class="macro-label">Total de silos</div>
            <div class="macro-value"><?= $totalSilos ?></div>
            <div class="macro-sub">cadastrados nas propriedades</div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6">
          <div class="macro-card dourado">
            <div class="macro-label">Capacidade total</div>
            <div class="macro-value"><?= number_format($capacidadeTotal, 0, ',', '.') ?> kg</div>
            <div class="macro-sub"><?= number_format($quantidadeTotal, 0, ',', '.') ?> kg armazenados</div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6">
          <div class="macro-card azul">
            <div class="macro-label">Ocupação média</div>
            <div class="macro-value"><?= $ocupacaoMedia ?>%</div>
            <div class="macro-sub">da capacidade total utilizada</div>
          </div>
        </div>
      </div>

      <!-- ── Filtros ── -->
      <div class="filtro-bar mb-3">
        <div>
          <label class="form-label">Buscar silo</label>
          <input type="text" class="form-control form-control-sm"
                 id="busca-silo" placeholder="Nome ou cultura..."
                 oninput="filtrarSilos()">
        </div>
        <div>
          <label class="form-label">Propriedade</label>
          <select class="form-select form-select-sm" id="filtro-propriedade" onchange="filtrarSilos()">
            <option value="">Todas</option>
            <?php
            $props = array_unique(array_column($silos ?? [], 'propriedade'));
            sort($props);
            foreach ($props as $p): ?>
              <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Cultura</label>
          <select class="form-select form-select-sm" id="filtro-cultura" onchange="filtrarSilos()">
            <option value="">Todas</option>
            <?php
            $cults = array_unique(array_column($silos ?? [], 'cultura'));
            sort($cults);
            foreach ($cults as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn-limpar-filtro" onclick="limparFiltrosSilos()">Limpar filtros</button>
        <div class="ms-auto d-flex align-items-center">
          <span class="small text-muted" id="label-contagem"><?= $totalSilos ?> silo<?= $totalSilos !== 1 ? 's' : '' ?> exibido<?= $totalSilos !== 1 ? 's' : '' ?></span>
        </div>
      </div>

      <!-- ── Tabela ── -->
      <div class="card-table-wrapper">
        <div class="table-responsive">
          <table class="table silos-table mb-0" id="tabela-silos">
            <thead>
              <tr>
                <th>Silo</th>
                <th>Propriedade</th>
                <th>Município / Estado</th>
                <th>Cultura</th>
                <th>Capacidade</th>
                <th>Armazenado</th>
                <th>Ocupação</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($silos)): ?>
                <tr>
                  <td colspan="8" class="text-center py-5 text-muted">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5"
                         class="mb-3 d-block mx-auto opacity-50">
                      <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                      <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Nenhum silo cadastrado.<br>
                    <span class="small">Clique em <strong>Novo Silo</strong> para começar.</span>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($silos as $silo):
                    $pct        = min((float)$silo['ocupacao_pct'], 100);
                    $corBarra   = $pct >= 85 ? 'alto' : ($pct >= 50 ? 'medio' : 'baixo');
                ?>
                  <tr data-nome="<?= strtolower(htmlspecialchars($silo['nome'] . ' ' . $silo['cultura'])) ?>"
                      data-propriedade="<?= htmlspecialchars($silo['propriedade'] ?? '') ?>"
                      data-cultura="<?= htmlspecialchars($silo['cultura'] ?? '') ?>">

                    <td class="fw-medium" style="color:var(--texto-escuro);">
                      <?= htmlspecialchars($silo['nome'] ?: '—') ?>
                    </td>

                    <td class="text-muted"><?= htmlspecialchars($silo['propriedade'] ?? '—') ?></td>

                    <td class="text-muted">
                      <?= htmlspecialchars($silo['municipio'] ?? '—') ?>
                      <?php if (!empty($silo['estado'])): ?>
                        <span class="tipo-badge ms-1"
                              style="background:rgba(59,130,246,.1);color:#1d4ed8;">
                          <?= htmlspecialchars($silo['estado']) ?>
                        </span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <span class="tipo-badge"
                            style="background:rgba(64,145,108,.1);color:var(--verde-accent);">
                        <?= htmlspecialchars($silo['cultura'] ?? '—') ?>
                      </span>
                    </td>

                    <td class="text-muted">
                      <?= number_format((float)$silo['capacidade_kg'], 0, ',', '.') ?> kg
                    </td>

                    <td class="td-valor">
                      <?= number_format((float)$silo['quantidade_kg'], 0, ',', '.') ?> kg
                    </td>

                    <td>
                      <div class="ocupacao-wrap">
                        <span class="small fw-semibold" style="color:var(--texto-escuro);"><?= $pct ?>%</span>
                        <div class="ocupacao-bar-bg">
                          <div class="ocupacao-bar-fill <?= $corBarra ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                      </div>
                    </td>

                    <td class="text-center">
                      <div class="d-inline-flex gap-1">
                        <!-- Editar -->
                        <button type="button"
                                class="btn-row-action editar"
                                title="Editar silo"
                                onclick='abrirModalEditar(<?= json_encode([
                                    "id"           => $silo["id"],
                                    "cultura"      => $silo["cultura"],
                                    "capacidade_kg"=> $silo["capacidade_kg"],
                                    "nome"         => $silo["nome"],
                                ]) ?>)'>
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                               stroke="currentColor" stroke-width="2"
                               stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </button>
                        <!-- Remover -->
                        <button type="button"
                                class="btn-row-action remover"
                                title="Remover silo"
                                onclick='abrirModalRemover(<?= (int)$silo["id"] ?>, <?= json_encode($silo["nome"] ?: "este silo") ?>)'>
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                               stroke="currentColor" stroke-width="2"
                               stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/>
                            <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
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
        <div class="table-footer">
          <span class="small text-muted" id="label-tabela">
            <?= $totalSilos ?> silo<?= $totalSilos !== 1 ? 's' : '' ?> exibido<?= $totalSilos !== 1 ? 's' : '' ?>
          </span>
          <span class="small text-muted">Gestão de silos</span>
        </div>
      </div>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->


<!-- ════════════════════════════════════════════
     MODAL: Cadastrar Novo Silo
════════════════════════════════════════════ -->
<div class="modal fade sw-modal"
     id="modal-cadastrar-silo"
     tabindex="-1"
     aria-labelledby="modal-cadastrar-silo-label"
     aria-hidden="true"
     data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modal-cadastrar-silo-label">Novo Silo</h5>
          <p class="modal-subtitle mb-0">Cadastre um silo vinculado a uma propriedade.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-cadastrar-silo" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <form id="form-cadastrar-silo" method="POST" action="index.php?page=store_silo">

          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label" for="cad-nome">Nome / Identificação do silo</label>
              <input type="text" class="form-control" id="cad-nome" name="nome"
                     placeholder="Ex: Silo A, Silo Norte..." required>
            </div>
            <div class="col-md-5">
              <label class="form-label" for="cad-propriedade">Propriedade</label>
              <select class="form-select" id="cad-propriedade" name="propriedade_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($propriedades ?? [] as $prop): ?>
                  <option value="<?= (int)$prop['id'] ?>">
                    <?= htmlspecialchars($prop['nome']) ?>
                    <?php if (!empty($prop['municipio'])): ?>
                      — <?= htmlspecialchars($prop['municipio']) ?>
                    <?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="cad-cultura">Cultura armazenada</label>
              <select class="form-select" id="cad-cultura" name="cultura" required>
                <option value="">Selecione...</option>
                <?php foreach ($culturas ?? [] as $cultura): ?>
                  <option value="<?= htmlspecialchars($cultura['nome']) ?>">
                    <?= htmlspecialchars($cultura['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="cad-capacidade">Capacidade (kg)</label>
              <input type="number" class="form-control" id="cad-capacidade" name="capacidade_kg"
                     placeholder="0" step="0.01" min="1" required>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="cad-quantidade">
                Qtd. atual (kg)
                <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
              </label>
              <input type="number" class="form-control" id="cad-quantidade" name="quantidade_kg"
                     placeholder="0" step="0.01" min="0" value="0">
            </div>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-sw-close="modal-cadastrar-silo">Cancelar</button>
        <button type="submit" form="form-cadastrar-silo"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Salvar silo
        </button>
      </div>

    </div>
  </div>
</div>


<!-- ════════════════════════════════════════════
     MODAL: Editar Silo (cultura + capacidade)
════════════════════════════════════════════ -->
<div class="modal fade sw-modal"
     id="modal-editar-silo"
     tabindex="-1"
     aria-labelledby="modal-editar-silo-label"
     aria-hidden="true" >
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modal-editar-silo-label">Editar Silo</h5>
          <p class="modal-subtitle mb-0" id="editar-silo-subtitulo">Altere a cultura e a capacidade.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <form id="form-editar-silo" method="POST" action="index.php?page=update_silo">
          <input type="hidden" name="id" id="editar-silo-id">

          <div class="mb-3">
            <label class="form-label" for="editar-cultura">Cultura armazenada</label>
            <select class="form-select" id="editar-cultura" name="cultura" required>
              <option value="">Selecione...</option>
              <?php foreach ($culturas ?? [] as $cultura): ?>
                <option value="<?= htmlspecialchars($cultura['nome']) ?>">
                  <?= htmlspecialchars($cultura['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-0">
            <label class="form-label" for="editar-capacidade">Capacidade (kg)</label>
            <input type="number" class="form-control" id="editar-capacidade" name="capacidade_kg"
                   step="0.01" min="1" required>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="form-editar-silo"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Salvar alterações
        </button>
      </div>

    </div>
  </div>
</div>


<!-- ════════════════════════════════════════════
     MODAL: Confirmar Remoção
════════════════════════════════════════════ -->
<div class="modal fade sw-modal"
     id="modal-remover-silo"
     tabindex="-1"
     aria-labelledby="modal-remover-silo-label"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">

      <div class="modal-header" style="border-bottom:1px solid #fee2e2; background:#fff5f5;">
        <div>
          <h5 class="modal-title" id="modal-remover-silo-label" style="color:#dc2626;">Remover Silo</h5>
          <p class="modal-subtitle mb-0" style="color:#ef4444;">Esta ação não pode ser desfeita.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <p class="mb-0 text-muted" style="font-size:14px;">
          Tem certeza que deseja remover o silo <strong id="remover-silo-nome"></strong>?
          Todos os registros de armazenagem deste silo serão excluídos.
        </p>
      </div>

      <div class="modal-footer">
        <form id="form-remover-silo" method="POST" action="index.php?page=delete_silo">
          <input type="hidden" name="id" id="remover-silo-id">
          <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger d-flex align-items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
              <path d="M10 11v6M14 11v6"/>
              <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
            </svg>
            Remover
          </button>
        </form>
      </div>

    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="../../public/js/app.js"></script>

<script>
function closeToast(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('hide');
  setTimeout(() => el.remove(), 320);
}


// ── Toast ──────────────────────────────────────────────────
document.querySelectorAll('.toast').forEach(t => {
  const d = parseFloat(getComputedStyle(t).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(t.id), d);
});

// ── Abrir modal de edição ─────────────────────────────────
function abrirModalEditar(dados) {
  document.getElementById('editar-silo-id').value   = dados.id;
  document.getElementById('editar-capacidade').value = dados.capacidade_kg;
  document.getElementById('editar-silo-subtitulo').textContent =
    dados.nome ? `Editando: ${dados.nome}` : 'Altere a cultura e a capacidade.';

  const sel = document.getElementById('editar-cultura');
  [...sel.options].forEach(o => o.selected = o.value === dados.cultura);

  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modal-editar-silo')
  ).show();
}

// ── Abrir modal de remoção ────────────────────────────────
function abrirModalRemover(id, nome) {
  document.getElementById('remover-silo-id').value        = id;
  document.getElementById('remover-silo-nome').textContent = nome;

  bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modal-remover-silo')
  ).show();
}

// ── Filtro combinado ──────────────────────────────────────
function filtrarSilos() {
  const termo      = document.getElementById('busca-silo').value.toLowerCase().trim();
  const propriedade = document.getElementById('filtro-propriedade').value;
  const cultura     = document.getElementById('filtro-cultura').value;

  const linhas = document.querySelectorAll('#tabela-silos tbody tr[data-nome]');
  let visiveis = 0;

  linhas.forEach(tr => {
    const bateBusca      = !termo      || tr.dataset.nome.includes(termo);
    const batePropriedade = !propriedade || tr.dataset.propriedade === propriedade;
    const bateCultura     = !cultura    || tr.dataset.cultura       === cultura;
    const visivel = bateBusca && batePropriedade && bateCultura;
    tr.style.display = visivel ? '' : 'none';
    if (visivel) visiveis++;
  });

  const label = `${visiveis} silo${visiveis !== 1 ? 's' : ''} exibido${visiveis !== 1 ? 's' : ''}`;
  document.getElementById('label-contagem').textContent = label;
  document.getElementById('label-tabela').textContent   = label;
}

// ── Limpar filtros ────────────────────────────────────────
function limparFiltrosSilos() {
  document.getElementById('busca-silo').value        = '';
  document.getElementById('filtro-propriedade').value = '';
  document.getElementById('filtro-cultura').value     = '';
  filtrarSilos();
}

// ── Validação: quantidade ≤ capacidade no cadastro ────────
document.getElementById('form-cadastrar-silo').addEventListener('submit', function (e) {
  const cap = parseFloat(document.getElementById('cad-capacidade').value) || 0;
  const qtd = parseFloat(document.getElementById('cad-quantidade').value) || 0;
  if (qtd > cap) {
    e.preventDefault();
    alert('A quantidade atual não pode ser maior que a capacidade do silo.');
    document.getElementById('cad-quantidade').focus();
  }
});
</script>

</body>
</html>