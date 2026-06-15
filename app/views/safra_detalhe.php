<?php
/*
 * app/views/safra_detalhe.php
 *
 * Variáveis esperadas:
 *   $safra          — array com dados da safra (SafraController::getById())
 *   $operacoes      — array de operações vinculadas (OperacoesFinanceirasController::getBySafra())
 *   $totalGastos    — float: soma das operações do tipo COMPRA DE INSUMOS
 *   $totalInsumos   — float: soma de quantidade de insumos usados
 *   $totalReceita   — float: soma de VENDA DE CEREAIS vinculadas a esta safra
 *   $cereais        — para o modal de venda
 *   $propriedades   — para os modais
 *   $insumos        — para modal de entrada simples
 *   $produtos       — para modal de compra
 *   $safras         — para dropdown safra nos modais (não necessário aqui pois já temos $safra)
 */

$user  = $_SESSION['user'];
$tipo  = $_SESSION['tipo'];

$pagina_atual = 'safras';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

// ── Dados da safra ───────────────────────────────────────────
$safra        = $safra ?? [];
$operacoes    = $operacoes ?? [];
$totalGastos  = (float) ($totalGastos  ?? 0);
$totalInsumos = (float) ($totalInsumos ?? 0);
$totalReceita = (float) ($totalReceita ?? 0);
$lucro        = $totalReceita - $totalGastos;

$ativa    = empty($safra['data_fim']) || strtotime($safra['data_fim']) >= time();
$cultura  = $safra['cultura'] ?? 'Safra';

// Cor da cultura para o cabeçalho
function culturaEstiloDetalhe(string $nome): array {
    $n = mb_strtolower($nome);
    $n = strtr($n, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
                     'ã'=>'a','â'=>'a','ê'=>'e','ô'=>'o','ç'=>'c']);

    if (str_contains($n, 'soja'))     return ['bg'=>'#713f12','acento'=>'#eab308','claro'=>'#fef9c3'];
    if (str_contains($n, 'milho'))    return ['bg'=>'#7c2d12','acento'=>'#f97316','claro'=>'#fff7ed'];
    if (str_contains($n, 'feij'))     return ['bg'=>'#1c1917','acento'=>'#a8a29e','claro'=>'#292524'];
    if (str_contains($n, 'canola'))   return ['bg'=>'#3f2005','acento'=>'#ca8a04','claro'=>'#fefce8'];
    if (str_contains($n, 'cafe'))     return ['bg'=>'#2c1204','acento'=>'#ea580c','claro'=>'#3c1a0a'];
    if (str_contains($n, 'trigo'))    return ['bg'=>'#78350f','acento'=>'#d97706','claro'=>'#fffbeb'];
    if (str_contains($n, 'arroz'))    return ['bg'=>'#14532d','acento'=>'#22c55e','claro'=>'#f0fdf4'];
    if (str_contains($n, 'algod'))    return ['bg'=>'#1e293b','acento'=>'#94a3b8','claro'=>'#f8fafc'];
    if (str_contains($n, 'sorgo'))    return ['bg'=>'#4a044e','acento'=>'#c026d3','claro'=>'#fdf4ff'];
    if (str_contains($n, 'cana'))     return ['bg'=>'#14532d','acento'=>'#16a34a','claro'=>'#f0fdf4'];

    return ['bg'=>'#0f2318','acento'=>'#40916c','claro'=>'#f0faf4'];
}

$cor = culturaEstiloDetalhe($cultura);

