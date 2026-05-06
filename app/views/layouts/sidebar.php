<aside class="sidebar">

  <div class="sidebar-logo">
    <div class="sidebar-logo-icon">
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
    <span class="sidebar-logo-name">
      Safra<em style="font-style:italic; color:#74c69d">Wise</em>
    </span>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-user-role">
      <?= $tipo === 'proprietario' ? '🏡 Proprietário' : '👷 Peão de campo' ?>
    </div>
    <div class="sidebar-user-name"><?= htmlspecialchars($user['nome']) ?></div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Principal</div>

    <a href="?page=dashboard" class="sw-nav-link <?= $pagina_atual === 'dashboard' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
      </svg>
      Dashboard
    </a>

    <a href="?page=talhoes" class="sw-nav-link <?= $pagina_atual === 'talhoes' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
      </svg>
      Talhões
      <span class="sw-nav-badge">12</span>
    </a>

    <a href="?page=insumos" class="sw-nav-link <?= $pagina_atual === 'insumos' ? 'active' : '' ?>">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
      </svg>
      Insumos
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

    <?php if ($tipo === 'proprietario'): ?>
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