<?php
/*
 * app/views/safras.php
 *
 * Variáveis esperadas:
 *   $safras           — SafraController::getAll() ou index()
 *   $culturasModal    — CulturaController::getDistintas()
 *   $talhoesModal     — TalhaoController::getAll() ou similar
 *   $propriedades     — PropriedadeController::getAll()  (para filtro)
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

$pagina_atual = 'safras';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

function culturaEstilo(string $nome): array {
    $n = mb_strtolower($nome);
    $n = strtr($n, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
                     'ã'=>'a','â'=>'a','ê'=>'e','ô'=>'o','ç'=>'c']);

    if (str_contains($n, 'soja'))     return ['fundo'=>'#fef9c3','borda'=>'#ca8a04','texto'=>'#713f12','tira'=>'#eab308','label'=>'#854d0e'];
    if (str_contains($n, 'milho'))    return ['fundo'=>'#fff7ed','borda'=>'#ea580c','texto'=>'#7c2d12','tira'=>'#f97316','label'=>'#c2410c'];
    if (str_contains($n, 'feij'))     return ['fundo'=>'#1c1917','borda'=>'#57534e','texto'=>'#d6d3d1','tira'=>'#a8a29e','label'=>'#e7e5e4'];
    if (str_contains($n, 'canola'))   return ['fundo'=>'#fefce8','borda'=>'#a16207','texto'=>'#3f2005','tira'=>'#ca8a04','label'=>'#713f12'];
    if (str_contains($n, 'cafe'))     return ['fundo'=>'#2c1204','borda'=>'#7c2d12','texto'=>'#fed7aa','tira'=>'#c2410c','label'=>'#fed7aa'];
    if (str_contains($n, 'trigo'))    return ['fundo'=>'#fffbeb','borda'=>'#b45309','texto'=>'#78350f','tira'=>'#d97706','label'=>'#92400e'];
    if (str_contains($n, 'arroz'))    return ['fundo'=>'#f0fdf4','borda'=>'#15803d','texto'=>'#14532d','tira'=>'#22c55e','label'=>'#166534'];
    if (str_contains($n, 'algod'))    return ['fundo'=>'#f8fafc','borda'=>'#475569','texto'=>'#1e293b','tira'=>'#94a3b8','label'=>'#334155'];
    if (str_contains($n, 'sorgo'))    return ['fundo'=>'#fdf4ff','borda'=>'#86198f','texto'=>'#4a044e','tira'=>'#c026d3','label'=>'#701a75'];
    if (str_contains($n, 'girassol')) return ['fundo'=>'#fff7ed','borda'=>'#c2410c','texto'=>'#7c2d12','tira'=>'#ea580c','label'=>'#9a3412'];
    if (str_contains($n, 'cana'))     return ['fundo'=>'#f0fdf4','borda'=>'#166534','texto'=>'#14532d','tira'=>'#16a34a','label'=>'#15803d'];
    if (str_contains($n, 'aveia'))    return ['fundo'=>'#faf5eb','borda'=>'#92400e','texto'=>'#451a03','tira'=>'#b45309','label'=>'#78350f'];

    return ['fundo'=>'#f0faf4','borda'=>'#40916c','texto'=>'#1a3a28','tira'=>'#40916c','label'=>'#2d6a4f'];
}

$totalSafras   = count($safras ?? []);
$totalAtivas   = 0;
$totalEncerradas = 0;
$culturasFiltro = [];
$propFiltro     = [];

foreach ($safras ?? [] as $s) {
    $ativa = empty($s['data_fim']) || strtotime($s['data_fim']) >= time();
    if ($ativa) $totalAtivas++; else $totalEncerradas++;
    $culturasFiltro[$s['cultura']] = true;
    $propFiltro[$s['nome_propriedade']] = true;
}
ksort($culturasFiltro);
ksort($propFiltro);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Safras</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
    .safra-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }

    @media (max-width: 1100px) { .safra-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 700px)  { .safra-grid { grid-template-columns: 1fr; } }

    .safra-card {
      background: var(--card-fundo, var(--branco));
      border: 1.5px solid var(--card-borda, #e2ece5);
      border-radius: var(--bs-border-radius-lg);
      display: flex;
      flex-direction: column;
      text-decoration: none;
      overflow: hidden;
      transition: transform 0.22s ease, box-shadow 0.22s ease;
      position: relative;
      cursor: pointer;
    }

    .safra-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 6px;
      background: var(--card-tira, var(--verde-vivo));
    }

    .safra-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 48px rgba(0,0,0,.13);
    }

    .safra-card-head {
      padding: 22px 20px 14px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-top: 2px;
    }

    .safra-cultura {
      font-family: 'DM Serif Display', Georgia, serif;
      font-size: 24px;
      line-height: 1.15;
      color: var(--card-texto, var(--texto-escuro));
    }

    .safra-variedade {
      font-size: 12px;
      color: var(--card-label, var(--texto-suave));
      padding: 0 20px 10px;
      opacity: .8;
    }

    .safra-status {
      font-size: 10px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: .5px;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .safra-status.ativa {
      background: rgba(64,145,108,.15);
      color: var(--verde-accent);
    }

    .safra-status.encerrada {
      background: rgba(107,143,117,.1);
      color: var(--texto-suave);
    }

    .safra-divider {
      height: 1px;
      background: var(--card-borda, #e2ece5);
      margin: 0 20px;
      opacity: .4;
    }

    .safra-infos {
      padding: 14px 20px;
      display: flex;
      flex-direction: column;
      gap: 9px;
      flex: 1;
    }

    .safra-info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
    }

    .safra-info-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: var(--card-label, var(--texto-suave));
      opacity: .75;
      white-space: nowrap;
    }

    .safra-info-val {
      font-size: 13px;
      font-weight: 500;
      color: var(--card-texto, var(--texto-escuro));
      text-align: right;
    }

    .safra-datas {
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      border-top: 1px dashed var(--card-borda, #e2ece5);
      margin-top: 4px;
    }

    .safra-datas-bloco { text-align: center; }

    .safra-data-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: var(--card-label, var(--texto-suave));
      opacity: .65;
      margin-bottom: 2px;
    }

    .safra-data-val {
      font-size: 13px;
      font-weight: 600;
      color: var(--card-texto, var(--texto-escuro));
    }

    .safra-data-val.em-andamento {
      color: var(--verde-vivo);
      font-style: italic;
    }

    .safra-datas-seta {
      font-size: 16px;
      color: var(--card-borda, #e2ece5);
      opacity: .7;
    }

    .safra-card-footer {
      padding: 12px 20px;
      border-top: 1px solid var(--card-borda, #e2ece5);
      font-size: 12px;
      font-weight: 600;
      color: var(--card-tira, var(--verde-vivo));
      display: flex;
      align-items: center;
      justify-content: space-between;
      opacity: .8;
      transition: opacity .15s;
    }

    .safra-card:hover .safra-card-footer { opacity: 1; }

    /* ── Filtro bar ── */
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
    .filtro-bar .form-select { font-size: 13px; min-width: 140px; }

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

    /* ── Macro mini-cards ── */
    .mini-card {
      background: var(--branco);
      border: 1px solid #e2ece5;
      border-radius: var(--bs-border-radius-lg);
      padding: 18px 22px;
      position: relative;
      overflow: hidden;
    }

    .mini-card::after {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      border-radius: 2px 2px 0 0;
    }

    .mini-card.verde::after   { background: var(--verde-vivo); }
    .mini-card.dourado::after { background: var(--dourado); }
    .mini-card.cinza::after   { background: var(--texto-suave); }

    .mini-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .7px;
      color: var(--texto-suave);
      margin-bottom: 5px;
    }

    .mini-value {
      font-family: 'DM Serif Display', Georgia, serif;
      font-size: 28px;
      color: var(--texto-escuro);
      line-height: 1;
    }

    .mini-sub { font-size: 11px; color: var(--texto-suave); margin-top: 3px; }

    /* ── Empty state ── */
    .empty-safras {
      text-align: center;
      padding: 64px 24px;
      color: var(--texto-suave);
    }

    .empty-safras svg { opacity: .35; margin-bottom: 16px; }
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

      <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
          <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif;color:var(--texto-escuro);">
            Safras
          </h2>
          <p class="text-muted mb-0 small">
            Gerencie e acompanhe o andamento de cada safra nas propriedades.
          </p>
        </div>
        <button type="button"
                class="btn btn-success d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modal-nova-safra">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Nova Safra
        </button>
      </div>

      <!-- ── Macro cards ── -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="mini-card verde">
            <div class="mini-label">Total de safras</div>
            <div class="mini-value"><?= $totalSafras ?></div>
            <div class="mini-sub">cadastradas no sistema</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mini-card verde">
            <div class="mini-label">Safras ativas</div>
            <div class="mini-value"><?= $totalAtivas ?></div>
            <div class="mini-sub">em andamento atualmente</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mini-card cinza">
            <div class="mini-label">Safras encerradas</div>
            <div class="mini-value"><?= $totalEncerradas ?></div>
            <div class="mini-sub">finalizadas</div>
          </div>
        </div>
      </div>

      <!-- ── Filtros ── -->
      <div class="filtro-bar mb-4" id="filtro-safras">
        <div>
          <label class="form-label">Buscar</label>
          <input type="text" class="form-control form-control-sm"
                 id="busca-safra" placeholder="Cultura, talhão, propriedade..."
                 oninput="filtrarSafras()">
        </div>
        <div>
          <label class="form-label">Cultura</label>
          <select class="form-select form-select-sm" id="filtro-cultura-safra"
                  onchange="filtrarSafras()">
            <option value="">Todas</option>
            <?php foreach (array_keys($culturasFiltro) as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Propriedade</label>
          <select class="form-select form-select-sm" id="filtro-prop-safra"
                  onchange="filtrarSafras()">
            <option value="">Todas</option>
            <?php foreach (array_keys($propFiltro) as $p): ?>
              <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Status</label>
          <select class="form-select form-select-sm" id="filtro-status-safra"
                  onchange="filtrarSafras()">
            <option value="">Todas</option>
            <option value="ativa">Ativas</option>
            <option value="encerrada">Encerradas</option>
          </select>
        </div>
        <button class="btn-limpar-filtro" onclick="limparFiltrosSafras()">
          Limpar filtros
        </button>
        <div class="ms-auto small text-muted" id="contagem-safras">
          <?= $totalSafras ?> safra<?= $totalSafras !== 1 ? 's' : '' ?>
        </div>
      </div>

      <!-- ── Grid de Safras ── -->
      <?php if (empty($safras)): ?>
        <div class="empty-safras">
          <svg width="72" height="72" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="1" class="d-block mx-auto">
            <path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
            <line x1="9" y1="9" x2="9.01" y2="9"/>
            <line x1="15" y1="9" x2="15.01" y2="9"/>
          </svg>
          <h5 style="font-family:'DM Serif Display',Georgia,serif;margin-top:16px;">
            Nenhuma safra cadastrada
          </h5>
          <p class="small mb-0">
            Clique em <strong>Nova Safra</strong> para começar a registrar.
          </p>
        </div>
      <?php else: ?>
        <div class="safra-grid" id="grid-safras">
          <?php foreach ($safras as $safra):
            $estilo  = culturaEstilo($safra['cultura'] ?? '');
            $ativa   = empty($safra['data_fim']) || strtotime($safra['data_fim']) >= time();
            $dataFim = !empty($safra['data_fim']) ? date('d/m/Y', strtotime($safra['data_fim'])) : null;
          ?>
          <a href="?page=safra_detalhe&id=<?= (int)$safra['id'] ?>"
             class="safra-card"
             data-cultura="<?= strtolower(htmlspecialchars($safra['cultura'])) ?>"
             data-prop="<?= strtolower(htmlspecialchars($safra['nome_propriedade'])) ?>"
             data-talhao="<?= strtolower(htmlspecialchars($safra['nome_talhao'] ?? '')) ?>"
             data-status="<?= $ativa ? 'ativa' : 'encerrada' ?>"
             style="--card-fundo:<?= $estilo['fundo'] ?>;
                    --card-borda:<?= $estilo['borda'] ?>;
                    --card-texto:<?= $estilo['texto'] ?>;
                    --card-tira:<?= $estilo['tira'] ?>;
                    --card-label:<?= $estilo['label'] ?>;">

            <!-- Cabeçalho do card -->
            <div class="safra-card-head">
              <div class="safra-cultura">
                <?= htmlspecialchars($safra['cultura']) ?>
              </div>
              <span class="safra-status <?= $ativa ? 'ativa' : 'encerrada' ?>">
                <?= $ativa ? '● Ativa' : '⏹ Encerrada' ?>
              </span>
            </div>

            <?php if (!empty($safra['variedade'])): ?>
              <div class="safra-variedade">
                <?= htmlspecialchars($safra['variedade']) ?>
              </div>
            <?php endif; ?>

            <div class="safra-divider"></div>

            <!-- Informações -->
            <div class="safra-infos">
              <div class="safra-info-row">
                <span class="safra-info-label">Propriedade</span>
                <span class="safra-info-val">
                  <?= htmlspecialchars($safra['nome_propriedade'] ?? '—') ?>
                </span>
              </div>
              <div class="safra-info-row">
                <span class="safra-info-label">Talhão</span>
                <span class="safra-info-val">
                  <?= htmlspecialchars($safra['nome_talhao'] ?? '—') ?>
                </span>
              </div>
              <div class="safra-info-row">
                <span class="safra-info-label">Área</span>
                <span class="safra-info-val">
                  <?= number_format((float)($safra['area_talhao'] ?? 0), 2, ',', '.') ?> ha
                </span>
              </div>
              <div class="safra-info-row">
                <span class="safra-info-label">Município</span>
                <span class="safra-info-val">
                  <?= htmlspecialchars($safra['municipio'] ?? '—') ?>
                  <?= !empty($safra['estado']) ? ', ' . htmlspecialchars($safra['estado']) : '' ?>
                </span>
              </div>
              <div class="safra-info-row">
                <span class="safra-info-label">Status talhão</span>
                <span class="safra-info-val" style="font-size:12px;">
                  <?= htmlspecialchars($safra['status_talhao'] ?? '—') ?>
                </span>
              </div>
            </div>

            <!-- Datas -->
            <div class="safra-datas">
              <div class="safra-datas-bloco">
                <div class="safra-data-label">Início</div>
                <div class="safra-data-val">
                  <?= date('d/m/Y', strtotime($safra['data_inicio'])) ?>
                </div>
              </div>
              <div class="safra-datas-seta">→</div>
              <div class="safra-datas-bloco">
                <div class="safra-data-label">Fim</div>
                <div class="safra-data-val <?= $dataFim ? '' : 'em-andamento' ?>">
                  <?= $dataFim ?? 'Em andamento' ?>
                </div>
              </div>
            </div>

            <!-- Rodapé -->
            <div class="safra-card-footer">
              <span>Ver operações e linha do tempo</span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2.5"
                   stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
              </svg>
            </div>

          </a>
          <?php endforeach; ?>
        </div><!-- /safra-grid -->
      <?php endif; ?>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->


<!-- ════════════════════════════════
     MODAL: Nova Safra
════════════════════════════════ -->
<div class="modal fade sw-modal" id="modal-nova-safra"
     tabindex="-1" aria-hidden="true" data-bs-reset-on-close>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title">Nova Safra</h5>
          <p class="modal-subtitle mb-0">Vincule uma cultura a um talhão e defina o período.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="form-nova-safra" method="POST" action="index.php?page=store_safra">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="ns-cultura">Cultura</label>
              <select class="form-select" id="ns-cultura" name="cultura_id" required>
                <option value="">Selecione a cultura...</option>
                <?php foreach ($culturasModal ?? [] as $c): ?>
                  <option value="<?= (int)$c['id'] ?>">
                    <?= htmlspecialchars($c['nome'] . ' - ' . $c['variedade']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="ns-talhao">Talhão</label>
              <select class="form-select" id="ns-talhao" name="talhao_id" required>
                <option value="">Selecione o talhão...</option>
                <?php foreach ($talhoesModal ?? [] as $t): ?>
                  <option value="<?= (int)$t['id'] ?>">
                    <?= htmlspecialchars($t['nome']) ?>
                    <?= !empty($t['propriedade_nome']) ? ' — ' . htmlspecialchars($t['propriedade_nome']) : '' ?>
                    <?= !empty($t['area_hectare']) ? ' (' . number_format((float)$t['area_hectare'], 2, ',', '.') . ' ha)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-0">
            <div class="col-md-6">
              <label class="form-label" for="ns-inicio">Data de início</label>
              <input type="date" class="form-control" id="ns-inicio"
                     name="data_inicio" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="ns-fim">
                Data de fim
                <span class="fw-normal text-muted ms-1" style="font-size:11px;">
                  (opcional — pode ser definida ao encerrar)
                </span>
              </label>
              <input type="date" class="form-control" id="ns-fim" name="data_fim">
            </div>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button type="submit" form="form-nova-safra"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Cadastrar safra
        </button>
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


document.querySelectorAll('.toast').forEach(t => {
  const d = parseFloat(getComputedStyle(t).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(t.id), d);
});

// ── Filtro dos cards ──────────────────────────────────────────
function filtrarSafras() {
  const busca   = document.getElementById('busca-safra').value.toLowerCase().trim();
  const cultura = document.getElementById('filtro-cultura-safra').value.toLowerCase();
  const prop    = document.getElementById('filtro-prop-safra').value.toLowerCase();
  const status  = document.getElementById('filtro-status-safra').value;

  const cards = document.querySelectorAll('#grid-safras .safra-card');
  let visiveis = 0;

  cards.forEach(card => {
    const textoBusca = (card.dataset.cultura + ' ' + card.dataset.prop + ' ' + card.dataset.talhao).toLowerCase();
    const bateBusca   = !busca   || textoBusca.includes(busca);
    const bateCultura = !cultura || card.dataset.cultura.includes(cultura);
    const bateProp    = !prop    || card.dataset.prop.includes(prop);
    const bateStatus  = !status  || card.dataset.status === status;

    const visivel = bateBusca && bateCultura && bateProp && bateStatus;
    card.style.display = visivel ? '' : 'none';
    if (visivel) visiveis++;
  });

  const el = document.getElementById('contagem-safras');
  if (el) el.textContent = `${visiveis} safra${visiveis !== 1 ? 's' : ''}`;
}

function limparFiltrosSafras() {
  ['busca-safra','filtro-cultura-safra','filtro-prop-safra','filtro-status-safra']
    .forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
  filtrarSafras();
}
</script>

</body>
</html>
