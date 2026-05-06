<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: /public/");
    exit;
}

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

$pagina_atual = $_GET['page'] ?? 'dashboard';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}
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

  <!-- Bootstrap primeiro, depois a customização da marca -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
</head>
<body>

<!-- ════ TOASTS ════ -->
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

      <!-- ── Métricas: Bootstrap row + col (substituem o CSS Grid .metrics-grid) ── -->
      <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6">
          <div class="card metric-card verde h-100">
            <div class="card-body p-4">
              <div class="metric-icon verde mb-3">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="metric-label mb-1">Talhões ativos</div>
              <div class="metric-value mb-2 text-light">12</div>
              <div class="metric-delta up">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                +2 nesta safra
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="card metric-card dourado h-100">
            <div class="card-body p-4">
              <div class="metric-icon dourado mb-3">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="metric-label mb-1">Área total</div>
              <div class="metric-value mb-2 text-light">847 ha</div>
              <div class="metric-delta up">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                +60 ha vs safra anterior
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="card metric-card azul h-100">
            <div class="card-body p-4">
              <div class="metric-icon azul mb-3">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                </svg>
              </div>
              <div class="metric-label mb-1">Insumos em estoque</div>
              <div class="metric-value mb-2 text-light">34</div>
              <div class="metric-delta down">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                3 com estoque baixo
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6">
          <div class="card metric-card roxo h-100">
            <div class="card-body p-4">
              <div class="metric-icon roxo mb-3">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="metric-label mb-1">Próxima colheita</div>
              <div class="metric-value mb-2 text-light">28 dias</div>
              <div class="metric-delta up">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Talhão B3 — Soja
              </div>
            </div>
          </div>
        </div>

      </div><!-- /row métricas -->

      <!-- ── Conteúdo: Bootstrap row + col (substituem o CSS Grid .content-grid) ── -->
      <div class="row g-4">

        <!-- Coluna principal -->
        <div class="col-xl-8 d-flex flex-column gap-4">

          <!-- Talhões -->
          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Talhões desta safra</span>
              <a href="?page=talhoes" class="section-action">Ver todos →</a>
            </div>
            <div>
              <?php
              $talhoes = [
                ['nome' => 'A1 - Cerradão Norte',      'cultura' => 'Soja',  'area' => '120 ha', 'status' => 'plantado', 'cor' => '#40916c'],
                ['nome' => 'B2 - Várzea Leste',        'cultura' => 'Milho', 'area' => '85 ha',  'status' => 'colheita', 'cor' => '#d4a017'],
                ['nome' => 'B3 - Pasto Novo',          'cultura' => 'Soja',  'area' => '200 ha', 'status' => 'plantado', 'cor' => '#40916c'],
                ['nome' => 'C1 - Baixada Sul',         'cultura' => 'Trigo', 'area' => '60 ha',  'status' => 'preparo',  'cor' => '#6b8f75'],
                ['nome' => 'D4 - Alto dos Pinheiros',  'cultura' => 'Soja',  'area' => '180 ha', 'status' => 'plantado', 'cor' => '#40916c'],
              ];
              $status_labels = ['plantado' => 'Plantado', 'colheita' => 'Colheita', 'preparo' => 'Preparo'];
              foreach ($talhoes as $t): ?>
              <div class="talhao-row">
                <div class="talhao-dot" style="background: <?= $t['cor'] ?>"></div>
                <div class="talhao-info">
                  <div class="talhao-name"><?= $t['nome'] ?></div>
                  <div class="talhao-meta"><?= $t['cultura'] ?></div>
                </div>
                <div class="talhao-area"><?= $t['area'] ?></div>
                <span class="status-pill <?= $t['status'] ?>"><?= $status_labels[$t['status']] ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Progresso por cultura -->
          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Progresso por cultura</span>
            </div>
            <div>
              <?php
              $culturas = [
                ['nome' => 'Soja',  'pct' => 68, 'cor' => '#40916c'],
                ['nome' => 'Milho', 'pct' => 42, 'cor' => '#d4a017'],
                ['nome' => 'Trigo', 'pct' => 15, 'cor' => '#3b82f6'],
              ];
              foreach ($culturas as $c): ?>
              <div class="progress-item">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                  <span class="progress-name"><?= $c['nome'] ?></span>
                  <span class="progress-pct"><?= $c['pct'] ?>%</span>
                </div>
                <!-- Bootstrap .progress com cor customizada via style -->
                <div class="progress" style="height: 6px; border-radius: 4px; background: #edf2ee;">
                  <div class="progress-bar" role="progressbar"
                       style="width: <?= $c['pct'] ?>%; background: <?= $c['cor'] ?>; border-radius: 4px; transition: width 1s ease;"
                       aria-valuenow="<?= $c['pct'] ?>" aria-valuemin="0" aria-valuemax="100">
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div><!-- /col principal -->

        <!-- Coluna lateral -->
        <div class="col-xl-4 d-flex flex-column gap-4">

          <!-- Clima -->
          <div class="clima-card">
            <div class="clima-local mb-2">📍 Ipiranga do Sul, RS</div>
            <div class="clima-temp">24°C</div>
            <div class="clima-desc mb-3">Parcialmente nublado</div>
            <div class="d-flex gap-4">
              <div>
                <span class="clima-stat-label">Umidade</span>
                <span class="clima-stat-val">72%</span>
              </div>
              <div>
                <span class="clima-stat-label">Vento</span>
                <span class="clima-stat-val">14 km/h</span>
              </div>
              <div>
                <span class="clima-stat-label">Chuva</span>
                <span class="clima-stat-val">8 mm</span>
              </div>
            </div>
          </div>

          <!-- Atividades recentes -->
          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Atividades recentes</span>
            </div>
            <div>
              <?php
              $atividades = [
                ['texto' => 'Aplicação de herbicida no Talhão A1 concluída.', 'tempo' => 'Há 2h',       'cor' => '#40916c'],
                ['texto' => 'Estoque de adubo NPK abaixo do mínimo.',         'tempo' => 'Ontem',       'cor' => '#d4a017'],
                ['texto' => 'Colheita iniciada no Talhão B2 — 42 ha.',        'tempo' => 'Ontem',       'cor' => '#3b82f6'],
                ['texto' => 'Novo peão adicionado: Carlos Ferreira.',          'tempo' => '3 dias atrás','cor' => '#8b5cf6'],
                ['texto' => 'Relatório mensal de julho gerado.',               'tempo' => '5 dias atrás','cor' => '#6b8f75'],
              ];
              foreach ($atividades as $a): ?>
              <div class="activity-item">
                <div class="activity-dot" style="background: <?= $a['cor'] ?>"></div>
                <div>
                  <div class="activity-text"><?= $a['texto'] ?></div>
                  <div class="activity-time"><?= $a['tempo'] ?></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div><!-- /col lateral -->

      </div><!-- /row conteúdo -->

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->

<script>
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
</script>

</body>
</html>