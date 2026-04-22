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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/safrawise/public/css/safrawise.css">
    <link rel="stylesheet" href="/safrawise/public/css/equipe.css">

    <style>
        /* Ajuste específico para o formulário de perfil */
        .card-perfil {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2ece5;
            box-shadow: 0 4px 12px rgba(15, 35, 24, 0.03);
            max-width: 700px;
        }
        .form-label-custom {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--texto-suave);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .input-safrawise {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e0e9e3;
            transition: 0.2s;
        }
        .input-safrawise:focus {
            border-color: var(--verde-vivo);
            box-shadow: 0 0 0 4px rgba(64, 145, 108, 0.1);
        }
        .helper-text {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<?php if ($toast): ?>
<div class="toast-container" id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
  <div class="toast show <?= $toast['tipo'] ?>" id="toast-main">
    <div class="toast-body d-flex align-items-center">
        <div class="me-3">
            <strong><?= htmlspecialchars($toast['titulo']) ?></strong><br>
            <span class="small"><?= htmlspecialchars($toast['mensagem']) ?></span>
        </div>
        <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="app-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg width="22" height="22" viewBox="0 0 48 48" fill="none">
                    <rect x="4" y="16" width="40" height="24" rx="4" fill="#0f2318" stroke="#74c69d" stroke-width="1.5"/>
                    <path d="M24 38 Q23 30 24 20 Q25 13 23 6" stroke="#52b788" stroke-width="2" stroke-linecap="round" fill="none"/>
                </svg>
            </div>
            <span class="sidebar-logo-name">Safra<em class="fst-italic" style="color:#74c69d">Wise</em></span>
        </div>

        <div class="sidebar-user">
            <div class="sidebar-user-role">🏡 Proprietário</div>
            <div class="sidebar-user-name"><?= htmlspecialchars($user['nome']) ?></div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Principal</div>
            <a href="?page=dashboard" class="nav-link">Dashboard</a>
            <a href="?page=talhoes" class="nav-link">Talhões</a>
            <a href="?page=insumos" class="nav-link">Insumos</a>

            <div class="nav-section-label">Administração</div>
            <a href="?page=equipe" class="nav-link">Equipe</a>
            <a href="?page=configuracoes" class="nav-link active">Configurações</a>
        </nav>

        <div class="sidebar-footer">
            <a href="?page=logout" class="btn-sair">Sair da conta</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1><?= $saudacao ?>, <?= htmlspecialchars(explode(' ', $user['nome'])[0]) ?></h1>
                <p class="text-muted small m-0">Gerencie seus dados de acesso</p>
            </div>
            <div class="topbar-right">
                <div class="topbar-avatar"><?= $iniciais ?></div>
            </div>
        </header>

        <div class="page-body">
            <div class="mb-4">
                <h2 class="font-titulo fs-3 mb-1">Meu Perfil</h2>
                <p class="text-muted small">Altere suas informações pessoais e credenciais de login.</p>
            </div>

            <div class="card-perfil p-4 p-md-5">
                <form action="index.php?page=update_perfil" method="POST">
                    
                    <div class="row">
                        <div class="col-12 mb-4">
                            <label class="form-label-custom">NOME COMPLETO</label>
                            <input type="text" name="nome" class="form-control input-safrawise" 
                                   value="<?= htmlspecialchars($user['nome']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label-custom">E-MAIL DE ACESSO</label>
                            <input type="email" name="email" class="form-control input-safrawise" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label-custom">CPF / CNPJ</label>
                            <input type="text" class="form-control input-safrawise bg-light" 
                                   value="<?= htmlspecialchars($user['cpf_cnpj']) ?>" readonly>
                        </div>

                        <hr class="my-4 opacity-10">

                        <div class="col-12 mb-2">
                            <label class="form-label-custom">NOVA SENHA</label>
                            <input type="password" name="senha" class="form-control input-safrawise" 
                                   placeholder="Deixe em branco para manter a atual">
                            <p class="helper-text">Mínimo de 6 caracteres se desejar alterar.</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn-safrawise-success px-5">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    // Fechamento automático do Toast
    setTimeout(() => {
        const t = document.getElementById('toast-main');
        if(t) t.remove();
    }, 5000);
</script>

</body>
</html>