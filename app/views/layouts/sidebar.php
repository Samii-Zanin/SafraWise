<?php
// ════════ BLINDAGEM DE ESCOPO ════════
// Busca SEMPRE a credencial real direto da sessão usando nomes de variáveis exclusivos.
// Isso evita conflitos com a variável $tipo gerada pelos alertas (Toasts).
$sessao_user  = $_SESSION['user'] ?? ['nome' => 'Usuário'];
$sessao_tipo  = $_SESSION['tipo'] ?? 'peao';
$pagina_atual = $pagina_atual ?? '';
?>

<aside class="sidebar">

 <div class="sidebar-logo">
    <div class="sidebar-logo-icon" style="background-color: #ffffff; border-radius: 8px; padding: 4px; display: flex; align-items: center; justify-content: center;">
      <img src="resources/images/SafrawiseRaw.png" alt="Logo SafraWise" style="width: 70px; height: auto;">
    </div>
    <span class="sidebar-logo-name">
      Safra<em style="font-style:italic; color:#74c69d">Wise</em>
    </span>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-user-role">
      <?= $sessao_tipo === 'proprietario' ? '🏡 Proprietário' : '👷 Peão de campo' ?>
    </div>
    <div class="sidebar-user-name"><?= htmlspecialchars($sessao_user['nome']) ?></div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Principal</div>

    <a href="?page=dashboard" class="sw-nav-link <?= $pagina_atual === 'dashboard' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
      </svg>
      Dashboard
    </a>
    <a href="?page=safras" class="sw-nav-link <?= $pagina_atual === 'safras' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M256 40 C220 75 215 120 256 160 C297 120 292 75 256 40Z M210 120 C170 110 145 130 150 180 C195 185 220 165 210 120Z M302 120 C292 165 317 185 362 180 C367 130 342 110 302 120Z M210 190 C170 180 145 200 150 250 C195 255 220 235 210 190Z M302 190 C292 235 317 255 362 250 C367 200 342 180 302 190Z M210 260 C170 250 145 270 150 320 C195 325 220 305 210 260Z M302 260 C292 305 317 325 362 320 C367 270 342 250 302 260Z M248 160 L248 360 Q248 370 256 370 Q264 370 264 360 L264 160Z M85 300 Q160 285 230 330 Q170 360 120 420 Q80 380 85 300Z M95 350 Q155 360 190 395 M282 330 Q352 285 427 300 Q432 380 392 420 Q342 360 282 330Z M322 395 Q357 360 417 350 M70 370 Q170 340 256 410 Q342 340 442 370 L442 405 Q342 375 256 445 Q170 375 70 405Z"/></svg>
      Safras
    </a>

    <a href="?page=estoques" class="sw-nav-link <?= $pagina_atual === 'estoques' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
      </svg>
      Estoques
    </a>

    <div class="nav-section-label">Análises</div>

    <a href="?page=relatorios" class="sw-nav-link <?= $pagina_atual === 'relatorios' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"/>
      </svg>
      Relatórios
    </a>

    <a href="?page=clima" class="sw-nav-link <?= $pagina_atual === 'clima' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z"/>
      </svg>
      Clima
    </a>

    <?php if ($sessao_tipo === 'proprietario'): ?>
      <div class="nav-section-label">Administração</div>

      <a href="?page=propriedades" class="sw-nav-link <?= $pagina_atual === 'propriedades' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        Propriedades
      </a>

    <a href="?page=equipe" class="sw-nav-link <?= $pagina_atual === 'equipe' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
      </svg>
      Equipe
    </a>
    <a href="?page=insumos" class="sw-nav-link <?= $pagina_atual === 'insumos' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
  <path d="M6 1C5.4 1 5 1.4 5 2v1c0 .3.1.5.3.7L6 5v11c0 1.1.9 2 2 2h4c1.1 0 2-.9 2-2V5l.7-1.3c.2-.2.3-.4.3-.7V2c0-.6-.4-1-1-1H6zm1 2h6v.5L12.4 5H7.6L7 3.5V3zm1 4h4v1H8V7zm0 3c1.2-1.5 2.8-1.5 4 0-.3 1.2-1.2 2-2 2s-1.7-.8-2-2zm-1 5h6v1H7v-1z"/>
</svg>
      Insumos
    </a>
    <a href="?page=produtos_culturas" class="sw-nav-link <?= $pagina_atual === 'produtos_culturas' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
    <path d="M9 8a1 1 0 012 0v10a1 1 0 01-2 0V8z"/>
    <path d="M10 14C8 14 4 12 4 9c0-3 3-5 6-2v7z"/>
    <path d="M10 10c2 0 6-2 6-5 0-3-3-5-6-2v7z"/>
  </svg>
      Produtos e Culturas
    </a>

    <a href="?page=silos" class="sw-nav-link <?= $pagina_atual === 'silos' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
    <path d="M5 8A5 5 0 0115 8H5z"/>
    <path d="M5 8h10v8a2 2 0 01-2 2H7a2 2 0 01-2-2V8z"/>
  </svg>
      Silos
    </a>

      <a href="?page=configuracoes" class="sw-nav-link <?= $pagina_atual === 'configuracoes' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
        </svg>
        Configurações
      </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="../../public/?page=logout" class="btn-sair">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
      </svg>
      Sair da conta
    </a>
  </div>

</aside>