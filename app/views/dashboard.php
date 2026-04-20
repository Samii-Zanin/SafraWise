<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
;
if (!isset($_SESSION['user'])) {
    header("Location: /public/");
    exit;
}

$user = $_SESSION['user'];
$tipo = $_SESSION['tipo'];
$iniciais = strtoupper(substr($user['nome'], 0, 1));
if (strpos($user['nome'], ' ') !== false) {
    $partes = explode(' ', $user['nome']);
    $iniciais = strtoupper($partes[0][0] . end($partes)[0]);
}

$saudacao = (function() {
    $h = (int)date('H');
    if ($h < 12) return 'Bom dia';
    if ($h < 18) return 'Boa tarde';
    return 'Boa noite';
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
  <link rel="preload" href="../../public/css/safrawise.css" as="style">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
</head>
<body>

<!-- ════ TOASTS ════ -->
<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= $toast['tipo'] ?>" id="toast-main" style="--toast-duration: 5s">
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
    <button class="toast-close" onclick="closeToast('toast-main')">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
      </svg>
    </button>
  </div>
</div>
<?php endif; ?>

<div class="app-layout">

  <!-- ════════ SIDEBAR ════════ -->
  <aside class="sidebar">

    <div class="sidebar-logo">
      <!-- LOGO NOVA: computador + planta -->
      <div class="sidebar-logo-icon">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="4" y="16" width="40" height="24" rx="4" fill="#0f2318" stroke="#74c69d" stroke-width="1.5"/>
          <rect x="7" y="19" width="34" height="18" rx="2.5" fill="#0a1c10"/>
          <rect x="21" y="40" width="6" height="4" rx="1.5" fill="#2d6a4f"/>
          <rect x="15" y="44" width="18" height="2.5" rx="1.5" fill="#52b788"/>
          <path d="M24 38 Q23 30 24 20 Q25 13 23 6" stroke="#52b788" stroke-width="2" stroke-linecap="round" fill="none"/>
          <path d="M24 28 Q16 24 13 15 Q20 13 24 28Z" fill="#2d6a4f"/>
          <path d="M24 21 Q33 17 35 8 Q27 6 24 21Z" fill="#40916c"/>
          <path d="M23 12 Q22 6 23 1 Q25 6 23 12Z" fill="#74c69d"/>
        </svg>
      </div>
      <span class="sidebar-logo-name">
        Safra<em style="font-style:italic; color:#74c69d">Wise</em>
      </span>
    </div>

    <div class="sidebar-user">
      <div class="sidebar-user-role">
        <?= $tipo === 'proprietario' ? '🏡 Proprietário' : '👷 Peão de campo' ?>
      </div>
      <div class="sidebar-user-name"><?= htmlspecialchars($user['nome']) ?></div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Principal</div>

      <a href="?page=dashboard" class="nav-link <?= $pagina_atual === 'dashboard' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
        </svg>
        Dashboard
      </a>

      <a href="?page=talhoes" class="nav-link <?= $pagina_atual === 'talhoes' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
        </svg>
        Talhões
        <span class="badge">12</span>
      </a>

      <a href="?page=insumos" class="nav-link <?= $pagina_atual === 'insumos' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
        </svg>
        Insumos
      </a>

      <div class="nav-section-label">Análises</div>

      <a href="?page=relatorios" class="nav-link">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"/>
        </svg>
        Relatórios
      </a>

      <a href="?page=clima" class="nav-link">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z"/>
        </svg>
        Clima
      </a>

      <?php if ($tipo === 'proprietario'): ?>
      <div class="nav-section-label">Administração</div>

      <a href="?page=equipe" class="nav-link">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
        </svg>
        Equipe
      </a>

      <a href="?page=configuracoes" class="nav-link">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
        </svg>
        Configurações
      </a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <a href="../../public/?page=logout" class="btn-sair">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
        </svg>
        Sair da conta
      </a>
    </div>
  </aside>

  <!-- ════════ CONTEÚDO PRINCIPAL ════════ -->
  <div class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1><?= $saudacao ?>, <?= htmlspecialchars(explode(' ', $user['nome'])[0]) ?></h1>
        <p><?= date('l, d \d\e F \d\e Y') ?> &mdash; Safra <?= date('Y') ?>/<?= date('Y') + 1 ?></p>
      </div>
      <div class="topbar-right">
        <button class="topbar-btn" title="Notificações">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
          </svg>
        </button>
        <button class="topbar-btn" title="Ajuda">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
          </svg>
        </button>
        <div class="topbar-avatar"><?= $iniciais ?></div>
      </div>
    </header>

    <div class="page-body">

      <div class="metrics-grid">
        <div class="metric-card verde">
          <div class="metric-icon verde">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
          </div>
          <div class="metric-label">Talhões ativos</div>
          <div class="metric-value">12</div>
          <div class="metric-delta up">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            +2 nesta safra
          </div>
        </div>

        <div class="metric-card dourado">
          <div class="metric-icon dourado">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
          </div>
          <div class="metric-label">Área total</div>
          <div class="metric-value">847 ha</div>
          <div class="metric-delta up">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            +60 ha vs safra anterior
          </div>
        </div>

        <div class="metric-card azul">
          <div class="metric-icon azul">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/></svg>
          </div>
          <div class="metric-label">Insumos em estoque</div>
          <div class="metric-value">34</div>
          <div class="metric-delta down">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            3 com estoque baixo
          </div>
        </div>

        <div class="metric-card roxo">
          <div class="metric-icon roxo">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
          </div>
          <div class="metric-label">Próxima colheita</div>
          <div class="metric-value">28 dias</div>
          <div class="metric-delta up">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Talhão B3 — Soja
          </div>
        </div>
      </div>

      <div class="content-grid">
        <div style="display:flex; flex-direction:column; gap:20px;">

          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Talhões desta safra</span>
              <a href="?page=talhoes" class="section-action">Ver todos →</a>
            </div>
            <div class="section-body">
              <?php
              $talhoes = [
                ['nome'=>'A1 - Cerradão Norte','cultura'=>'Soja', 'area'=>'120 ha','status'=>'plantado','cor'=>'#40916c'],
                ['nome'=>'B2 - Várzea Leste',  'cultura'=>'Milho','area'=>'85 ha', 'status'=>'colheita','cor'=>'#d4a017'],
                ['nome'=>'B3 - Pasto Novo',    'cultura'=>'Soja', 'area'=>'200 ha','status'=>'plantado','cor'=>'#40916c'],
                ['nome'=>'C1 - Baixada Sul',   'cultura'=>'Trigo','area'=>'60 ha', 'status'=>'preparo', 'cor'=>'#6b8f75'],
                ['nome'=>'D4 - Alto dos Pinheiros','cultura'=>'Soja','area'=>'180 ha','status'=>'plantado','cor'=>'#40916c'],
              ];
              foreach ($talhoes as $t): ?>
              <div class="talhao-row">
                <div class="talhao-dot" style="background:<?= $t['cor'] ?>"></div>
                <div class="talhao-info">
                  <div class="talhao-name"><?= $t['nome'] ?></div>
                  <div class="talhao-meta"><?= $t['cultura'] ?></div>
                </div>
                <div class="talhao-area"><?= $t['area'] ?></div>
                <span class="status-pill <?= $t['status'] ?>">
                  <?= ['plantado'=>'Plantado','colheita'=>'Colheita','preparo'=>'Preparo'][$t['status']] ?>
                </span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Progresso por cultura</span>
            </div>
            <div class="section-body">
              <?php
              $culturas = [
                ['nome'=>'Soja', 'pct'=>68,'cor'=>'#40916c'],
                ['nome'=>'Milho','pct'=>42,'cor'=>'#d4a017'],
                ['nome'=>'Trigo','pct'=>15,'cor'=>'#3b82f6'],
              ];
              foreach ($culturas as $c): ?>
              <div class="progress-item">
                <div class="progress-header">
                  <span class="progress-name"><?= $c['nome'] ?></span>
                  <span class="progress-pct"><?= $c['pct'] ?>%</span>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill" style="width:<?= $c['pct'] ?>%; background:<?= $c['cor'] ?>"></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:20px;">

          <div class="clima-card">
            <div class="clima-local">📍 Ipiranga do Sul, RS</div>
            <div class="clima-temp">24°C</div>
            <div class="clima-desc">Parcialmente nublado</div>
            <div class="clima-stats">
              <div class="clima-stat">
                <span class="clima-stat-label">Umidade</span>
                <span class="clima-stat-val">72%</span>
              </div>
              <div class="clima-stat">
                <span class="clima-stat-label">Vento</span>
                <span class="clima-stat-val">14 km/h</span>
              </div>
              <div class="clima-stat">
                <span class="clima-stat-label">Chuva</span>
                <span class="clima-stat-val">8 mm</span>
              </div>
            </div>
          </div>

          <div class="section-card">
            <div class="section-header">
              <span class="section-title">Atividades recentes</span>
            </div>
            <div class="section-body">
              <?php
              $atividades = [
                ['texto'=>'Aplicação de herbicida no Talhão A1 concluída.','tempo'=>'Há 2h','cor'=>'#40916c'],
                ['texto'=>'Estoque de adubo NPK abaixo do mínimo.','tempo'=>'Ontem','cor'=>'#d4a017'],
                ['texto'=>'Colheita iniciada no Talhão B2 — 42 ha concluídos.','tempo'=>'Ontem','cor'=>'#3b82f6'],
                ['texto'=>'Novo peão adicionado: Carlos Ferreira.','tempo'=>'3 dias atrás','cor'=>'#8b5cf6'],
                ['texto'=>'Relatório mensal de julho gerado.','tempo'=>'5 dias atrás','cor'=>'#6b8f75'],
              ];
              foreach ($atividades as $a): ?>
              <div class="activity-item">
                <div class="activity-dot" style="background:<?= $a['cor'] ?>"></div>
                <div>
                  <div class="activity-text"><?= $a['texto'] ?></div>
                  <div class="activity-time"><?= $a['tempo'] ?></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<script>
function closeToast(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('hide');
  setTimeout(() => el.remove(), 320);
}

document.querySelectorAll('.toast').forEach(function(toast) {
  const duration = parseFloat(getComputedStyle(toast).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(function() { closeToast(toast.id); }, duration);
});
</script>

</body>
</html>