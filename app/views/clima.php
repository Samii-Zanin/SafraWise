<?php
function owmIconeNome(int $id): string {
    if ($id >= 200 && $id < 300) return 'cloud-lightning';
    if ($id >= 300 && $id < 400) return 'cloud-drizzle';
    if ($id >= 500 && $id < 600) return 'cloud-rain';
    if ($id >= 600 && $id < 700) return 'cloud-snow';
    if ($id >= 700 && $id < 800) return 'wind';
    if ($id === 800)              return 'sun';
    if ($id === 801)              return 'cloud-sun';
    return 'cloud';
}

function fmtHora(int $unix, int $offset): string {
    $d = new DateTime('@' . ($unix + $offset));
    return $d->format('H:i');
}

// OWM às vezes classifica como "nublado" mesmo com chuva nos campos de medição
function detectarCondicao(array $clima): int {
    $mm = (float) ($clima['rain']['1h'] ?? $clima['rain']['3h'] ?? 0);
    $sn = (float) ($clima['snow']['1h'] ?? $clima['snow']['3h'] ?? 0);
    if ($sn > 0)    return 601;
    if ($mm >= 2.5) return 501;
    if ($mm >= 0.1) return 500;
    return (int) $clima['weather'][0]['id'];
}

// cada grupo de condição OWM ganha um tema visual próprio
function owmTema(int $id): array {
    if ($id >= 200 && $id < 300) return [
        'bg'         => 'linear-gradient(135deg,#1a0533 0%,#2d1b4e 60%,#4a2070 100%)',
        'glow'       => 'rgba(168,85,247,.20)',
        'icon_color' => '#c084fc',
        'stat_bg'    => 'rgba(168,85,247,.08)',
    ];
    if ($id >= 300 && $id < 400) return [
        'bg'         => 'linear-gradient(135deg,#0c2238 0%,#1a3a5f 60%,#2a5080 100%)',
        'glow'       => 'rgba(147,197,253,.15)',
        'icon_color' => '#93c5fd',
        'stat_bg'    => 'rgba(147,197,253,.08)',
    ];
    if ($id >= 500 && $id < 600) return [
        'bg'         => 'linear-gradient(135deg,#0c1c33 0%,#163557 60%,#1d4878 100%)',
        'glow'       => 'rgba(96,165,250,.20)',
        'icon_color' => '#60a5fa',
        'stat_bg'    => 'rgba(96,165,250,.08)',
    ];
    if ($id >= 600 && $id < 700) return [
        'bg'         => 'linear-gradient(135deg,#162233 0%,#243d57 60%,#3a5f80 100%)',
        'glow'       => 'rgba(224,242,254,.15)',
        'icon_color' => '#e0f2fe',
        'stat_bg'    => 'rgba(224,242,254,.08)',
    ];
    if ($id >= 700 && $id < 800) return [
        'bg'         => 'linear-gradient(135deg,#1c1c2c 0%,#2e2e42 60%,#454560 100%)',
        'glow'       => 'rgba(148,163,184,.12)',
        'icon_color' => '#94a3b8',
        'stat_bg'    => 'rgba(148,163,184,.08)',
    ];
    if ($id === 800) return [
        'bg'         => 'linear-gradient(135deg,#7c2d12 0%,#c2410c 40%,#ea580c 100%)',
        'glow'       => 'rgba(253,230,138,.30)',
        'icon_color' => '#fde68a',
        'stat_bg'    => 'rgba(253,230,138,.10)',
    ];
    if ($id === 801) return [
        'bg'         => 'linear-gradient(135deg,#14532d 0%,#166534 50%,#b45309 100%)',
        'glow'       => 'rgba(253,230,138,.20)',
        'icon_color' => '#fde68a',
        'stat_bg'    => 'rgba(253,230,138,.08)',
    ];
    // 802-804 nublado
    return [
        'bg'         => 'linear-gradient(135deg,#1e2a38 0%,#2d3e52 60%,#3d5060 100%)',
        'glow'       => 'rgba(203,213,225,.12)',
        'icon_color' => '#cbd5e1',
        'stat_bg'    => 'rgba(203,213,225,.08)',
    ];
}

