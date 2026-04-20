<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
};
if (isset($_SESSION['user'])) {
    header("Location: /public/?page=dashboard");
    exit;
}

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
  <title>SafraWise — Acesse sua conta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
</head>
<body>

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

<div class="login-page">

  <!-- ── PAINEL ESQUERDO – Branding ── -->
  <div class="login-brand">
    <div class="brand-grid"></div>

    <div class="brand-top">
      <div class="brand-logo">
        <!-- LOGO NOVA: computador + planta -->
        <div class="brand-logo-icon">
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
        <span class="brand-logo-name">
          Safra<em style="font-style:italic; color:var(--verde-claro)">Wise</em>
        </span>
      </div>

      <h2 class="brand-headline">
        Gestão<br>
        <em>inteligente</em><br>
        da sua safra.
      </h2>
      <p class="brand-sub">
        Acompanhe talhões, controle insumos e tome decisões com dados precisos direto do campo.
      </p>
    </div>

    <!-- Planta decorativa grande -->
    <svg class="brand-plant" width="320" height="380" viewBox="0 0 320 380" fill="none">
      <path d="M160 370 Q158 300 162 220 Q164 160 155 80" stroke="#74c69d" stroke-width="3" stroke-linecap="round" fill="none"/>
      <path d="M158 200 Q110 180 88 130 Q120 118 158 200Z" fill="#40916c"/>
      <path d="M162 160 Q210 135 230 85 Q200 78 162 160Z" fill="#52b788"/>
      <path d="M156 260 Q118 248 98 210 Q126 202 156 260Z" fill="#2d6a4f"/>
      <path d="M164 230 Q205 212 220 178 Q196 172 164 230Z" fill="#40916c"/>
      <path d="M155 100 Q130 88 122 58 Q144 54 155 100Z" fill="#74c69d"/>
      <ellipse cx="160" cy="372" rx="50" ry="8" fill="#1a3a28"/>
    </svg>

    <div class="brand-stats">
      <div class="stat-item">
        <div class="stat-num">2.4k</div>
        <div class="stat-label">Produtores</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">18M ha</div>
        <div class="stat-label">Monitorados</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">99.8%</div>
        <div class="stat-label">Uptime</div>
      </div>
    </div>
  </div>

  <!-- ── PAINEL DIREITO – Formulário ── -->
  <div class="login-form-panel">
    <div class="login-box">
      <p class="login-eyebrow">Bem-vindo de volta</p>
      <h1 class="login-title">Entre na sua conta</h1>

      <!-- Toggle tipo de acesso -->
      <div class="access-toggle">
        <button type="button" class="access-btn active" id="btn-prop" onclick="setTipo('proprietario')">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
          </svg>
          Proprietário
        </button>
        <button type="button" class="access-btn" id="btn-peao" onclick="setTipo('peao')">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
          </svg>
          Peão
        </button>
      </div>

      <!-- Formulário -->
      <form method="POST" action="../../public/?page=auth">
        <input type="hidden" name="tipo" id="tipo" value="proprietario">

        <div class="form-group">
          <label class="form-label" for="email">E-mail ou CPF</label>
          <span class="form-icon">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M2.5 6.5L10 11l7.5-4.5M3 5h14a1 1 0 011 1v8a1 1 0 01-1 1H3a1 1 0 01-1-1V6a1 1 0 011-1z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <input class="form-input" type="text" id="email" name="email" 
          placeholder="seu@email.com.br ou 000.000.000-00" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="senha">Senha</label>
          <span class="form-icon">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="8" width="14" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M7 8V6a3 3 0 016 0v2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <input class="form-input" type="password" id="senha" name="senha"
            placeholder="••••••••" autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn-login">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>
          Entrar no sistema
        </button>
      </form>

      <div class="login-footer">
        Não tem uma conta? <a href="../../public/?page=cadastro_proprietario">Cadastre-se grátis</a>
      </div>

      <div class="login-footer" style="margin-top: 12px; font-size: 12px; opacity: 0.8;">
        Problemas para acessar? <a href="#" style="color: var(--texto-suave); text-decoration: underline;">Fale com o suporte</a>
      </div>

    </div> </div> ```
    </div>
  </div>

</div>

<script>
function setTipo(tipo) {
  document.getElementById('tipo').value = tipo;
  document.getElementById('btn-prop').classList.toggle('active', tipo === 'proprietario');
  document.getElementById('btn-peao').classList.toggle('active', tipo === 'peao');
}

function closeToast(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('hide');
  setTimeout(() => el.remove(), 320);
}

// Auto-fechar após a duração da barra de progresso
document.querySelectorAll('.toast').forEach(function(toast) {
  const duration = parseFloat(getComputedStyle(toast).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(function() {
    closeToast(toast.id);
  }, duration);
});
</script>

</body>
</html>