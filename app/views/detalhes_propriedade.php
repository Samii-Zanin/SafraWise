<?php
$user = $_SESSION['user'];
$tipo = $_SESSION['tipo'];
/** @var array $propriedade */
/** @var array $talhoes */
/** @var float $area_ocupada */

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

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}
$pagina_atual = 'propriedades'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>SafraWise — <?= htmlspecialchars($propriedade['nome']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">
  <style>
      .prop-header-card { background: #0f2318; color: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; }
      .area-progress { height: 10px; border-radius: 10px; background: rgba(255,255,255,0.1); margin: 15px 0; }
      .area-progress-bar { background: #74c69d; border-radius: 10px; transition: 0.5s; }
      .card-table-wrapper { background-color: #f8f8f5; border-radius: 20px; border: 1px solid #e5e5de; overflow: hidden; }
      
      /* Estilo para o selo de posse na tabela */
      .posse-label { font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; padding: 2px 6px; border-radius: 4px; }
      .posse-proprio { background: #e2eece; color: #4b6321; }
      .posse-arrendado { background: #fef3c7; color: #92400e; }
  </style>
</head>
<body>

<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= htmlspecialchars($toast['tipo']) ?>" id="toast-main" style="--toast-duration: 5s">
    <div class="toast-body d-flex justify-content-between align-items-center">
      <div>
        <strong class="toast-title d-block"><?= htmlspecialchars($toast['titulo']) ?></strong>
        <span class="toast-msg small"><?= htmlspecialchars($toast['mensagem']) ?></span>
      </div>
      <button class="btn-close" onclick="closeToast('toast-main')" type="button"></button>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="app-layout">
  <?php include 'layouts/sidebar.php'; ?>
  <?php include 'layouts/ticker.php'; ?>

  <div class="main-content">
    <?php include 'layouts/topbar.php'; ?>

    <div class="page-body">
      
      <div class="prop-header-card">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="badge mb-2" style="background: rgba(116, 198, 157, 0.2); color: #74c69d;">Fazenda Selecionada</span>
            <h1 class="display-6 fw-bold mb-1"><?= htmlspecialchars($propriedade['nome']) ?></h1>
            <p class="opacity-75 mb-0"><?= htmlspecialchars($propriedade['municipio']) ?> - <?= $propriedade['estado'] ?> | <?= htmlspecialchars($propriedade['localizacao']) ?></p>
          </div>
          <a href="index.php?page=propriedades" class="btn btn-outline-light btn-sm">Voltar à lista</a>
        </div>

        <div class="mt-4">
          <div class="d-flex justify-content-between small">
            <span>Área Ocupada: <strong><?= number_format($area_ocupada, 2, ',', '.') ?> ha</strong></span>
            <span>Área Produtiva Total: <strong><?= number_format($propriedade['area_produtiva'], 2, ',', '.') ?> ha</strong></span>
          </div>
          <div class="area-progress">
            <?php $perc = ($propriedade['area_produtiva'] > 0) ? ($area_ocupada / $propriedade['area_produtiva']) * 100 : 0; ?>
            <div class="area-progress-bar" style="width: <?= min($perc, 100) ?>%"></div>
          </div>
          <p class="small mb-0 opacity-50">Disponível para novos talhões: <?= number_format($propriedade['area_produtiva'] - $area_ocupada, 2, ',', '.') ?> ha</p>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="font-titulo fs-4 mb-0">Talhões Registrados</h3>
        <button class="btn btn-success d-flex align-items-center gap-2" data-sw-open="modal-cadastro-talhao">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Novo Talhão
        </button>
      </div>

      <div class="card-table-wrapper">
        <div class="table-responsive">
          <table class="table table-safrawise mb-0">
            <thead>
              <tr>
                <th>Identificação / Posse</th>
                <th>Área (ha)</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($talhoes)): ?>
                <tr><td colspan="4" class="text-center py-5 text-muted">Nenhum talhão criado nesta fazenda.</td></tr>
              <?php else: ?>
                <?php foreach($talhoes as $t): ?>
                  <tr>
                    <td class="fw-medium text-dark">
                        <?= htmlspecialchars($t['nome']) ?><br>
                        <?php if ($t['tipo_posse'] === 'ARRENDADO'): ?>
                            <span class="posse-label posse-arrendado">Arrendado: <?= number_format($t['custo_arrendamento_sacas'], 1, ',', '.') ?> sc/ha</span>
                        <?php else: ?>
                            <span class="posse-label posse-proprio">Próprio</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= number_format($t['area_hectare'], 2, ',', '.') ?> ha</td>
                    <td><span class="badge bg-light text-dark border"><?= $t['status'] ?></span></td>
                    <td class="text-end">
                        
                        <button type="button" class="btn-table-action" title="Editar"
                                onclick="abrirEditarTalhao('<?= $t['id'] ?>', '<?= htmlspecialchars($t['nome'], ENT_QUOTES) ?>', '<?= $t['area_hectare'] ?>', '<?= $t['tipo_posse'] ?>', '<?= $t['custo_arrendamento_sacas'] ?>')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>

                        <a href="index.php?page=delete_talhao&id=<?= $t['id'] ?>&propriedade_id=<?= $propriedade['id'] ?>" 
                           class="btn-table-action text-danger" title="Excluir" 
                           onclick="return confirm('Tem certeza que deseja excluir este talhão?')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </a>

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

<div class="modal fade sw-modal" id="modal-cadastro-talhao" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="index.php?page=store_talhao">
        <input type="hidden" name="propriedade_id" value="<?= $propriedade['id'] ?>">
        
        <div class="modal-header">
           <div>
             <h5 class="modal-title">Novo Talhão</h5>
             <p class="modal-subtitle mb-0">Em <?= htmlspecialchars($propriedade['nome']) ?></p>
           </div>
           <button type="button" class="btn-close" data-sw-close="modal-cadastro-talhao"></button>
        </div>
        
        <div class="modal-body">
           <div class="mb-3">
             <label class="form-label">Nome do Talhão</label>
             <input type="text" name="nome" class="form-control input-safrawise" required>
           </div>
           
           <div class="row g-3 mb-3">
             <div class="col-md-6">
               <label class="form-label">Área (hectares)</label>
               <input type="number" step="0.01" name="area_hectare" min="0.01" placeholder="0.00" class="form-control input-safrawise" required>
             </div>
             <div class="col-md-6">
               <label class="form-label">Tipo de Posse</label>
               <select name="tipo_posse" class="form-select input-safrawise" onchange="toggleCusto(this)" required>
                 <option value="PROPRIO">Próprio</option>
                 <option value="ARRENDADO">Arrendado</option>
               </select>
             </div>
           </div>

           <div class="mb-0 d-none" id="div-custo-cadastro">
             <label class="form-label">Custo do Arrendamento (sacas soja/ha)</label>
             <div class="input-group">
                <input type="number" step="0.1" min="0" name="custo_arrendamento_sacas" class="form-control input-safrawise" value="0.0">
                <span class="input-group-text bg-white border-start-0 text-muted">sc/ha</span>
             </div>
           </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn-modal-cancel" data-sw-close="modal-cadastro-talhao">Cancelar</button>
          <button type="submit" class="btn btn-success">Criar Talhão</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade sw-modal" id="modal-editar-talhao" tabindex="-1" aria-hidden="true" data-sw-reset-on-close>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="form-editar-talhao" method="POST" action="index.php?page=update_talhao">
        <input type="hidden" name="id" id="edit-talhao-id">
        <input type="hidden" name="propriedade_id" value="<?= $propriedade['id'] ?>">
        
        <div class="modal-header">
           <div>
             <h5 class="modal-title">Editar Talhão</h5>
             <p class="modal-subtitle mb-0">Em <?= htmlspecialchars($propriedade['nome']) ?></p>
           </div>
           <button type="button" class="btn-close" data-sw-close="modal-editar-talhao"></button>
        </div>
        
        <div class="modal-body">
           <div class="mb-3">
             <label class="form-label">Nome do Talhão</label>
             <input type="text" name="nome" id="edit-talhao-nome" class="form-control input-safrawise" required>
           </div>

           <div class="row g-3 mb-3">
             <div class="col-md-6">
               <label class="form-label">Área (hectares)</label>
               <input type="number" step="0.01" min="0.01" name="area_hectare" id="edit-talhao-area" class="form-control input-safrawise" required>
             </div>
             <div class="col-md-6">
               <label class="form-label">Tipo de Posse</label>
               <select name="tipo_posse" id="edit-talhao-posse" class="form-select input-safrawise" onchange="toggleCusto(this)" required>
                 <option value="PROPRIO">Próprio</option>
                 <option value="ARRENDADO">Arrendado</option>
               </select>
             </div>
           </div>

           <div class="mb-0 d-none" id="div-custo-edicao">
             <label class="form-label">Custo do Arrendamento (sacas soja/ha)</label>
             <div class="input-group">
                <input type="number" step="0.1" min="0" name="custo_arrendamento_sacas" id="edit-talhao-custo" class="form-control input-safrawise">
                <span class="input-group-text bg-white border-start-0 text-muted">sc/ha</span>
             </div>
           </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn-modal-cancel" data-sw-close="modal-editar-talhao">Cancelar</button>
          <button type="submit" class="btn btn-success">Salvar Alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ModalManager !== 'undefined') {
            ModalManager.init();
        }
    });

    // Função para mostrar/esconder o campo de custo baseado na seleção
    function toggleCusto(selectElement) {
        const form = selectElement.closest('form');
        // Busca a div de custo dentro do formulário atual (cadastro ou edição)
        const divCusto = form.querySelector('[id^="div-custo-"]');
        
        if (selectElement.value === 'ARRENDADO') {
            divCusto.classList.remove('d-none');
        } else {
            divCusto.classList.add('d-none');
        }
    }

    function closeToast(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hide');
        setTimeout(() => el.remove(), 320);
    }

    function abrirEditarTalhao(id, nome, area, posse, custo) {
        document.getElementById('edit-talhao-id').value = id;
        document.getElementById('edit-talhao-nome').value = nome;
        document.getElementById('edit-talhao-area').value = area;
        
        const selectPosse = document.getElementById('edit-talhao-posse');
        selectPosse.value = posse;
        
        const inputCusto = document.getElementById('edit-talhao-custo');
        inputCusto.value = custo;

        // Atualiza a visibilidade do campo de custo ao carregar o modal
        toggleCusto(selectPosse);
        
        if (typeof ModalManager !== 'undefined') {
            ModalManager.open('modal-editar-talhao');
        }
    }
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });
</script>
</body>
</html>