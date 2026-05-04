<?php
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

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
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
  <?php include 'layouts/ticker.php'; ?>

  <div class="main-content">

    <?php include 'layouts/topbar.php'; ?>

    <div class="page-body">

      <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
          <h2 class="mb-1" style="font-family:'DM Serif Display',Georgia,serif; color:var(--texto-escuro);">
            Colaboradores
          </h2>
          <p class="text-muted mb-0 small">
            Veja e gerencie o acesso dos peões à sua propriedade.
          </p>
        </div>

        <!--
          data-sw-open="modal-cadastro-peao"
          O ModalManager em modal.js captura este atributo
          e abre o modal correspondente ao clicar.
        -->
        <button type="button"
                class="btn btn-success d-flex align-items-center gap-2"
                data-sw-open="modal-cadastro-peao">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Novo Peão
        </button>
      </div>

      <div class="card-table-wrapper">
        <div class="table-responsive">
          <table class="table table-safrawise mb-0">
            <thead>
              <tr>
                <th>Nome do Colaborador</th>
                <th>CPF</th>
                <th>Contato</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($equipe)): ?>
                <tr>
                  <td colspan="4" class="text-center py-5 text-muted text-light">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5"
                         class="mb-3 d-block mx-auto opacity-50">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                      <circle cx="9" cy="7" r="4"/>
                      <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Nenhum colaborador cadastrado ainda.<br>
                    Clique em <strong>"Novo Peão"</strong> para adicionar.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($equipe as $peao): ?>
                  <tr>
                    <td class="fw-medium text-light">
                      <?= htmlspecialchars($peao['nome']) ?>
                    </td>
                    <td class="text-light">
                      <?= htmlspecialchars($peao['cpf_cnpj']) ?>
                    </td>
                    <td class="text-light">
                      <div class="small text-light">
                        <?= !empty($peao['telefone'])
                              ? htmlspecialchars($peao['telefone'])
                              : '<span class="opacity-50">Sem telefone</span>' ?>
                        <br>
                        <?= !empty($peao['email']) ? htmlspecialchars($peao['email']) : '' ?>
                      </div>
                    </td>
                    <td class="text-end">
                      <button class="btn-table-action" title="Editar" type="button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->


<!-- ════════════════════════════════════════════════════════════
     MODAL — Cadastro de Peão
     
     Classe .sw-modal         → estilos da marca (safrawise.css)
     data-sw-reset-on-close   → ModalManager reseta o form ao fechar
     O modal NÃO usa data-bs-* para abrir/fechar: quem cuida disso
     é exclusivamente o ModalManager (modal.js).
════════════════════════════════════════════════════════════ -->
<div class="modal fade sw-modal"
     id="modal-cadastro-peao"
     tabindex="-1"
     aria-labelledby="modal-cadastro-peao-label"
     aria-hidden="true"
     data-sw-reset-on-close>

  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <!-- Header -->
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modal-cadastro-peao-label">
            Adicionar Peão
          </h5>
          <p class="modal-subtitle mb-0">
            Preencha os dados do novo colaborador.
          </p>
        </div>
        <!--
          data-sw-close="modal-cadastro-peao"
          O ModalManager fecha o modal ao clicar neste botão.
        -->
        <button type="button"
                class="btn-close "
                data-sw-close="modal-cadastro-peao"
                aria-label="Fechar">
        </button>
      </div>

      <!-- Body — Formulário -->
      <div class="modal-body">
        <form id="form-cadastro-peao"
              method="POST"
              action="index.php?page=store_peao">

          <!-- Nome -->
          <div class="mb-3">
            <label class="form-label" for="peao-nome">Nome completo</label>
            <input type="text"
                   class="form-control"
                   id="peao-nome"
                   name="nome"
                   placeholder="Ex: Carlos Ferreira"
                   required>
          </div>

          <!-- CPF + Telefone lado a lado -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="peao-cpf">CPF</label>
              <input type="text"
                     class="form-control"
                     id="peao-cpf"
                     name="cpf_cnpj"
                     placeholder="000.000.000-00"
                     required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="peao-telefone">
                Telefone
                <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
              </label>
              <input type="text"
                     class="form-control"
                     id="peao-telefone"
                     name="telefone"
                     placeholder="(00) 00000-0000">
            </div>
          </div>

          <!-- E-mail -->
          <div class="mb-3">
            <label class="form-label" for="peao-email">
              E-mail
              <span class="fw-normal text-muted ms-1" style="font-size:11px;">(opcional)</span>
            </label>
            <div class="input-icon-wrap">
              <span class="form-icon">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M2.5 6.5L10 11l7.5-4.5M3 5h14a1 1 0 011 1v8a1 1 0 01-1 1H3a1 1 0 01-1-1V6a1 1 0 011-1z"
                        stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
              <input type="email"
                     class="form-control"
                     id="peao-email"
                     name="email"
                     placeholder="colaborador@email.com">
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label" for="peao-senha">Senha de acesso</label>
            <div class="input-icon-wrap">
              <span class="form-icon">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="8" width="14" height="10" rx="2"
                        stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M7 8V6a3 3 0 016 0v2"
                        stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
              <input type="password"
                     class="form-control"
                     id="peao-senha"
                     name="senha"
                     placeholder="••••••••"
                     autocomplete="new-password"
                     required>
            </div>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button"
                class="btn-modal-cancel"
                data-sw-close="modal-cadastro-peao">
          Cancelar
        </button>

        <button type="submit"
                form="form-cadastro-peao"
                class="btn btn-success d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/>
            <line x1="22" y1="11" x2="16" y2="11"/>
          </svg>
          Adicionar peão
        </button>
      </div>

    </div><!-- /modal-content -->
  </div><!-- /modal-dialog -->
</div><!-- /modal -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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