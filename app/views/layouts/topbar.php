<?php
/*
 * layouts/topbar.php
 *
 * Variáveis esperadas (definidas pelo layout da página pai):
 *   $pagina_atual  — string  (ex: 'dashboard', 'equipe')
 *   $saudacao      — string  (ex: 'Bom dia')
 *   $user          — array   (['nome' => '...'])
 *   $iniciais      — string  (ex: 'JF')
 */

$titulos_pagina = [
    'dashboard'    => 'Dashboard',
    'talhoes'      => 'Talhões',
    'insumos'      => 'Insumos',
    'relatorios'   => 'Relatórios',
    'clima'        => 'Clima',
    'equipe'       => 'Equipe',
    'configuracoes' => 'Configurações',
    'produtos_culturas' => 'Produtos e Culturas',
];

$titulo_atual = $titulos_pagina[$pagina_atual] ?? ucfirst($pagina_atual);
$primeiro_nome = explode(' ', $user['nome'])[0];
?>
<header class="topbar">

  <div class="topbar-left">
    <h1><?= htmlspecialchars($titulo_atual) ?></h1>
    <p><?= $saudacao ?>, <?= htmlspecialchars($primeiro_nome) ?> 👋</p>
  </div>

  <div class="d-flex align-items-center gap-2 topbar-right">
    <button class="topbar-btn" title="Notificações" type="button">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
      </svg>
    </button>

    <div class="topbar-avatar" title="<?= htmlspecialchars($user['nome']) ?>">
      <?= htmlspecialchars($iniciais) ?>
    </div>
  </div>

</header>