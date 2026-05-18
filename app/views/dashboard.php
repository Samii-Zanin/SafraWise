<?php
$user  = $_SESSION['user'];
$tipo  = $_SESSION['tipo'];

$h        = (int) date('H');
$saudacao = $h < 12 ? 'Bom dia' : ($h < 18 ? 'Boa tarde' : 'Boa noite');

$iniciais = strtoupper(substr($user['nome'], 0, 1));
if (str_contains($user['nome'], ' ')) {
    $partes   = explode(' ', $user['nome']);
    $iniciais = strtoupper($partes[0][0] . end($partes)[0]);
}

$pagina_atual = 'dashboard';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

// cores fixas por status de talhão — iguais ao resto do sistema
$statusCor = [
    'plantado' => '#40916c',
    'colheita' => '#d4a017',
    'preparo'  => '#6b8f75',
];
$statusLabel = [
    'plantado' => 'Plantado',
    'colheita' => 'Colheita',
    'preparo'  => 'Preparo',
];

// cada cultura ganha uma cor fixa para os progress bars
$coresCultura = ['#40916c', '#d4a017', '#3b82f6', '#8b5cf6', '#f97316', '#06b6d4'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Dashboard</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
    @keyframes spin { to { transform: rotate(360deg); } }

    .metric-icon svg { width: 20px !important; height: 20px !important; }
    .metric-delta svg { width: 13px !important; height: 13px !important; }

    .empty-state {
      padding: 40px 24px;
      text-align: center;
      color: var(--texto-suave);
    }
    .empty-state svg { opacity: .35; margin-bottom: 12px; }
    .empty-state p   { font-size: 13.5px; margin: 0; }
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

      <!-- ── Métricas ── -->
      <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6">
          <div class="card metric-card verde h-100">
            <div class="card-body p-4">
              <div class="metric-icon verde mb-3">
                <i data-lucide="map-pin"></i>
              </div>
              <div class="metric-label mb-1">Talhões cadastrados</div>
              <div class="metric-value mb-2">
                <?= $metricas['total_talhoes'] ?>
              </div>
              <?php if ($metricas['total_talhoes'] > 0): ?>
              <div class="metric-delta up">
                <i data-lucide="trending-up"></i>
                <?= number_format($metricas['area_total'], 1, ',', '.') ?> ha produtivos
              </div>
              <?php else: ?>
              <div class="metric-delta" style="color:var(--texto-suave)">
                Nenhum talhão ainda
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="card metric-card dourado h-100">
            <div class="card-body p-4">
              <div class="metric-icon dourado mb-3">
                <i data-lucide="ruler"></i>
              </div>
              <div class="metric-label mb-1">Área produtiva total</div>
              <div class="metric-value mb-2">
                <?= $metricas['area_total'] > 0
                    ? number_format($metricas['area_total'], 1, ',', '.') . ' ha'
                    : '— ha' ?>
              </div>
              <?php if ($metricas['area_total'] > 0): ?>
              <div class="metric-delta up">
                <i data-lucide="trending-up"></i>
                soma das propriedades
              </div>
              <?php else: ?>
              <div class="metric-delta" style="color:var(--texto-suave)">
                Cadastre uma propriedade
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="card metric-card azul h-100">
            <div class="card-body p-4">
              <div class="metric-icon azul mb-3">
                <i data-lucide="package"></i>
              </div>
              <div class="metric-label mb-1">Insumos em estoque</div>
              <div class="metric-value mb-2">
                <?= $metricas['total_insumos'] ?>
              </div>
              <?php if ($metricas['total_insumos'] > 0): ?>
              <div class="metric-delta up">
                <i data-lucide="check-circle"></i>
                itens com quantidade
              </div>
              <?php else: ?>
              <div class="metric-delta" style="color:var(--texto-suave)">
                Nenhum item em estoque
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="card metric-card roxo h-100">
            <div class="card-body p-4">
              <div class="metric-icon roxo mb-3">
                <i data-lucide="calendar-range"></i>
              </div>
              <div class="metric-label mb-1">Próxima colheita</div>
              <div class="metric-value mb-2">
                <?php if ($metricas['proxima_safra']): ?>
                  <?= $metricas['dias_para_colheita'] ?> dias
                <?php else: ?>
                  —
                <?php endif; ?>
              </div>
              <?php if ($metricas['proxima_safra']): ?>
              <div class="metric-delta up">
                <i data-lucide="sprout"></i>
                <?= htmlspecialchars($metricas['proxima_safra']['talhao']) ?>
                — <?= htmlspecialchars($metricas['proxima_safra']['cultura']) ?>
              </div>
              <?php else: ?>
              <div class="metric-delta" style="color:var(--texto-suave)">
                Nenhuma safra ativa
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div><!-- /métricas -->

      <!-- ── Corpo: talhões + barra lateral ── -->
      <div class="row g-4">

        <!-- coluna principal -->
        <div class="col-xl-8 d-flex flex-column gap-4">

          <!-- talhões desta safra -->
          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Talhões</span>
              <a href="?page=talhoes" class="section-action">Ver todos →</a>
            </div>

            <?php if (empty($talhoes)): ?>
              <div class="empty-state">
                <i data-lucide="map" style="width:40px;height:40px;display:block;margin:0 auto 12px"></i>
                <p>Nenhum talhão cadastrado ainda.<br>
                   <a href="?page=talhoes" class="text-success">Adicionar primeiro talhão</a>
                </p>
              </div>
            <?php else: ?>
              <?php foreach ($talhoes as $t):
                $status = $t['status'] ?? 'preparo';
                $cor    = $statusCor[$status]  ?? '#6b8f75';
                $label  = $statusLabel[$status] ?? ucfirst($status);
              ?>
              <div class="talhao-row">
                <div class="talhao-dot" style="background:<?= $cor ?>"></div>
                <div class="talhao-info">
                  <div class="talhao-name"><?= htmlspecialchars($t['nome']) ?></div>
                  <div class="talhao-meta">
                    <?= $t['cultura'] ? htmlspecialchars($t['cultura']) : 'Sem safra ativa' ?>
                    <?php if (!empty($t['propriedade'])): ?>
                      · <?= htmlspecialchars($t['propriedade']) ?>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="talhao-area">
                  <?= number_format((float)$t['area_hectare'], 1, ',', '.') ?> ha
                </div>
                <span class="status-pill <?= $status ?>"><?= $label ?></span>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- progresso por cultura -->
          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Progresso por cultura</span>
              <a href="?page=talhoes" class="section-action">Safras →</a>
            </div>

            <?php if (empty($progressoCulturas)): ?>
              <div class="empty-state">
                <i data-lucide="sprout" style="width:40px;height:40px;display:block;margin:0 auto 12px"></i>
                <p>Nenhuma safra ativa no momento.</p>
              </div>
            <?php else: ?>
              <?php foreach ($progressoCulturas as $i => $c):
                $cor = $coresCultura[$i % count($coresCultura)];
                $pct = min(100, max(0, (int)$c['progresso']));
              ?>
              <div class="progress-item">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                  <span class="progress-name">
                    <?= htmlspecialchars($c['cultura']) ?>
                    <span class="text-muted ms-1" style="font-size:11px">
                      (<?= $c['talhoes'] ?> talhão<?= $c['talhoes'] != 1 ? 'ões' : '' ?>)
                    </span>
                  </span>
                  <span class="progress-pct"><?= $pct ?>%</span>
                </div>
                <div class="progress" style="height:6px; border-radius:4px; background:#edf2ee">
                  <div class="progress-bar" role="progressbar"
                       style="width:<?= $pct ?>%; background:<?= $cor ?>; border-radius:4px; transition:width 1s ease"
                       aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        </div><!-- /col principal -->

        <!-- coluna lateral -->
        <div class="col-xl-4 d-flex flex-column gap-4">

          <!-- clima — cidade da propriedade cadastrada -->
          <div class="clima-card" id="dash-clima">

            <!-- seletor de propriedade -->
            <?php if (!empty($propriedades)): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;position:relative;z-index:2">
              <span style="font-size:10px;opacity:.5;text-transform:uppercase;letter-spacing:.6px">Clima</span>
              <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="dw-prop-btn"
                  style="background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:11px;padding:3px 10px;line-height:1.5;max-width:170px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  <i data-lucide="map-pin" style="width:11px;height:11px;vertical-align:middle;margin-right:3px"></i>
                  <span id="dw-prop-label"><?= htmlspecialchars($propriedades[0]['nome']) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:12px;min-width:200px">
                  <?php foreach ($propriedades as $p): ?>
                  <li>
                    <button class="dropdown-item d-flex align-items-center justify-content-between gap-3 py-2"
                            data-prop-id="<?= $p['id'] ?>"
                            data-prop-nome="<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>">
                      <span><?= htmlspecialchars($p['nome']) ?></span>
                      <span class="text-muted" style="font-size:10px;white-space:nowrap"><?= htmlspecialchars($p['municipio']) ?>, <?= htmlspecialchars($p['estado']) ?></span>
                    </button>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <?php endif; ?>

            <!-- loading -->
            <div id="dclima-loading" style="text-align:center;padding:20px 0;color:var(--texto-suave)">
              <i data-lucide="loader" style="width:22px;height:22px;animation:spin 1.2s linear infinite"></i>
              <div style="font-size:12px;margin-top:6px">Buscando clima...</div>
            </div>

            <!-- erro -->
            <div id="dclima-error" style="display:none;text-align:center;padding:16px 0;color:var(--texto-suave);font-size:12px">
              <i data-lucide="alert-triangle" style="width:18px;height:18px;margin-bottom:6px"></i>
              <div id="dclima-error-msg">Localização indisponível</div>
              <a href="?page=clima" style="font-size:11px;color:#40916c">Ver página de clima →</a>
            </div>

            <!-- dados -->
            <div id="dclima-content" style="display:none">
              <div class="clima-local mb-2">
                <i data-lucide="map-pin" style="width:13px;height:13px;vertical-align:middle"></i>
                <span id="dw-city">—</span>
              </div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <i id="dw-icon" data-lucide="sun" style="width:32px;height:32px;color:#f4a226;flex-shrink:0"></i>
                <div class="clima-temp" id="dw-temp">—</div>
              </div>
              <div class="clima-desc mb-3" id="dw-desc">—</div>
              <div class="d-flex gap-4">
                <div>
                  <span class="clima-stat-label">Umidade</span>
                  <span class="clima-stat-val" id="dw-humidity">—</span>
                </div>
                <div>
                  <span class="clima-stat-label">Vento</span>
                  <span class="clima-stat-val" id="dw-wind">—</span>
                </div>
                <div>
                  <span class="clima-stat-label">Sensação</span>
                  <span class="clima-stat-val" id="dw-feels">—</span>
                </div>
              </div>
            </div>

          </div>

          <!-- atalhos rápidos -->
          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Atalhos</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
              <a href="?page=talhoes"         class="btn btn-outline-success btn-sm d-flex align-items-center gap-2">
                <i data-lucide="map-pin"      style="width:15px;height:15px"></i> Gerenciar talhões
              </a>
              <a href="?page=insumos"         class="btn btn-outline-success btn-sm d-flex align-items-center gap-2">
                <i data-lucide="package"      style="width:15px;height:15px"></i> Ver insumos
              </a>
              <a href="?page=estoques"        class="btn btn-outline-success btn-sm d-flex align-items-center gap-2">
                <i data-lucide="database"     style="width:15px;height:15px"></i> Ver estoques
              </a>
              <a href="?page=relatorios"      class="btn btn-outline-success btn-sm d-flex align-items-center gap-2">
                <i data-lucide="bar-chart-2"  style="width:15px;height:15px"></i> Gerar relatório
              </a>
              <?php if ($tipo === 'proprietario'): ?>
              <a href="?page=propriedades"    class="btn btn-outline-success btn-sm d-flex align-items-center gap-2">
                <i data-lucide="home"         style="width:15px;height:15px"></i> Propriedades
              </a>
              <?php endif; ?>
            </div>
          </div>

        </div><!-- /col lateral -->

      </div><!-- /row corpo -->

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
lucide.createIcons();

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

// ── widget de clima do dashboard ──
(function () {
  const TEMAS = {
    thunderstorm: { bg: 'linear-gradient(135deg,#1a0533 0%,#2d1b4e 60%,#4a2070 100%)', icon: '#c084fc', glow: 'rgba(168,85,247,.22)' },
    drizzle:      { bg: 'linear-gradient(135deg,#0c2238 0%,#1a3a5f 60%,#2a5080 100%)', icon: '#93c5fd', glow: 'rgba(147,197,253,.18)' },
    rain:         { bg: 'linear-gradient(135deg,#0c1c33 0%,#163557 60%,#1d4878 100%)', icon: '#60a5fa', glow: 'rgba(96,165,250,.22)'  },
    snow:         { bg: 'linear-gradient(135deg,#162233 0%,#243d57 60%,#3a5f80 100%)', icon: '#e0f2fe', glow: 'rgba(224,242,254,.18)' },
    atmosphere:   { bg: 'linear-gradient(135deg,#1c1c2c 0%,#2e2e42 60%,#454560 100%)', icon: '#94a3b8', glow: 'rgba(148,163,184,.12)' },
    clear:        { bg: 'linear-gradient(135deg,#7c2d12 0%,#c2410c 40%,#ea580c 100%)', icon: '#fde68a', glow: 'rgba(253,230,138,.35)' },
    fewClouds:    { bg: 'linear-gradient(135deg,#14532d 0%,#166534 50%,#b45309 100%)', icon: '#fde68a', glow: 'rgba(253,230,138,.22)' },
    clouds:       { bg: 'linear-gradient(135deg,#1e2a38 0%,#2d3e52 60%,#3d5060 100%)', icon: '#cbd5e1', glow: 'rgba(203,213,225,.12)' },
  };

  function getTema(id) {
    if (id >= 200 && id < 300) return TEMAS.thunderstorm;
    if (id >= 300 && id < 400) return TEMAS.drizzle;
    if (id >= 500 && id < 600) return TEMAS.rain;
    if (id >= 600 && id < 700) return TEMAS.snow;
    if (id >= 700 && id < 800) return TEMAS.atmosphere;
    if (id === 800)             return TEMAS.clear;
    if (id === 801)             return TEMAS.fewClouds;
    return TEMAS.clouds;
  }

  function owmIcon(id) {
    if (id >= 200 && id < 300) return 'cloud-lightning';
    if (id >= 300 && id < 400) return 'cloud-drizzle';
    if (id >= 500 && id < 600) return 'cloud-rain';
    if (id >= 600 && id < 700) return 'cloud-snow';
    if (id >= 700 && id < 800) return 'wind';
    if (id === 800)             return 'sun';
    if (id === 801)             return 'cloud-sun';
    return 'cloud';
  }

  // OWM às vezes reporta "nublado" mesmo com chuva ativa — checa os campos de precipitação
  function detectarCondicao(d) {
    const mm = (d.rain?.['1h'] ?? d.rain?.['3h'] ?? 0);
    const sn = (d.snow?.['1h'] ?? d.snow?.['3h'] ?? 0);
    if (sn > 0)      return 601; // neve
    if (mm >= 2.5)   return 501; // chuva moderada
    if (mm >= 0.1)   return 500; // chuva leve / garoa
    return d.weather[0].id;
  }

  function showError(msg) {
    document.getElementById('dclima-loading').style.display = 'none';
    document.getElementById('dclima-error-msg').textContent = msg;
    document.getElementById('dclima-error').style.display   = '';
    lucide.createIcons();
  }

  function renderDash(d) {
    const id   = detectarCondicao(d);
    const tema = getTema(id);
    const card = document.getElementById('dash-clima');

    card.style.background = tema.bg;

    let glow = card.querySelector('.dash-clima-glow');
    if (!glow) {
      glow = document.createElement('div');
      glow.className = 'dash-clima-glow';
      glow.style.cssText = 'position:absolute;inset:0;pointer-events:none;border-radius:inherit';
      card.insertBefore(glow, card.firstChild);
    }
    glow.style.background = `radial-gradient(ellipse at 90% 10%,${tema.glow} 0%,transparent 65%)`;

    document.getElementById('dw-city').textContent     = `${d.name}, ${d.sys.country}`;
    document.getElementById('dw-temp').textContent     = `${Math.round(d.main.temp)}°C`;
    document.getElementById('dw-desc').textContent     = d.weather[0].description;
    document.getElementById('dw-humidity').textContent = `${d.main.humidity}%`;
    document.getElementById('dw-wind').textContent     = `${Math.round(d.wind.speed * 3.6)} km/h`;
    document.getElementById('dw-feels').textContent    = `${Math.round(d.main.feels_like)}°C`;

    const iconEl = document.getElementById('dw-icon');
    iconEl.setAttribute('data-lucide', owmIcon(id));
    iconEl.style.color = tema.icon;

    document.getElementById('dclima-loading').style.display = 'none';
    document.getElementById('dclima-content').style.display = '';
    lucide.createIcons();
  }

  function buscarClima(propId, propNome) {
    document.getElementById('dclima-loading').style.display = '';
    document.getElementById('dclima-error').style.display   = 'none';
    document.getElementById('dclima-content').style.display = 'none';
    lucide.createIcons();
    if (propNome) document.getElementById('dw-prop-label').textContent = propNome;
    const url = propId ? `index.php?page=weather_proxy&prop=${propId}` : 'index.php?page=weather_proxy';
    fetch(url)
      .then(r => r.json())
      .then(d => d.error ? showError(d.error) : renderDash(d))
      .catch(() => showError('Falha ao buscar clima'));
  }

  // wire itens do dropdown
  document.querySelectorAll('#dash-clima [data-prop-id]').forEach(btn => {
    btn.addEventListener('click', () => buscarClima(btn.dataset.propId, btn.dataset.propNome));
  });

  // fetch inicial com a primeira propriedade
  const primeiro = document.querySelector('#dash-clima [data-prop-id]');
  buscarClima(primeiro?.dataset.propId ?? null);
})();
</script>

</body>
</html>