// ── Tipo de operação → visual ────────────────────────────────
function tipoOperacaoVisual(string $tipo): array {
    return match(true) {
        str_contains($tipo, 'COMPRA')      => ['cor'=>'#16a34a','bg'=>'rgba(22,163,74,.1)','icone'=>'compra','label'=>'Compra'],
        str_contains($tipo, 'VENDA')       => ['cor'=>'#2563eb','bg'=>'rgba(37,99,235,.1)', 'icone'=>'venda', 'label'=>'Venda'],
        str_contains($tipo, 'SAÍDA')       => ['cor'=>'#dc2626','bg'=>'rgba(220,38,38,.1)', 'icone'=>'saida', 'label'=>'Saída'],
        str_contains($tipo, 'PULVERIZA')   => ['cor'=>'#0891b2','bg'=>'rgba(8,145,178,.1)', 'icone'=>'agro',  'label'=>'Pulverização'],
        str_contains($tipo, 'PLANTIO')     => ['cor'=>'#65a30d','bg'=>'rgba(101,163,13,.1)','icone'=>'agro',  'label'=>'Plantio'],
        str_contains($tipo, 'COLHEITA')    => ['cor'=>'#d97706','bg'=>'rgba(217,119,6,.1)', 'icone'=>'agro',  'label'=>'Colheita'],
        str_contains($tipo, 'ADUBA')       => ['cor'=>'#7c3aed','bg'=>'rgba(124,58,237,.1)','icone'=>'agro',  'label'=>'Adubação'],
        default                             => ['cor'=>'#6b7280','bg'=>'rgba(107,114,128,.1)','icone'=>'outro','label'=>$tipo],
    };
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — <?= htmlspecialchars($cultura) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
    /* ── Hero do detalhe ── */
    .safra-hero {
      background: <?= $cor['bg'] ?>;
      border-radius: var(--bs-border-radius-lg);
      padding: 28px 32px;
      margin-bottom: 28px;
      position: relative;
      overflow: hidden;
    }

    .safra-hero::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(255,255,255,.04);
    }

    .safra-hero-cultura {
      font-family: 'DM Serif Display', Georgia, serif;
      font-size: 36px;
      color: #fff;
      line-height: 1.1;
      margin-bottom: 4px;
    }

    .safra-hero-sub {
      font-size: 13px;
      color: rgba(255,255,255,.6);
    }

    .safra-hero-badges {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 12px;
    }

    .hero-badge {
      font-size: 11px;
      font-weight: 600;
      padding: 4px 12px;
      border-radius: 20px;
      border: 1px solid rgba(255,255,255,.2);
      color: rgba(255,255,255,.85);
      background: rgba(255,255,255,.08);
      white-space: nowrap;
    }

    .hero-badge.destaque {
      background: <?= $cor['acento'] ?>;
      border-color: <?= $cor['acento'] ?>;
      color: #fff;
    }

    /* ── Macro cards do detalhe ── */
    .detalhe-macro {
      background: var(--branco);
      border: 1px solid #e2ece5;
      border-radius: var(--bs-border-radius-lg);
      padding: 20px 24px;
      position: relative;
      overflow: hidden;
    }

    .detalhe-macro::after {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      border-radius: 2px 2px 0 0;
    }

    .detalhe-macro.vermelho::after { background: #ef4444; }
    .detalhe-macro.verde::after    { background: var(--verde-vivo); }
    .detalhe-macro.azul::after     { background: #3b82f6; }
    .detalhe-macro.dourado::after  { background: var(--dourado); }

    .detalhe-macro-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .7px;
      color: var(--texto-suave);
      margin-bottom: 6px;
    }

    .detalhe-macro-value {
      font-family: 'DM Serif Display', Georgia, serif;
      font-size: 26px;
      color: var(--texto-escuro);
      line-height: 1.1;
    }

    .detalhe-macro-sub {
      font-size: 11px;
      color: var(--texto-suave);
      margin-top: 4px;
    }

    /* ── Timeline ── */
    .timeline-wrap {
      position: relative;
      padding-left: 36px;
    }

    .timeline-wrap::before {
      content: '';
      position: absolute;
      left: 14px; top: 0; bottom: 0;
      width: 2px;
      background: #e2ece5;
      border-radius: 2px;
    }

    .timeline-item {
      position: relative;
      margin-bottom: 18px;
    }

    .timeline-dot {
      position: absolute;
      left: -29px;
      top: 16px;
      width: 16px; height: 16px;
      border-radius: 50%;
      border: 2.5px solid;
      background: var(--branco);
      z-index: 1;
    }

    .timeline-card {
      background: var(--branco);
      border: 1px solid #e2ece5;
      border-radius: var(--bs-border-radius-lg);
      padding: 16px 20px;
      transition: box-shadow .15s;
    }

    .timeline-card:hover { box-shadow: 0 4px 16px rgba(15,35,24,.07); }

    .timeline-data {
      font-size: 11px;
      font-weight: 700;
      color: var(--texto-suave);
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-bottom: 6px;
    }

    .timeline-tipo {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: 8px;
    }

    .timeline-desc {
      font-size: 13.5px;
      color: var(--texto-escuro);
      margin-bottom: 4px;
    }

    .timeline-valores {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
    }

    .timeline-valor-item {
      font-size: 12px;
      color: var(--texto-suave);
    }

    .timeline-valor-item strong {
      color: var(--texto-escuro);
      font-weight: 600;
    }

    /* ── Botões de ação ── */
    .btn-acao {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 18px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      border: 1.5px solid;
      cursor: pointer;
      transition: all .15s;
      text-decoration: none;
    }

    .btn-acao.verde {
      background: var(--verde-vivo);
      border-color: var(--verde-vivo);
      color: #fff;
    }

    .btn-acao.verde:hover {
      background: var(--verde-accent);
      border-color: var(--verde-accent);
    }

    .btn-acao.outline {
      background: transparent;
      border-color: #dde8e0;
      color: var(--texto-medio);
    }

    .btn-acao.outline:hover {
      border-color: var(--verde-vivo);
      color: var(--verde-vivo);
    }

    .btn-acao.perigo {
      background: transparent;
      border-color: #fecaca;
      color: #dc2626;
    }

    .btn-acao.perigo:hover {
      background: #fef2f2;
    }

    /* ── Empty timeline ── */
    .timeline-empty {
      text-align: center;
      padding: 48px 24px;
      color: var(--texto-suave);
    }

    .timeline-empty svg { opacity: .3; margin-bottom: 14px; }

    /* ── Breadcrumb ── */
    .sw-breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--texto-suave);
      margin-bottom: 20px;
    }

    .sw-breadcrumb a {
      color: var(--verde-vivo);
      text-decoration: none;
      font-weight: 500;
    }

    .sw-breadcrumb a:hover { text-decoration: underline; }

    .sw-breadcrumb-sep { opacity: .4; }

    /* ── Dose preview (modal compra) ── */
    .dose-preview {
      background: #f0faf4; border: 1px solid #c3e0cc;
      border-radius: 10px; padding: 10px 14px;
      display: flex; align-items: center;
      justify-content: space-between; gap: 8px;
    }
    .dose-preview-label { font-size: 11px; font-weight: 700; text-transform: uppercase;
                          letter-spacing: .6px; color: var(--texto-suave); }
    .dose-preview-value { font-family: 'DM Serif Display', Georgia, serif;
                          font-size: 18px; color: var(--verde-vivo); }

    /* ── Decisao cards ── */
    .decisao-card {
      display: flex; flex-direction: column; align-items: center; gap: 14px;
      padding: 28px 16px; border: 2px solid #e2ece5; border-radius: 16px;
      background: var(--branco); cursor: pointer; transition: all 0.2s ease;
      text-align: center; width: 100%;
    }
    .decisao-card:hover { border-color: var(--verde-vivo); background: #f0faf4;
      transform: translateY(-2px); box-shadow: 0 6px 20px rgba(64,145,108,.15); }
    .decisao-card.azul:hover { border-color: #3b82f6; background: #eff6ff;
      box-shadow: 0 6px 20px rgba(59,130,246,.15); }
    .decisao-icon { width: 52px; height: 52px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center; }
    .decisao-icon svg { width: 26px; height: 26px; }
    .decisao-icon.verde { background: rgba(64,145,108,.12); color: var(--verde-vivo); }
    .decisao-icon.azul  { background: rgba(59,130,246,.10); color: #3b82f6; }
    .decisao-titulo { font-family: 'DM Serif Display', Georgia, serif;
      font-size: 15px; color: var(--texto-escuro); line-height: 1.2; }
    .decisao-desc { font-size: 12px; color: var(--texto-suave); line-height: 1.4; }
  </style>
</head>
<body>

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

      <!-- ── Breadcrumb ── -->
      <div class="sw-breadcrumb">
        <a href="?page=safras">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
          </svg>
          Safras
        </a>
        <span class="sw-breadcrumb-sep">/</span>
        <span><?= htmlspecialchars($cultura) ?></span>
        <?php if (!empty($safra['nome_talhao'])): ?>
          <span class="sw-breadcrumb-sep">/</span>
          <span><?= htmlspecialchars($safra['nome_talhao']) ?></span>
        <?php endif; ?>
      </div>

      <!-- ── Hero ── -->
      <div class="safra-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
          <div>
            <div class="safra-hero-cultura"><?= htmlspecialchars($cultura) ?></div>
            <div class="safra-hero-sub">
              <?= htmlspecialchars($safra['variedade'] ?? '') ?>
            </div>
            <div class="safra-hero-badges mt-3">
              <span class="hero-badge destaque">
                <?= $ativa ? '● Em andamento' : '✓ Encerrada' ?>
              </span>
              <?php if (!empty($safra['nome_propriedade'])): ?>
                <span class="hero-badge">
                  <?= htmlspecialchars($safra['nome_propriedade']) ?>
                </span>
              <?php endif; ?>
              <?php if (!empty($safra['nome_talhao'])): ?>
                <span class="hero-badge">
                  Talhão: <?= htmlspecialchars($safra['nome_talhao']) ?>
                </span>
              <?php endif; ?>
              <?php if (!empty($safra['area_talhao'])): ?>
                <span class="hero-badge">
                  <?= number_format((float)$safra['area_talhao'], 2, ',', '.') ?> ha
                </span>
              <?php endif; ?>
              <?php if (!empty($safra['data_inicio'])): ?>
                <span class="hero-badge">
                  Início: <?= date('d/m/Y', strtotime($safra['data_inicio'])) ?>
                </span>
              <?php endif; ?>
              <?php if (!empty($safra['data_fim'])): ?>
                <span class="hero-badge">
                  Fim: <?= date('d/m/Y', strtotime($safra['data_fim'])) ?>
                </span>
              <?php endif; ?>
              <?php if (!empty($safra['municipio'])): ?>
                <span class="hero-badge">
                  <?= htmlspecialchars($safra['municipio']) ?>
                  <?= !empty($safra['estado']) ? '/' . htmlspecialchars($safra['estado']) : '' ?>
                </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Botões de ação -->
          <div class="d-flex gap-2 flex-wrap">
            <button type="button"
                    class="btn-acao verde"
                    data-sw-open="modal-nova-op-financeira">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2.5"
                   stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
              </svg>
              Operação Financeira
            </button>
            <button type="button"
                    class="btn-acao outline bg-light"
                    data-sw-open="modal-nova-op-agricola">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2.5"
                   stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                <path d="M12 8v4l3 3"/>
              </svg>
              Operação Agrícola
            </button>
            <?php if ($ativa): ?>
            <button type="button"
                    class="btn-acao perigo bg-light"
                    data-sw-open="modal-encerrar-safra">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/>
                  <rect x="9" y="9" width="6" height="6"/>
                </svg>
              Encerrar Safra
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- ── Macro cards ── -->
      <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
          <div class="detalhe-macro vermelho">
            <div class="detalhe-macro-label">Gastos agrícolas</div>
            <div class="detalhe-macro-value">
              R$ <?= number_format($totalGastos, 2, ',', '.') ?>
            </div>
            <div class="detalhe-macro-sub">em compras de insumos</div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="detalhe-macro azul">
            <div class="detalhe-macro-label">Insumos utilizados</div>
            <div class="detalhe-macro-value">
              <?= number_format($totalInsumos, 2, ',', '.') ?>
            </div>
            <div class="detalhe-macro-sub">unidades consumidas</div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="detalhe-macro verde">
            <div class="detalhe-macro-label">Receita (vendas)</div>
            <div class="detalhe-macro-value">
              R$ <?= number_format($totalReceita, 2, ',', '.') ?>
            </div>
            <div class="detalhe-macro-sub">total de vendas de cereais</div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="detalhe-macro <?= $lucro >= 0 ? 'verde' : 'vermelho' ?>">
            <div class="detalhe-macro-label">Resultado</div>
            <div class="detalhe-macro-value"
                 style="color:<?= $lucro >= 0 ? 'var(--verde-vivo)' : '#dc2626' ?>;">
              <?= $lucro >= 0 ? '+' : '' ?>R$ <?= number_format(abs($lucro), 2, ',', '.') ?>
            </div>
            <div class="detalhe-macro-sub">receita − gastos</div>
          </div>
        </div>
      </div>

      <!-- ── Timeline ── -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"
            style="font-family:'DM Serif Display',Georgia,serif;color:var(--texto-escuro);">
          Linha do tempo
        </h5>
        <span class="small text-muted">
          <?= count($operacoes) ?> operaç<?= count($operacoes) !== 1 ? 'ões' : 'ão' ?> registrada<?= count($operacoes) !== 1 ? 's' : '' ?>
        </span>
      </div>

      <?php if (empty($operacoes)): ?>
        <div class="card-table-wrapper">
          <div class="timeline-empty">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1"
                 class="d-block mx-auto">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <h6 style="font-family:'DM Serif Display',Georgia,serif;margin-top:14px;">
              Nenhuma operação registrada ainda
            </h6>
            <p class="small mb-3 text-muted">
              Registre operações financeiras ou agrícolas vinculadas a esta safra.
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
              <button class="btn btn-success btn-sm"
                      data-sw-open="modal-nova-op-financeira">
                + Operação financeira
              </button>
              <button class="btn btn-outline-secondary btn-sm"
                      data-sw-open="modal-nova-op-agricola">
                + Operação agrícola
              </button>
            </div>
          </div>
        </div>

      <?php else: ?>
        <div class="timeline-wrap">
          <?php foreach ($operacoes as $op):
            $visual  = tipoOperacaoVisual($op['tipo_operacao'] ?? '');
            $dataOp  = !empty($op['data_operacao'])
                ? date('d/m/Y', strtotime($op['data_operacao']))
                : '—';
            $ehAgro  = ($op['operacao'] ?? '') === 'Operação Agrícola';
            $valor   = (float)($op['valor_operacao'] ?? 0);
            $qtd     = (float)($op['quantidade']     ?? 0);
          ?>
            <div class="timeline-item">
              <div class="timeline-dot"
                  style="border-color:<?= $visual['cor'] ?>;
                          background:<?= $visual['bg'] ?>;"></div>

              <div class="timeline-card">

                <!-- Cabeçalho: data + categoria -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="timeline-data mb-0"><?= $dataOp ?></span>
                  <span style="font-size:10px;font-weight:600;padding:2px 8px;
                              border-radius:20px;
                              background:<?= $ehAgro ? 'rgba(217,119,6,.1)' : 'rgba(37,99,235,.08)' ?>;
                              color:<?= $ehAgro ? '#b45309' : '#1d4ed8' ?>;">
                    <?= $ehAgro ? '🌱 Agrícola' : '💰 Financeira' ?>
                  </span>
                </div>

                <!-- Tipo da operação -->
                <span class="timeline-tipo"
                      style="background:<?= $visual['bg'] ?>;color:<?= $visual['cor'] ?>;">
                  <?= htmlspecialchars($visual['label']) ?>
                </span>

                <!-- Produto / insumo -->
                <?php if (!empty($op['produto_nome'])): ?>
                  <div class="timeline-desc" style="font-size:13px;">
                    <?= htmlspecialchars($op['produto_nome']) ?>
                  </div>
                <?php endif; ?>

                <!-- Descrição (se diferente do produto) -->
                <?php if (!empty($op['descricao'])): ?>
                  <div class="small text-muted mb-2"
                      style="font-size:12px;line-height:1.4;">
                    <?= htmlspecialchars($op['descricao']) ?>
                  </div>
                <?php endif; ?>

                <!-- Valores em linha -->
                <div class="timeline-valores">
                  <?php if ($qtd > 0): ?>
                    <div class="timeline-valor-item">
                      Qtd: <strong>
                        <?= number_format($qtd, 2, ',', '.') ?>
                        <?= !empty($op['tipo_produto']) ? htmlspecialchars($op['tipo_produto']) : '' ?>
                      </strong>
                    </div>
                  <?php endif; ?>

                  <?php if ($valor > 0): ?>
                    <div class="timeline-valor-item">
                      <?= $ehAgro ? 'Custo' : 'Valor' ?>:
                      <strong style="color:<?= str_contains($op['tipo_operacao'] ?? '', 'VENDA')
                          ? 'var(--verde-vivo)' : '#dc2626' ?>;">
                        <?= str_contains($op['tipo_operacao'] ?? '', 'VENDA') ? '+' : '−' ?>
                        R$ <?= number_format($valor, 2, ',', '.') ?>
                      </strong>
                    </div>
                  <?php endif; ?>
                </div>

              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->


<!-- ════════════════════════════════════
     MODAL: Encerrar Safra
════════════════════════════════════ -->
<div class="modal fade sw-modal" id="modal-encerrar-safra"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header" style="background:#3d1515;">
        <div>
          <h5 class="modal-title">Encerrar Safra</h5>
          <p class="modal-subtitle mb-0">Esta ação define a data de fim para hoje.</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-encerrar-safra"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0" style="font-size:14px;color:var(--texto-escuro);">
          Tem certeza que deseja encerrar a safra de
          <strong><?= htmlspecialchars($cultura) ?></strong>
          <?= !empty($safra['nome_talhao']) ? 'no talhão <strong>' . htmlspecialchars($safra['nome_talhao']) . '</strong>' : '' ?>?
        </p>
        <p class="small text-muted mt-2 mb-0">
          A data de fim será definida como <strong><?= date('d/m/Y') ?></strong>.
        </p>
      </div>
      <div class="modal-footer">
        <form method="POST" action="index.php?page=encerrar_safra">
          <input type="hidden" name="safra_id" value="<?= (int)($safra['id'] ?? 0) ?>">
          <button type="button" class="btn-modal-cancel" data-sw-close="modal-encerrar-safra">
            Cancelar
          </button>
          <button type="submit"
                  class="btn btn-danger d-flex align-items-center gap-2">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <rect x="9" y="9" width="6" height="6"/>
            </svg>
            Encerrar agora
          </button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- ════════════════════════════════════
     MODAL: Nova Operação Financeira
     (mesmos modais de decisão de estoques.php — reutilizar via include ou copiar)
     O safra_id já vem pré-selecionado com o id da safra atual
════════════════════════════════════ -->
<div class="modal fade sw-modal" id="modal-nova-op-financeira"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Nova Operação Financeira</h5>
          <p class="modal-subtitle mb-0">Qual tipo de operação deseja registrar?</p>
        </div>
        <button type="button" class="btn-close" data-sw-close="modal-nova-op-financeira"></button>
      </div>
      <div class="modal-body pb-4">
        <div class="row g-3">
          <div class="col-6">
            <button class="decisao-card" onclick="abrirModalCompraDetalhe()">
              <div class="decisao-icon verde">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004
                       18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0
                       00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0
                       01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="decisao-titulo">Compra de insumos</div>
              <div class="decisao-desc">Registra compra<br>e atualiza estoque</div>
            </button>
          </div>
          <div class="col-6">
            <button class="decisao-card azul" onclick="abrirModalVendaDetalhe()">
              <div class="decisao-icon azul">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2
                       6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0
                       01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="decisao-titulo">Venda de cereal</div>
              <div class="decisao-desc">Registra venda<br>e atualiza silo</div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ════════════════════════════════════
     MODAL: Nova Operação Agrícola
     (placeholder — será desenvolvida em etapa futura)
════════════════════════════════════ -->
<div class="modal fade sw-modal" id="modal-nova-op-agricola"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title">Nova Operação Agrícola</h5>
          <p class="modal-subtitle mb-0">
            Registre uma atividade de campo vinculada a esta safra.
          </p>
        </div>
        <button type="button" class="btn-close"
                data-sw-close="modal-nova-op-agricola"></button>
      </div>

      <div class="modal-body">
        <form id="form-op-agricola" method="POST"
              action="index.php?page=store_op_agricola">

          <input type="hidden" name="safra_id"
                 value="<?= (int)($safra['id'] ?? 0) ?>">
          <input type="hidden" name="custos_operacao"
                 id="op-custo-operacao" value="0">
                    
          <input type="hidden" name="talhao_id"
       value="<?= (int)($safra['talhao_id'] ?? 0) ?>">

<!-- Linha 1: Tipo + Data -->
<div class="row g-3 mb-3">
  <div class="col-md-6">
    <label class="form-label">Tipo de operação</label>
    <select class="form-select" name="tipo_operacao" id="op-tipo" required>
      <option value="">Selecione...</option>
      <option value="PLANTIO">Plantio</option>
      <option value="PULVERIZAÇÃO">Pulverização</option>
      <option value="ADUBAÇÃO">Adubação</option>
      <option value="COLHEITA">Colheita</option>
      <option value="IRRIGAÇÃO">Irrigação</option>
      <option value="CALAGEM">Calagem</option>
      <option value="OUTRO">Outro</option>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Data da operação</label>
    <input type="date" class="form-control" name="data_operacao"
           id="op-data" value="<?= date('Y-m-d') ?>" required>
  </div>
</div>

          <?php if (!empty($safra['nome_talhao'])): ?>
            <input type="hidden" name="talhao_id"
              value="<?= (int)($safra['talhao_id'] ?? 0) ?>">  
            <div class="mb-3 d-flex align-items-center gap-2"
                style="background:#f5f9f6;border:1px solid #c3e0cc;
                        border-radius:10px;padding:10px 14px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="var(--verde-vivo)" stroke-width="2.5"
                  stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              <span class="small" style="color:var(--verde-accent);">
                Talhão vinculado:
                <strong><?= htmlspecialchars($safra['nome_talhao']) ?></strong>
                <?= !empty($safra['area_talhao'])
                    ? '(' . number_format((float)$safra['area_talhao'], 2, ',', '.') . ' ha)'
                    : '' ?>
              </span>
            </div>
          <?php endif; ?>

          <?php if ($tipo === 'peao'): ?>
            <input type="hidden" name="peao_id" value="<?= (int)$user['id'] ?>">
            <div class="mb-3 d-flex align-items-center gap-2"
                style="background:#eff6ff;border:1px solid #bfdbfe;
                        border-radius:10px;padding:10px 14px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="#2563eb" stroke-width="2.5"
                  stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <span class="small" style="color:#1d4ed8;">
                Operação atribuída a
                <strong><?= htmlspecialchars($user['nome']) ?></strong>
              </span>
            </div>
          <?php else: ?>
            <div class="mb-3">
              <label class="form-label">
                Peão responsável
                <span class="fw-normal text-muted ms-1"
                      style="font-size:11px;">(opcional)</span>
              </label>
              <select class="form-select" name="peao_id" id="op-peao">
                <option value="">Sem responsável definido</option>
                <?php foreach ($peoes ?? [] as $p): ?>
                  <option value="<?= (int)$p['id'] ?>">
                    <?= htmlspecialchars($p['nome']) ?>
                    <?= !empty($p['funcao']) ? '— ' . htmlspecialchars($p['funcao']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label">
                Insumo utilizado
                <span class="fw-normal text-muted ms-1"
                      style="font-size:11px;">(opcional)</span>
              </label>
              <select class="form-select" name="insumo_id" id="op-insumo"
                      onchange="calcularCustoOperacao()">
                <option value="" data-valor-dose="0">Nenhum insumo</option>
                <?php foreach ($insumosAgricolas ?? [] as $ins): ?>
                <option value="<?= (int)($ins['id'] ?? 0) ?>"
                        data-valor-dose="<?= number_format((float)($ins['valor_por_dose'] ?? 0), 4, '.', '') ?>"
                        data-unidade="<?= htmlspecialchars($ins['unidade_medida'] ?? '') ?>">
                  <?= htmlspecialchars($ins['nome'] ?? '') ?>
                  <?= !empty($ins['marca']) ? '— ' . htmlspecialchars($ins['marca']) : '' ?>
                  (R$ <?= number_format((float)($ins['valor_por_dose'] ?? 0), 2, ',', '.') ?>
                  /<?= htmlspecialchars($ins['unidade_medida'] ?? 'un') ?>)
                </option>
              <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label">
                Quantidade
                <span class="fw-normal text-muted ms-1"
                      style="font-size:10px;" id="op-unidade-label"></span>
              </label>
              <input type="number" class="form-control" name="quantidade_insumo"
                     id="op-quantidade" placeholder="0"
                     step="0.001" min="0" value="0"
                     oninput="calcularCustoOperacao()">
            </div>
          </div>

          <!-- Cálculo de custo -->
          <div class="mb-3 p-3"
               style="background:#f5f9f6;border:1px solid #c3e0cc;border-radius:12px;">
            <div class="fw-semibold mb-2" style="font-size:12px;
                 text-transform:uppercase;letter-spacing:.5px;color:var(--texto-suave);">
              Custo da operação
            </div>

            <div class="d-flex justify-content-between small text-muted mb-2">
              <span>Custo do insumo (qtd × valor/dose)</span>
              <span id="op-custo-base" class="fw-semibold"
                    style="color:var(--texto-escuro);">R$ 0,00</span>
            </div>

            <div class="row g-2 mb-2 align-items-center">
              <div class="col">
                <label class="form-label mb-1" style="font-size:11px;">
                  Custos extras (mão de obra, combustível, etc.)
                </label>
                <input type="number" class="form-control form-control-sm"
                       id="op-custo-extra" placeholder="0,00"
                       step="0.01" min="0" value="0"
                       oninput="calcularCustoOperacao()">
              </div>
            </div>

            <div style="border-top:1px solid #c3e0cc;margin:10px 0 8px;"></div>

            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold" style="color:var(--verde-accent);">
                Total da operação
              </span>
              <span id="op-custo-total"
                    style="font-family:'DM Serif Display',Georgia,serif;
                           font-size:20px;color:var(--verde-vivo);">
                R$ 0,00
              </span>
            </div>
          </div>

          <!-- Descrição -->
          <div class="mb-0">
            <label class="form-label">
              Descrição
              <span class="fw-normal text-muted ms-1"
                    style="font-size:11px;">(opcional)</span>
            </label>
            <textarea class="form-control" name="descricao" id="op-descricao"
                      rows="2"
                      placeholder="Detalhes da operação..."></textarea>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel"
                data-sw-close="modal-nova-op-agricola">
          Cancelar
        </button>
        <button type="submit" form="form-op-agricola"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Registrar operação
        </button>
      </div>

    </div>
  </div>
</div>


<!-- ════════════════════════════════════
     MODAL: Compra de Insumo
════════════════════════════════════ -->
<div class="modal fade sw-modal" id="modal-compra-insumo"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title">Registrar Compra de Insumo</h5>
          <p class="modal-subtitle mb-0">
            A operação será lançada como
            <strong style="color:var(--verde-claro);">COMPRA DE INSUMOS</strong>
            e o estoque será atualizado automaticamente.
          </p>
        </div>
        <button type="button" class="btn-close"
                data-sw-close="modal-compra-insumo"></button>
      </div>

      <div class="modal-body">
        <form id="form-compra-insumo" method="POST"
              action="index.php?page=store_compra_insumo">

          <input type="hidden" name="tipo_operacao" value="COMPRA DE INSUMOS">
          <input type="hidden" name="safra_id"
                 value="<?= (int)($safra['id'] ?? 0) ?>">

          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label" for="compra-produto">Produto</label>
              <select class="form-select" id="compra-produto"
                      name="produto_id" required>
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
                   style="color:var(--texto-suave);font-size:11px;">
                  ↗ Não encontrei meu produto — cadastrar agora
                </a>
              </div>
            </div>
            <div class="col-md-5">
              <label class="form-label" for="compra-propriedade">
                Propriedade de destino
              </label>
              <select class="form-select" id="compra-propriedade"
                      name="propriedade_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($propriedades ?? [] as $prop): ?>
                  <option value="<?= (int)$prop['id'] ?>">
                    <?= htmlspecialchars($prop['nome']) ?>
                    <?= !empty($prop['municipio'])
                        ? '— ' . htmlspecialchars($prop['municipio']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label" for="compra-valor">
                Valor total da compra (R$)
              </label>
              <input type="number" class="form-control" id="compra-valor"
                     name="valor_operacao" placeholder="0,00"
                     step="0.01" min="0.01" required
                     oninput="calcularValorPorDose()">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="compra-quantidade">
                Quantidade
                <span class="fw-normal text-muted ms-1"
                      style="font-size:10px;"
                      id="compra-unidade-label"></span>
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

          <div class="mb-0">
            <label class="form-label" for="compra-descricao">
              Descrição
              <span class="fw-normal text-muted ms-1"
                    style="font-size:11px;">(opcional)</span>
            </label>
            <textarea class="form-control" id="compra-descricao"
                      name="descricao" rows="2"
                      placeholder="Ex: Compra de herbicida para safra verão...">
            </textarea>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel"
                data-sw-close="modal-compra-insumo">Cancelar</button>
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


<!-- ════════════════════════════════════
     MODAL: Venda de Cereal
════════════════════════════════════ -->
<div class="modal fade sw-modal" id="modal-venda-cereal"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header" style="background:#1a1a2e;">
        <div>
          <h5 class="modal-title">Registrar Venda de Cereal</h5>
          <p class="modal-subtitle mb-0">
            Operação lançada como
            <strong style="color:#fca5a5;">VENDA DE CEREAIS</strong>
            — silo atualizado automaticamente.
          </p>
        </div>
        <button type="button" class="btn-close"
                data-sw-close="modal-venda-cereal"></button>
      </div>

      <div class="modal-body">
        <form id="form-venda-cereal" method="POST"
              action="index.php?page=venda_cereal">

          <input type="hidden" name="valor_operacao" id="venda-valor-operacao">
          <input type="hidden" name="safra_id"
                 value="<?= (int)($safra['id'] ?? 0) ?>">

          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label">Cereal vendido</label>
              <select class="form-select" id="venda-produto"
                      name="produto_id" required>
                <option value="">Selecione o cereal...</option>
                <?php foreach ($cereais ?? [] as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"
                          data-nome="<?= htmlspecialchars($c['nome']) ?>">
                    <?= htmlspecialchars($c['nome']) ?>
                    <?= !empty($c['unidade_medida'])
                        ? '(' . htmlspecialchars($c['unidade_medida']) . ')' : '' ?>
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
                    <?= !empty($prop['municipio'])
                        ? '— ' . htmlspecialchars($prop['municipio']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Quantidade vendida (kg)</label>
              <input type="number" class="form-control" id="venda-qtd"
                     name="quantidade" placeholder="0"
                     step="0.001" min="0.001" required
                     oninput="calcularVenda()">
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
                <span class="fw-normal text-muted"
                      style="font-size:10px;">editável</span>
              </label>
              <input type="number" class="form-control" id="venda-vl-bruto"
                     placeholder="0,00" step="0.01" min="0"
                     oninput="aplicarDesconto()">
            </div>
          </div>

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

          <div class="mb-3 p-3"
               style="background:#fff5f5;border:1px solid #fecaca;
                      border-radius:12px;">
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
              <span id="rv-desc-val" class="fw-semibold text-danger">
                − R$ 0,00
              </span>
            </div>
            <hr style="border-color:#fecaca;margin:8px 0;">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold" style="color:#dc2626;">Valor final</span>
              <span id="rv-final"
                    style="font-family:'DM Serif Display',Georgia,serif;
                           font-size:22px;color:#dc2626;">R$ 0,00</span>
            </div>
          </div>

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
                data-sw-close="modal-venda-cereal">Cancelar</button>
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="../../public/js/app.js"></script>

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



document.querySelectorAll('.toast').forEach(t => {
  const d = parseFloat(getComputedStyle(t).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(t.id), d);
});

// ── Decisão: abre modal final fechando o de decisão ───────────
function abrirModalCompraDetalhe() {
  ModalManager.close('modal-nova-op-financeira');
  setTimeout(() => ModalManager.open('modal-compra-insumo'), 300);
}

function abrirModalVendaDetalhe() {
  ModalManager.close('modal-nova-op-financeira');
  setTimeout(() => ModalManager.open('modal-venda-cereal'), 300);
}

// ── Cálculo compra ────────────────────────────────────────────
function calcularValorPorDose() {
  const valor = parseFloat(document.getElementById('compra-valor').value)     || 0;
  const qtd   = parseFloat(document.getElementById('compra-quantidade').value) || 0;
  const el    = document.getElementById('dose-calculada');
  if (valor > 0 && qtd > 0) {
    el.textContent = (valor / qtd).toLocaleString('pt-BR', {
      minimumFractionDigits: 2, maximumFractionDigits: 4
    });
    el.style.color = 'var(--verde-vivo)';
  } else {
    el.textContent = '—';
    el.style.color = 'var(--texto-suave)';
  }
}

// ── Cálculo venda ─────────────────────────────────────────────
const SACA_KG = 60;
const fmtBRL  = v => 'R$ ' + Number(v).toLocaleString('pt-BR', {
  minimumFractionDigits: 2, maximumFractionDigits: 2
});

function calcularVenda() {
  const qtd   = parseFloat(document.getElementById('venda-qtd').value)     || 0;
  const vsc   = parseFloat(document.getElementById('venda-vl-saca').value) || 0;
  const bruto = (qtd / SACA_KG) * vsc;
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
    input.step         = tipo === 'percentual' ? '0.1' : '1';
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

  let desconto = 0, final = bruto, nota = 'Sem descontos aplicados', lDesc = '';

  if (tipo === 'percentual' && dVal > 0) {
    desconto = bruto * (dVal / 100);
    final    = bruto - desconto;
    nota     = `Desconto de ${dVal}% aplicado`;
    lDesc    = `Desconto (${dVal}%)`;
  } else if (tipo === 'sacas' && dVal > 0) {
    desconto = dVal * vsc;
    final    = bruto - desconto;
    nota     = `Desconto de ${dVal} sacas (${dVal * SACA_KG}kg) do total`;
    lDesc    = `Desconto (${dVal} sacas)`;
  }

  final = Math.max(0, final);
  const sacas = qtd / SACA_KG;

  document.getElementById('rv-sacas').textContent = sacas > 0
    ? sacas.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' sc'
    : '—';
  document.getElementById('rv-bruto').textContent = fmtBRL(bruto);
  document.getElementById('rv-final').textContent = fmtBRL(final);
  document.getElementById('venda-valor-operacao').value = final.toFixed(2);

  const linhaDesc = document.getElementById('rv-desc-linha');
  if (desconto > 0) {
    linhaDesc.style.removeProperty('display');
    document.getElementById('rv-desc-label').textContent = lDesc;
    document.getElementById('rv-desc-val').textContent   = '− ' + fmtBRL(desconto);
  } else {
    linhaDesc.style.display = 'none';
  }

  const ta   = document.getElementById('venda-descricao');
  const base = (ta.dataset.base || '').trim();
  ta.value   = base ? `${base}\n${nota}` : nota;
}

document.getElementById('venda-descricao')?.addEventListener('input', function () {
  const lines = this.value.split('\n');
  const last  = lines[lines.length - 1];
  if (/^(Sem descontos|Desconto de)/i.test(last) && lines.length > 1)
    this.dataset.base = lines.slice(0, -1).join('\n').trim();
  else if (!/^(Sem descontos|Desconto de)/i.test(last))
    this.dataset.base = this.value;
});

// ── Limpa ao fechar ───────────────────────────────────────────
['modal-compra-insumo', 'modal-venda-cereal'].forEach(id => {
  document.getElementById(id)?.addEventListener('hidden.bs.modal', () => {
    document.getElementById(id).querySelector('form')?.reset();
    const dose = document.getElementById('dose-calculada');
    if (dose) { dose.textContent = '—'; dose.style.color = 'var(--texto-suave)'; }
    ['rv-sacas','rv-bruto','rv-final'].forEach(r => {
      const el = document.getElementById(r);
      if (el) el.textContent = r === 'rv-sacas' ? '—' : 'R$ 0,00';
    });
    const ta = document.getElementById('venda-descricao');
    if (ta) { ta.value = ''; ta.dataset.base = ''; }
    document.getElementById('venda-desc-wrap')?.style.setProperty('display','none');
    document.getElementById('rv-desc-linha')?.style.setProperty('display','none','important');
  });
});



// ── Cálculo custo operação agrícola ──────────────────────────
function calcularCustoOperacao() {
  const sel       = document.getElementById('op-insumo');
  const opt       = sel.options[sel.selectedIndex];
  const valorDose = parseFloat(opt?.dataset.valorDose) || 0;
  const unidade   = opt?.dataset.unidade || 'un';
  const qtd       = parseFloat(document.getElementById('op-quantidade').value) || 0;
  const extra     = parseFloat(document.getElementById('op-custo-extra').value) || 0;

  // Atualiza label da unidade no campo quantidade
  const labelUnidade = document.getElementById('op-unidade-label');
  if (labelUnidade) labelUnidade.textContent = unidade ? `(${unidade})` : '';

  const custoBase = qtd * valorDose;
  const total     = custoBase + extra;

  document.getElementById('op-custo-base').textContent  = fmtBRL(custoBase);
  document.getElementById('op-custo-total').textContent = fmtBRL(total);
  document.getElementById('op-custo-operacao').value    = total.toFixed(2);
}

// Limpa modal agrícola ao fechar
document.getElementById('modal-nova-op-agricola')
  ?.addEventListener('hidden.bs.modal', () => {
    document.getElementById('form-op-agricola')?.reset();
    document.getElementById('op-custo-base').textContent  = 'R$ 0,00';
    document.getElementById('op-custo-total').textContent = 'R$ 0,00';
    document.getElementById('op-custo-operacao').value    = '0';
    document.getElementById('op-unidade-label').textContent = '';
  });
</script>

</body>
</html>

