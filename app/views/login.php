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
  <title>SafraWise — Acesse sua conta</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
  
  <style>
    .input-icon-wrap {
      position: relative;
    }
    .pe-5-custom {
      padding-right: 45px !important;
    }
    .toggle-password-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      border: 0;
      background: transparent;
      color: #64748b;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
      cursor: pointer;
    }
    .toggle-password-btn:hover {
      color: var(--verde-vivo, #40916c);
    }
  </style>
</head>
<body>

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
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 00-1.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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
        Gestão<br>
        <em>inteligente</em><br>
        da sua safra.
      </h2>
      <p class="brand-sub">
        Acompanhe talhões, controle insumos e tome decisões com dados precisos direto do campo.
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

  <div class="login-form-panel">
    <div class="login-box">

      <p class="login-eyebrow">Bem-vindo de volta</p>
      <h1 class="login-title">Entre na sua conta</h1>

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

      <form method="POST" action="../public/?page=auth">
        <input type="hidden" name="tipo" id="tipo" value="proprietario">

        <div class="mb-3">
          <label class="form-label" for="cpf">CPF</label>
          <div class="input-icon-wrap">
            <span class="form-icon">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M3 6a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V6z" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 9h3M7 12h6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <input class="form-control form-control-lg text-black"
                   type="text"
                   id="cpf"
                   name="cpf"
                   placeholder="000.000.000-00"
                   maxlength="14"
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
            <input class="form-control form-control-lg text-black pe-5-custom"
                   type="password"
                   id="senha"
                   name="senha"
                   placeholder="••••••••"
                   autocomplete="current-password"
                   required>
            <button type="button" class="toggle-password-btn" id="btn-toggle-password" onclick="togglePasswordVisibility()" title="Mostrar senha">
              <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-7 0-11-7-11-7a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
          <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>
          Entrar no sistema
        </button>
      </form>

      <p class="text-center mt-4 small" style="color: var(--texto-suave);">
        Não tem uma conta?
        <a href="../../public/?page=cadastro_proprietario" class="fw-semibold text-decoration-none" style="color: var(--verde-vivo);">
          Cadastre-se grátis
        </a>
      </p>

      <p class="text-center small" style="font-size: 12px; color: var(--texto-suave); opacity: 0.8;">
        Problemas para acessar?
        <a href="#" class="text-decoration-underline" style="color: var(--texto-suave);">Fale com o suporte</a>
      </p>

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

document.querySelectorAll('.toast').forEach(toast => {
  const duration = parseFloat(getComputedStyle(toast).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(toast.id), duration);
});

/* ── MÁSCARA EXCLUSIVA E ESTRITA PARA CPF ── */
const inputCpf = document.getElementById('cpf');

inputCpf.addEventListener('input', function (e) {
    let value = e.target.value;
    
    // Remove qualquer caractere que não seja número
    value = value.replace(/\D/g, "");
    
    // Força o limite estrito de 11 dígitos numéricos
    if (value.length > 11) {
        value = value.slice(0, 11);
    }
    
    // Monta a máscara estrutural posicional do CPF (000.000.000-00)
    if (value.length > 9) {
        value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2})$/, "$1.$2.$3-$4");
    } else if (value.length > 6) {
        value = value.replace(/^(\d{3})(\d{3})(\d{1,3})$/, "$1.$2.$3");
    } else if (value.length > 3) {
        value = value.replace(/^(\d{3})(\d{1,3})$/, "$1.$2");
    }
    
    e.target.value = value;
});

/* ── CONTROLE DE VISUALIZAÇÃO DA SENHA ── */
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('senha');
    const eyeIconContainer = document.getElementById('btn-toggle-password');
    
    const eyeOpenSVG = `
      <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
        <circle cx="12" cy="12" r="3"></circle>
      </svg>
    `;
    
    const eyeClosedSVG = `
      <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-7 0-11-7-11-7a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
        <line x1="1" y1="1" x2="23" y2="23"></line>
      </svg>
    `;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIconContainer.innerHTML = eyeOpenSVG;
        eyeIconContainer.setAttribute('title', 'Esconder senha');
    } else {
        passwordInput.type = 'password';
        eyeIconContainer.innerHTML = eyeClosedSVG;
        eyeIconContainer.setAttribute('title', 'Mostrar senha');
    }
}
</script>

</body>
</html>