<?php
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

$pagina_atual = 'equipe'; 

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
  <title>SafraWise — Equipe</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <link rel="stylesheet" href="../../public/css/safrawise.css">
</head>
<body>

<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= $toast['tipo'] ?>" id="toast-main" style="--toast-duration: 5s">
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
    <button class="toast-close" onclick="closeToast('toast-main')">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
  </div>
</div>
<?php endif; ?>

<div class="app-layout">

  <aside class="sidebar">

    <div class="sidebar-logo">
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
        Safra<em class="fst-italic" style="color:#74c69d">Wise</em>
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
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
        Dashboard
      </a>

      <a href="?page=talhoes" class="nav-link <?= $pagina_atual === 'talhoes' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
        Talhões
      </a>

      <a href="?page=insumos" class="nav-link <?= $pagina_atual === 'insumos' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/></svg>
        Insumos
      </a>

      <div class="nav-section-label">Análises</div>

      <a href="?page=relatorios" class="nav-link <?= $pagina_atual === 'relatorios' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"/></svg>
        Relatórios
      </a>

      <?php if ($tipo === 'proprietario'): ?>
      <div class="nav-section-label">Administração</div>

      <a href="?page=equipe" class="nav-link <?= $pagina_atual === 'equipe' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
        Equipe
      </a>

      <a href="?page=configuracoes" class="nav-link <?= $pagina_atual === 'configuracoes' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
        Configurações
      </a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <a href="../../public/?page=logout" class="btn-sair">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
        Sair da conta
      </a>
    </div>
  </aside>

  <div class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1><?= $saudacao ?>, <?= htmlspecialchars(explode(' ', $user['nome'])[0]) ?></h1>
        <p>Gerenciamento de Equipe</p>
      </div>
      <div class="topbar-right">
        <div class="topbar-avatar"><?= $iniciais ?></div>
      </div>
    </header>

    <div class="page-body">
      
      <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
              <h2 class="font-titulo text-dark mb-1 fs-3">Colaboradores</h2>
              <p class="text-muted mb-0 small">Veja e gerencie o acesso dos peões à sua propriedade.</p>
          </div>
          
          <a href="index.php?page=cadastro_peao" class="btn-safrawise-success">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
              Novo Peão
          </a>
      </div>

      <div class="card-table-wrapper">
          <div class="table-responsive">
              <table class="table table-safrawise mb-0">
                  <thead>
                      <tr>
                          <th class="border-0">Nome do Colaborador</th>
                          <th class="border-0">CPF</th>
                          <th class="border-0">Contato</th>
                          <th class="border-0 text-end">Ações</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (empty($equipe)): ?>
                          <tr>
                              <td colspan="4" class="text-center py-5 text-muted">
                                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3 opacity-50"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg><br>
                                  Nenhum colaborador cadastrado ainda.<br>
                                  Clique em <b>"Novo Peão"</b> para adicionar.
                              </td>
                          </tr>
                      <?php else: ?>
                          <?php foreach ($equipe as $peao): ?>
                              <tr>
                                  <td class="fw-medium text-dark">
                                      <?= htmlspecialchars($peao['nome']) ?>
                                  </td>
                                  <td class="text-muted">
                                      <?= htmlspecialchars($peao['cpf_cnpj']) ?>
                                  </td>
                                  <td class="text-muted">
                                      <div class="small">
                                          <?= !empty($peao['telefone']) ? htmlspecialchars($peao['telefone']) : '<span class="opacity-50">Sem telefone</span>' ?><br>
                                          <?= !empty($peao['email']) ? htmlspecialchars($peao['email']) : '' ?>
                                      </div>
                                  </td>
                                  <td class="text-end">
                                      <button class="btn-table-action" title="Editar">
                                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                      </button>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
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