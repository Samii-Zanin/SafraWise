<?php
/*
 * layouts/topbar.php
 *
 * Componente Autônomo — Gerencia internamente as informações do usuário logado.
 * Variável externa esperada (definida pela página pai):
 * $pagina_atual  — string  (ex: 'dashboard', 'talhoes')
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Recupera o usuário da sessão de forma segura
$user_session = $_SESSION['user'] ?? ['nome' => 'Usuário'];

// 2. Calcula as iniciais do avatar dinamicamente para não precisar passar pela View
$iniciais_dinamicas = strtoupper(substr($user_session['nome'], 0, 1));
if (str_contains($user_session['nome'], ' ')) {
    $partes_nome = explode(' ', $user_session['nome']);
    $iniciais_dinamicas = strtoupper($partes_nome[0][0] . end($partes_nome)[0]);
}

// 3. Calcula a saudação baseada no fuso horário correto de Brasília
$saudacao_dinamica = (function () {
    date_default_timezone_set('America/Sao_Paulo'); 
    $h = (int) date('H');
    return match (true) {
        $h < 12 => 'Bom dia',
        $h < 18 => 'Boa tarde',
        default => 'Boa noite',
    };
})();

$titulos_pagina = [
    'dashboard'         => 'Dashboard',
    'talhoes'           => 'Talhões',
    'insumos'           => 'Insumos',
    'relatorios'        => 'Relatórios',
    'clima'             => 'Clima',
    'equipe'            => 'Equipe',
    'configuracoes'     => 'Configurações',
    'produtos_culturas' => 'Produtos e Culturas',
];

// Fallback caso a variável de página não venha definida
$pagina_ativa = $pagina_atual ?? 'dashboard';

$titulo_atual = $titulos_pagina[$pagina_ativa] ?? ucfirst($pagina_ativa);
$primeiro_nome = explode(' ', $user_session['nome'])[0];
?>
<header class="topbar">

  <div class="topbar-left">
    <h1><?= htmlspecialchars($titulo_atual) ?></h1>
    <p><?= $saudacao_dinamica ?>, <?= htmlspecialchars($primeiro_nome) ?> 👋</p>
  </div>

  <div class="d-flex align-items-center gap-2 topbar-right">
    <button class="topbar-btn" title="Notificações" type="button">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
      </svg>
    </button>

    <div class="topbar-avatar" title="<?= htmlspecialchars($user_session['nome']) ?>">
      <?= htmlspecialchars($iniciais_dinamicas) ?>
    </div>
  </div>

</header>