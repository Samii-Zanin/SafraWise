<?php
require_once '../app/helpers/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header("Location: /public/?page=dashboard");
    exit;
}

$toast = flashToast();
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

<?php include 'layouts/toast.php'; ?>

<div class="login-page">

  <div class="login-brand" style="display: flex; flex-direction: column; padding: 48px; background: linear-gradient(135deg, #0f2318 0%, #1a3a28 100%); position: relative; overflow: hidden;">
    
    <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(116, 198, 157, 0.1) 0%, transparent 70%); border-radius: 50%;"></div>
    
    <div class="brand-top" style="position: relative; z-index: 2;">
      <div class="brand-logo" style="display: flex; align-items: center; gap: 12px; margin-bottom: 60px;">
        <div class="brand-logo-icon" style="background-color: #ffffff; border-radius: 8px; padding: 6px; display: flex; align-items: center; justify-content: center;">
          <img src="resources/images/SafrawiseRaw.png" alt="Logo SafraWise" style="width: 80px; height: auto;">
        </div>
        <span class="brand-logo-name" style="font-size: 1.6rem; font-weight: 600; color: #ffffff; letter-spacing: 0.5px;">
          Safra<em style="font-style:italic; color:#74c69d">Wise</em>
        </span>
      </div>

      <h2 class="brand-headline" style="font-size: 3.2rem; font-family: 'DM Serif Display', serif; line-height: 1.1; margin-bottom: 24px; color: #ffffff;">
        Gestão<br>
        <em style="color: #74c69d;">inteligente</em><br>
        da sua safra.
      </h2>
      <p class="brand-sub" style="font-size: 1.1rem; color: #e2e8f0; max-width: 420px; line-height: 1.6; opacity: 0.9;">
        Acompanhe talhões, controle insumos e tome decisões com dados precisos direto do campo. Tudo em um só lugar.
      </p>
    </div>

    <svg class="brand-plant" width="320" height="380" viewBox="0 0 320 380" fill="none" style="align-self: center; margin-top: auto; z-index: 2; max-width: 100%; height: auto;">
      <path d="M160 370 Q158 300 162 220 Q164 160 155 80" stroke="#74c69d" stroke-width="3" stroke-linecap="round" fill="none"/>
      <path d="M158 200 Q110 180 88 130 Q120 118 158 200Z" fill="#40916c"/>
      <path d="M162 160 Q210 135 230 85 Q200 78 162 160Z" fill="#52b788"/>
      <path d="M156 260 Q118 248 98 210 Q126 202 156 260Z" fill="#2d6a4f"/>
      <path d="M164 230 Q205 212 220 178 Q196 172 164 230Z" fill="#40916c"/>
      <path d="M155 100 Q130 88 122 58 Q144 54 155 100Z" fill="#74c69d"/>
      <ellipse cx="160" cy="372" rx="50" ry="8" fill="#1a3a28"/>
    </svg>

  </div>

  <div class="login-form-panel">
    <div class="login-box">

      <p class="login-eyebrow">Nova conta</p>
      <h1 class="login-title">Cadastre-se</h1>

      <form action="index.php?page=store_proprietario" method="POST">

        <div class="mb-3">
          <label class="form-label" for="nome">Nome Completo</label>
          <input type="text"
                 class="form-control form-control-lg text-black"
                 id="nome"
                 name="nome"
                 placeholder="João da Silva"
                 required>
        </div>
        
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label" for="cpf_cnpj">CPF</label>
            <input type="text"
                   class="form-control form-control-lg text-black"
                   id="cpf_cnpj"
                   name="cpf_cnpj"
                   placeholder="000.000.000-00"
                   maxlength="14"
                   required>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="telefone">Telefone</label>
            <input type="text"
                   class="form-control form-control-lg text-black"
                   id="telefone"
                   name="telefone"
                   placeholder="(00) 00000-0000"
                   maxlength="15">
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
                   class="form-control form-control-lg text-black"
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
                   class="form-control form-control-lg text-black pe-5-custom"
                   id="senha"
                   name="senha"
                   placeholder="••••••••"
                   autocomplete="new-password"
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

/* ── MÁSCARA EXCLUSIVA PARA CPF ── */
const inputCpf = document.getElementById('cpf_cnpj');
inputCpf.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, "");
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 9) {
        value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2})$/, "$1.$2.$3-$4");
    } else if (value.length > 6) {
        value = value.replace(/^(\d{3})(\d{3})(\d{1,3})$/, "$1.$2.$3");
    } else if (value.length > 3) {
        value = value.replace(/^(\d{3})(\d{1,3})$/, "$1.$2");
    }
    e.target.value = value;
});

/* ── MÁSCARA DINÂMICA PARA TELEFONE (8 OU 9 DÍGITOS) ── */
const inputTelefone = document.getElementById('telefone');
inputTelefone.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, "");
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 10) {
        // Formato Celular: (XX) XXXXX-XXXX
        value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
    } else if (value.length > 6) {
        // Formato Fixo: (XX) XXXX-XXXX
        value = value.replace(/^(\d{2})(\d{4})(\d{1,4})$/, "($1) $2-$3");
    } else if (value.length > 2) {
        value = value.replace(/^(\d{2})(\d{1,4})$/, "($1) $2");
    } else if (value.length > 0) {
        value = value.replace(/^(\d{1,2})$/, "($1");
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