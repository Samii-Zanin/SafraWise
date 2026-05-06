<?php
// Lógica para as iniciais e saudação (mantendo o padrão das outras telas)
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

$pagina_atual = 'configuracoes'; 

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
  <title>SafraWise — Configurações</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
      /* Ajuste específico para o formulário de perfil (Bege/Areia Premium) */
      .card-perfil {
          background-color: #f8f8f5; /* Tom areia claro idêntico à sua tabela */
          border-radius: 20px;
          border: 1px solid #e5e5de;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
          max-width: 750px;
      }
      
      .form-label-custom {
          font-size: 0.75rem;
          font-weight: 700;
          color: #475569; /* Slate escuro para alto contraste */
          text-transform: uppercase;
          letter-spacing: 0.5px;
          margin-bottom: 8px;
          display: block;
      }
      
      .input-safrawise {
          background-color: #ffffff !important;
          color: #0f172a !important; /* Texto escuro e nítido */
          border-radius: 12px;
          padding: 12px 16px;
          border: 1px solid #cbd5e1 !important;
          transition: all 0.2s ease-in-out;
      }
      
      .input-safrawise:focus {
          border-color: #40916c !important; /* Verde assinatura SafraWise */
          box-shadow: 0 0 0 4px rgba(64, 145, 108, 0.15) !important;
          color: #0f172a !important;
      }

      /* Estilo para campos somente-leitura (CPF) */
      .input-safrawise[readonly] {
          background-color: #e2e8f0 !important;
          color: #64748b !important;
          border-color: #cbd5e1 !important;
          cursor: not-allowed;
      }
      
      .helper-text {
          font-size: 0.8rem;
          color: #64748b;
          margin-top: 6px;
      }
  </style>
</head>
<body>

<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= htmlspecialchars($toast['tipo']) ?>" id="toast-main" style="--toast-duration: 5s">
    <div class="toast-icon">
      <?php if ($toast['tipo'] === 'success'): ?>
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
      <?php else: ?>
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
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
  <?php include 'layouts/ticker.php'; ?>

  <div class="main-content">

    <?php include 'layouts/topbar.php'; ?>

    <div class="page-body">
      <div class="mb-4">
        <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
          Meu Perfil
        </h2>
        <p class="text-muted small">
          Altere suas informações pessoais e credenciais de login com segurança.
        </p>
      </div>

      <div class="card-perfil p-4 p-md-5">
        <form action="index.php?page=update_perfil" method="POST">
          
          <div class="row">
            <div class="col-12 mb-4">
              <label class="form-label-custom" for="perfil-nome">Nome completo</label>
              <input type="text" 
                     id="perfil-nome"
                     name="nome" 
                     class="form-control input-safrawise" 
                     value="<?= htmlspecialchars($user['nome']) ?>" 
                     required>
            </div>

            <div class="col-md-6 mb-4">
              <label class="form-label-custom" for="perfil-email">E-mail de acesso</label>
              <input type="email" 
                     id="perfil-email"
                     name="email" 
                     class="form-control input-safrawise" 
                     value="<?= htmlspecialchars($user['email']) ?>" 
                     required>
            </div>

            <div class="col-md-6 mb-4">
              <label class="form-label-custom" for="perfil-documento">CPF / CNPJ</label>
              <input type="text" 
                     id="perfil-documento"
                     class="form-control input-safrawise" 
                     value="<?= htmlspecialchars($user['cpf_cnpj']) ?>" 
                     readonly>
            </div>

            <div class="col-12">
              <hr class="my-3" style="border-color: #e5e5de;">
            </div>

            <div class="col-12 mb-2">
              <label class="form-label-custom" for="perfil-senha">Nova senha</label>
              <input type="password" 
                     id="perfil-senha"
                     name="senha" 
                     class="form-control input-safrawise" 
                     placeholder="Deixe em branco para manter a atual"
                     autocomplete="new-password">
              <p class="helper-text">Mínimo de 6 caracteres se desejar alterar.</p>
            </div>
          </div>

          <div class="mt-4 pt-2">
            <button type="submit" class="btn btn-success d-flex align-items-center gap-2 px-4 py-2.5" style="border-radius: 12px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Salvar Alterações
            </button>
          </div>
        </form>
      </div>
    </div></div></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>

<script>
/* ── Toast auto-close ── */
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