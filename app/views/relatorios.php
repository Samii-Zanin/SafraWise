<?php
$user  = $_SESSION['user'];
$tipo  = $_SESSION['tipo'];

$pagina_atual = 'relatorios';

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

$totalModulos = count($modulos ?? []);

$lucideIcons = [
    'casa'       => 'home',
    'campo'      => 'map',
    'insumo'     => 'flask-conical',
    'estoque'    => 'package',
    'silo'       => 'database',
    'safra'      => 'calendar-range',
    'planta'     => 'leaf',
    'produto'    => 'box',
    'operacao'   => 'wrench',
    'financeiro' => 'banknote',
    'equipe'     => 'users',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafraWise — Relatórios</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/safrawise.css">

  <style>
    /* banner escuro no topo da página — não tem equivalente no sistema */
    .relatorios-banner {
      background: linear-gradient(135deg, var(--verde-escuro) 0%, #1b4332 100%);
      border-radius: var(--bs-border-radius-lg);
      padding: 28px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
      flex-wrap: wrap;
      margin-bottom: 28px;
    }
    .relatorios-banner h2 { font-family: 'DM Serif Display', Georgia, serif; color: #fff; font-size: 22px; margin: 0 0 4px; }
    .relatorios-banner p  { color: #95d5b2; font-size: 13.5px; margin: 0; }
    .banner-badge {
      background: rgba(116,198,157,.18);
      border: 1px solid rgba(116,198,157,.35);
      border-radius: 12px;
      padding: 10px 20px;
      text-align: center;
      white-space: nowrap;
    }
    .banner-badge .num { font-family: 'DM Serif Display', Georgia, serif; font-size: 28px; color: #74c69d; line-height: 1; }
    .banner-badge .txt { font-size: 11px; color: #95d5b2; margin-top: 2px; }

    /* os cards usam .metric-card e .metric-icon do safrawise.css;
       só precisamos do cursor e do lift no hover */
    .modulo-card { cursor: pointer; height: 100%; }
    .modulo-card:hover { transform: translateY(-2px); }

    /* o "laranja" não existe no safrawise.css, os outros (verde/dourado/azul/roxo) já existem */
    .metric-card.laranja::after  { background: #f97316; }
    .metric-icon.laranja { background: rgba(249,115,22,.12); color: #ea580c; }

    .metric-icon svg { width: 20px; height: 20px; }

    /* botão discreto no rodapé de cada card */
    .btn-gerar {
      font-size: 12px;
      font-weight: 600;
      color: var(--verde-vivo);
      background: transparent;
      border: 1.5px solid var(--verde-vivo);
      border-radius: 8px;
      padding: 5px 14px;
      cursor: pointer;
      transition: all .15s;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      white-space: nowrap;
    }
    .btn-gerar:hover { background: var(--verde-vivo); color: #fff; }

    /* grid de checkboxes no modal */
    .col-check-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
      gap: 8px 14px;
    }
    .col-check-item {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid #e2ece5;
      cursor: pointer;
      transition: background .12s, border-color .12s;
      user-select: none;
    }
    .col-check-item:hover   { background: #f0faf3; border-color: #b7dfc4; }
    .col-check-item.marcado { background: #e9f8ef; border-color: var(--verde-vivo); }
    .col-check-item input   { width: 15px; height: 15px; accent-color: var(--verde-vivo); cursor: pointer; flex-shrink: 0; }
    .col-check-item label   { font-size: 13px; cursor: pointer; margin: 0; }

    /* spinner de carregamento no botão PDF */
    .spinner-sm {
      width: 14px; height: 14px;
      border: 2px solid rgba(255,255,255,.4);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .6s linear infinite;
      display: inline-block;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

<?php if ($toast): ?>
<div class="toast-container" id="toast-container">
  <div class="toast <?= htmlspecialchars($toast['tipo']) ?>" id="toast-main" style="--toast-duration:5s">
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
    <button class="toast-close" onclick="closeToast('toast-main')" type="button">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
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

      <div class="relatorios-banner">
        <div>
          <h2>Gerador de Relatórios</h2>
          <p>Selecione um módulo, escolha as colunas e exporte em PDF ou Excel.</p>
        </div>
        <div class="banner-badge">
          <div class="num"><?= $totalModulos ?></div>
          <div class="txt">módulos disponíveis</div>
        </div>
      </div>

      <div class="row g-3">
        <?php foreach ($modulos as $key => $mod):
          $icone = $lucideIcons[$mod['icone']] ?? 'file';
          $cor   = htmlspecialchars($mod['cor']);
        ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
          <div class="card metric-card modulo-card <?= $cor ?>"
               onclick="abrirModal('<?= htmlspecialchars($key) ?>')">
            <div class="card-body d-flex flex-column gap-2 pb-3">
              <div class="metric-icon <?= $cor ?>">
                <i data-lucide="<?= $icone ?>"></i>
              </div>
              <div>
                <div class="fw-semibold" style="font-size:15px; color:var(--texto-escuro)"><?= htmlspecialchars($mod['label']) ?></div>
                <div class="text-muted mt-1" style="font-size:12.5px; line-height:1.4"><?= htmlspecialchars($mod['descricao']) ?></div>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
              <span style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--texto-suave)">
                <?= count($mod['colunas']) ?> colunas
              </span>
              <button type="button" class="btn-gerar"
                      onclick="event.stopPropagation(); abrirModal('<?= htmlspecialchars($key) ?>')">
                <i data-lucide="file-plus" style="width:13px;height:13px"></i>
                Gerar
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</div>


<!-- Modal de seleção de colunas e exportação -->
<div class="modal fade sw-modal" id="modal-relatorio" tabindex="-1"
     aria-labelledby="modal-relatorio-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modal-relatorio-label">Configurar Relatório</h5>
          <p class="modal-subtitle mb-0" id="modal-relatorio-desc">Escolha as colunas para incluir.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
          <span class="small fw-medium text-muted">Colunas:</span>
          <button type="button" class="btn btn-link btn-sm p-0 text-success" onclick="marcarTodos(true)">Selecionar todos</button>
          <button type="button" class="btn btn-link btn-sm p-0 text-success" onclick="marcarTodos(false)">Desmarcar todos</button>
          <span class="ms-auto small text-muted" id="label-contador"></span>
        </div>
        <div class="col-check-grid" id="colunas-container"></div>
      </div>

      <div class="modal-footer gap-2 flex-wrap">
        <button type="button" class="btn-modal-cancel me-auto" data-bs-dismiss="modal">Cancelar</button>

        <button type="button" class="btn btn-success d-flex align-items-center gap-2"
                id="btn-excel" onclick="exportarExcel()">
          <i data-lucide="table-2" style="width:16px;height:16px"></i>
          Exportar Excel
        </button>

        <button type="button" class="btn btn-danger d-flex align-items-center gap-2"
                id="btn-pdf" onclick="exportarPDF()">
          <i data-lucide="file-text" style="width:16px;height:16px"></i>
          Exportar PDF
        </button>
      </div>

    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/modalManager.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.3/dist/jspdf.plugin.autotable.min.js"></script>

<script>
lucide.createIcons();

function closeToast(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = 'none';
}

document.querySelectorAll('.toast').forEach(t => {
  const d = parseFloat(getComputedStyle(t).getPropertyValue('--toast-duration')) * 1000 || 5000;
  setTimeout(() => closeToast(t.id), d);
});

const MODULOS = <?= json_encode($modulosParaJS, JSON_UNESCAPED_UNICODE) ?>;
let moduloAtual = null;

function abrirModal(key) {
  moduloAtual = key;
  const m = MODULOS[key];

  document.getElementById('modal-relatorio-label').textContent = 'Relatório — ' + m.label;
  document.getElementById('modal-relatorio-desc').textContent  = m.descricao;

  const container = document.getElementById('colunas-container');
  container.innerHTML = '';

  Object.entries(m.colunas).forEach(([colKey, colLabel]) => {
    const item = document.createElement('div');
    item.className    = 'col-check-item marcado';
    item.dataset.col  = colKey;

    const cbId = 'chk-' + colKey;
    item.innerHTML =
      `<input type="checkbox" id="${cbId}" value="${colKey}" checked>` +
      `<label for="${cbId}">${colLabel}</label>`;

    const cb = item.querySelector('input');
    cb.addEventListener('change', () => {
      item.classList.toggle('marcado', cb.checked);
      atualizarContador();
    });
    item.addEventListener('click', e => {
      if (e.target.tagName !== 'INPUT') {
        cb.checked = !cb.checked;
        cb.dispatchEvent(new Event('change'));
      }
    });

    container.appendChild(item);
  });

  atualizarContador();
  bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-relatorio')).show();
}

function atualizarContador() {
  const total  = document.querySelectorAll('#colunas-container input').length;
  const checked = document.querySelectorAll('#colunas-container input:checked').length;
  document.getElementById('label-contador').textContent = `${checked} de ${total} selecionadas`;
}

function marcarTodos(estado) {
  document.querySelectorAll('#colunas-container input').forEach(cb => {
    cb.checked = estado;
    cb.closest('.col-check-item').classList.toggle('marcado', estado);
  });
  atualizarContador();
}

function getColunasSelected() {
  return [...document.querySelectorAll('#colunas-container input:checked')].map(cb => cb.value);
}

// CSV via form submit normal — o PHP devolve o arquivo para download
function exportarExcel() {
  const colunas = getColunasSelected();
  if (!colunas.length) { alert('Selecione ao menos uma coluna.'); return; }

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'index.php?page=export_relatorio';

  [['modulo', moduloAtual], ['formato', 'csv'], ...colunas.map(c => ['colunas[]', c])].forEach(([name, value]) => {
    const input = Object.assign(document.createElement('input'), { type: 'hidden', name, value });
    form.appendChild(input);
  });

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

// PDF gerado no browser com jsPDF — busca os dados via JSON para não abrir nova aba
async function exportarPDF() {
  const colunas = getColunasSelected();
  if (!colunas.length) { alert('Selecione ao menos uma coluna.'); return; }

  const btnPdf   = document.getElementById('btn-pdf');
  const btnExcel = document.getElementById('btn-excel');
  btnPdf.disabled = btnExcel.disabled = true;
  btnPdf.innerHTML = '<span class="spinner-sm"></span> Gerando...';

  try {
    const fd = new FormData();
    fd.append('modulo', moduloAtual);
    fd.append('formato', 'json');
    colunas.forEach(c => fd.append('colunas[]', c));

    const resp = await fetch('index.php?page=export_relatorio', { method: 'POST', body: fd });
    const data = await resp.json();
    if (data.error) { alert('Erro: ' + data.error); return; }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const m   = MODULOS[moduloAtual];

    // cabeçalho verde escuro
    doc.setFillColor(15, 35, 24);
    doc.rect(0, 0, 297, 18, 'F');
    doc.setFont('helvetica', 'bold').setFontSize(13).setTextColor(255, 255, 255);
    doc.text('SafraWise — ' + m.label, 14, 12);
    doc.setFont('helvetica', 'normal').setFontSize(9).setTextColor(149, 213, 178);
    doc.text('Gerado em: ' + new Date().toLocaleString('pt-BR'), 283, 12, { align: 'right' });

    doc.autoTable({
      head: [Object.values(data.colunas)],
      body: data.dados.map(row => Object.values(row).map(v => v ?? '—')),
      startY: 22,
      styles:            { fontSize: 8, cellPadding: 3, textColor: [30, 40, 35] },
      headStyles:        { fillColor: [15, 35, 24], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 8.5 },
      alternateRowStyles:{ fillColor: [247, 251, 248] },
      tableLineColor:    [226, 236, 229],
      tableLineWidth:    0.1,
    });

    // número de página em cada folha
    for (let i = 1; i <= doc.internal.getNumberOfPages(); i++) {
      doc.setPage(i);
      doc.setFontSize(8).setTextColor(140, 160, 148);
      doc.text(`Página ${i} de ${doc.internal.getNumberOfPages()}`, doc.internal.pageSize.getWidth() / 2, doc.internal.pageSize.getHeight() - 6, { align: 'center' });
    }

    doc.save(`relatorio_${moduloAtual}_${new Date().toISOString().slice(0, 10)}.pdf`);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-relatorio')).hide();

  } catch (err) {
    alert('Erro ao gerar PDF: ' + err.message);
  } finally {
    btnPdf.disabled = btnExcel.disabled = false;
    btnPdf.innerHTML = '<i data-lucide="file-text" style="width:16px;height:16px"></i> Exportar PDF';
    lucide.createIcons(); // re-processa o ícone que foi sobrescrito pelo innerHTML
  }
}
</script>

</body>
</html>