$toast = $toast ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Clima</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
    .weather-hero {
      background: linear-gradient(135deg, #0f2318 0%, #1b4332 60%, #2d6a4f 100%);
      border-radius: 16px;
      color: #fff;
      padding: 40px 36px;
      position: relative;
      overflow: hidden;
    }
    .weather-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at 80% 20%, rgba(116,198,157,.15) 0%, transparent 60%);
      pointer-events: none;
    }
    .weather-city  { font-size: 13px; opacity: .7; letter-spacing: .5px; text-transform: uppercase; }
    .weather-temp  { font-size: 72px; font-weight: 700; line-height: 1; letter-spacing: -3px; }
    .weather-desc  { font-size: 18px; text-transform: capitalize; opacity: .9; }
    .weather-feels { font-size: 13px; opacity: .6; }
    .weather-icon-big { opacity: .85; }

    .stat-card {
      background: var(--card-bg, #fff);
      border: 1px solid var(--borda, #e8efe9);
      border-radius: 12px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      height: 100%;
    }
    .stat-card .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--texto-suave, #6e8a71); }
    .stat-card .stat-val   { font-size: 22px; font-weight: 600; color: var(--texto-principal, #1a2e1e); }
    .stat-card .stat-icon  { color: #40916c; }
    .stat-card .stat-icon svg { width: 18px !important; height: 18px !important; }

    .empty-weather {
      text-align: center;
      padding: 80px 24px;
      color: var(--texto-suave, #6e8a71);
    }
    .empty-weather svg { opacity: .3; margin-bottom: 16px; }
    .metric-icon svg { width: 20px !important; height: 20px !important; }
  </style>
</head>
<body>

<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= htmlspecialchars($toast['tipo']) ?>" id="toast-main" style="--toast-duration:5s">
    <div class="toast-body">
      <div class="toast-title"><?= htmlspecialchars($toast['titulo']) ?></div>
      <div class="toast-msg"><?= htmlspecialchars($toast['mensagem']) ?></div>
    </div>
    <button class="toast-close" onclick="closeToast('toast-main')" type="button">&times;</button>
  </div>
</div>
<?php endif; ?>

<div class="app-layout">

  <?php include 'layouts/sidebar.php'; ?>

  <div class="main-content">
    <?php include 'layouts/ticker.php'; ?>
    <?php include 'layouts/topbar.php'; ?>

    <div class="page-body">

      <!-- seletor de propriedade — só aparece quando há mais de uma -->
      <?php if (count($propriedades) > 1): ?>
      <div class="d-flex flex-wrap gap-2 mb-4">
        <?php foreach ($propriedades as $p):
          $ativo = ((int)$p['id'] === (int)($propriedade['id'] ?? 0));
        ?>
        <a href="?page=clima&prop=<?= $p['id'] ?>"
           class="btn btn-sm <?= $ativo ? 'btn-success' : 'btn-outline-secondary' ?>"
           style="font-size:12px">
          <i data-lucide="home" style="width:12px;height:12px;vertical-align:middle"></i>
          <?= htmlspecialchars($p['nome']) ?>
          <span style="opacity:.65;font-size:10px;margin-left:2px"><?= htmlspecialchars($p['municipio']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!$propriedade): ?>
        <!-- sem propriedade cadastrada -->
        <div class="empty-weather">
          <i data-lucide="cloud-off" style="width:48px;height:48px;display:block;margin:0 auto 16px"></i>
          <h5 class="mb-2">Nenhuma propriedade cadastrada</h5>
          <p style="font-size:14px">Cadastre uma propriedade com município para ver o clima.</p>
          <a href="?page=propriedades" class="btn btn-success btn-sm mt-2">Cadastrar propriedade</a>
        </div>

      <?php elseif (!$clima): ?>
        <!-- propriedade existe mas a API falhou -->
        <div class="empty-weather">
          <i data-lucide="alert-triangle" style="width:48px;height:48px;display:block;margin:0 auto 16px;color:#f97316;opacity:1"></i>
          <h5 class="mb-2">Não foi possível obter o clima</h5>
          <p style="font-size:14px">
            Município: <strong><?= htmlspecialchars($propriedade['municipio']) ?>, <?= htmlspecialchars($propriedade['estado']) ?></strong>
          </p>
          <p style="font-size:13px;opacity:.7">Verifique a chave da API ou tente novamente.</p>
          <a href="?page=clima" class="btn btn-outline-success btn-sm mt-2">
            <i data-lucide="refresh-cw" style="width:13px;height:13px"></i> Tentar novamente
          </a>
        </div>

      <?php else:
        $wId    = detectarCondicao($clima);
        $icone  = owmIconeNome($wId);
        $offset = (int) $clima['timezone'];
        $tema   = owmTema($wId);
      ?>

        <!-- hero card — tema dinâmico por condição -->
        <div class="weather-hero mb-4" style="background:<?= $tema['bg'] ?>">
          <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 80% 20%,<?= $tema['glow'] ?> 0%,transparent 60%);pointer-events:none"></div>
          <div class="row align-items-center g-0" style="position:relative">
            <div class="col">
              <div class="weather-city mb-2">
                <i data-lucide="map-pin" style="width:13px;height:13px;vertical-align:middle;margin-right:4px"></i>
                <?= htmlspecialchars($clima['name']) ?>, <?= htmlspecialchars($propriedade['estado']) ?>
              </div>
              <div class="weather-temp"><?= round($clima['main']['temp']) ?>°C</div>
              <div class="weather-desc mt-1"><?= htmlspecialchars(ucfirst($clima['weather'][0]['description'])) ?></div>
              <div class="weather-feels mt-1">Sensação térmica <?= round($clima['main']['feels_like']) ?>°C</div>
            </div>
            <div class="col-auto weather-icon-big">
              <i data-lucide="<?= $icone ?>" style="width:80px;height:80px;color:<?= $tema['icon_color'] ?>"></i>
            </div>
          </div>
        </div>

        <!-- grade de detalhes -->
        <div class="row g-3 mb-3">

          <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card">
              <span class="stat-icon"><i data-lucide="droplets"></i></span>
              <span class="stat-label">Umidade</span>
              <span class="stat-val"><?= $clima['main']['humidity'] ?>%</span>
            </div>
          </div>

          <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card">
              <span class="stat-icon"><i data-lucide="wind"></i></span>
              <span class="stat-label">Vento</span>
              <span class="stat-val"><?= round($clima['wind']['speed'] * 3.6) ?> km/h</span>
            </div>
          </div>

          <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card">
              <span class="stat-icon"><i data-lucide="eye"></i></span>
              <span class="stat-label">Visibilidade</span>
              <?php $vis = (int)($clima['visibility'] ?? 0); ?>
              <span class="stat-val"><?= $vis >= 1000 ? number_format($vis / 1000, 1, ',', '') . ' km' : "{$vis} m" ?></span>
            </div>
          </div>

          <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card">
              <span class="stat-icon"><i data-lucide="gauge"></i></span>
              <span class="stat-label">Pressão</span>
              <span class="stat-val"><?= $clima['main']['pressure'] ?> hPa</span>
            </div>
          </div>

          <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card">
              <span class="stat-icon"><i data-lucide="sunrise"></i></span>
              <span class="stat-label">Nascer do sol</span>
              <span class="stat-val"><?= fmtHora((int)$clima['sys']['sunrise'], $offset) ?></span>
            </div>
          </div>

          <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card">
              <span class="stat-icon"><i data-lucide="sunset"></i></span>
              <span class="stat-label">Pôr do sol</span>
              <span class="stat-val"><?= fmtHora((int)$clima['sys']['sunset'], $offset) ?></span>
            </div>
          </div>

        </div>

        <p class="text-muted" style="font-size:11px">
          <i data-lucide="refresh-cw" style="width:11px;height:11px;vertical-align:middle"></i>
          Atualizado agora ·
          Fonte: <?= htmlspecialchars($propriedade['municipio']) ?>, <?= htmlspecialchars($propriedade['estado']) ?> ·
          <a href="?page=clima&prop=<?= $propriedade['id'] ?>" style="color:inherit">Atualizar</a>
        </p>

      <?php endif; ?>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
lucide.createIcons();
function closeToast(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.add('hide'); setTimeout(() => el.remove(), 320); }
}
document.querySelectorAll('.toast').forEach(t => {
  const d = parseFloat(getComputedStyle(t).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(t.id), d);
});
</script>

</body>
</html>
