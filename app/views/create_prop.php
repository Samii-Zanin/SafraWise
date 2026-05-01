<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
  <title>SafraWise — Criar conta</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

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

<div class="login-page">

  <div class="login-brand">
    <div class="brand-grid"></div>

    <div class="brand-top">
      <div class="brand-logo">
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
        Comece a gerenciar<br>
        sua safra de forma<br>
        <em>inteligente.</em>
      </h2>
      <p class="brand-sub">
        Crie sua conta, acompanhe talhões, controle insumos e tome decisões com dados direto do campo.
      </p>
    </div>

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
        <div class="stat-num">100%</div>
        <div class="stat-label">Controle</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">24/7</div>
        <div class="stat-label">Monitoramento</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">99.8%</div>
        <div class="stat-label">Uptime</div>
      </div>
    </div>
  </div>

  <div class="login-form-panel">
    <div class="login-box">

      <p class="login-eyebrow">Nova conta</p>
      <h1 class="login-title">Cadastre-se</h1>

      <form action="index.php?page=store_proprietario" method="POST">

        <div class="mb-3">
          <label class="form-label" for="nome">Nome Completo</label>
          <input type="text"
                 class="form-control form-control-lg text-light"
                 id="nome"
                 name="nome"
                 placeholder="João da Silva"
                 required>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label" for="cpf_cnpj">CPF / CNPJ</label>
            <input type="text"
                   class="form-control form-control-lg text-light"
                   id="cpf_cnpj"
                   name="cpf_cnpj"
                   placeholder="000.000.000-00"
                   required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="telefone">Telefone</label>
            <input type="text"
                   class="form-control form-control-lg text-light"
                   id="telefone"
                   name="telefone"
                   placeholder="(00) 00000-0000">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="email">E-mail</label>
          <div class="input-icon-wrap">
            <span class="form-icon">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M2.5 6.5L10 11l7.5-4.5M3 5h14a1 1 0 011 1v8a1 1 0 01-1 1H3a1 1 0 01-1-1V6a1 1 0 011-1z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <input type="email"
                   class="form-control form-control-lg text-light"
                   id="email"
                   name="email"
                   placeholder="seu@email.com.br"
                   required>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label" for="senha">Senha</label>
          <div class="input-icon-wrap">
            <span class="form-icon">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="8" width="14" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 8V6a3 3 0 016 0v2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <input type="password"
                   class="form-control form-control-lg text-light"
                   id="senha"
                   name="senha"
                   placeholder="••••••••"
                   autocomplete="new-password"
                   required>
          </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
          Criar minha conta
          <svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <line x1="4" y1="10" x2="16" y2="10"/>
            <polyline points="11 5 16 10 11 15"/>
          </svg>
        </button>

      </form>

      <p class="text-center mt-4 small" style="color: var(--texto-suave);">
        Já tem uma conta?
        <a href="index.php?page=login" class="fw-semibold text-decoration-none" style="color: var(--verde-vivo);">
          Entre no sistema
        </a>
      </p>

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

document.querySelectorAll('.toast').forEach(toast => {
  const duration = parseFloat(getComputedStyle(toast).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(toast.id), duration);
});
</script>

</body>
</html>