<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafraWise - Cadastro de Proprietário</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/auth.css">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            
            <div class="col-lg-6 bg-brand-gradient d-none d-lg-flex flex-column justify-content-between p-5">
                
                <div style="z-index: 2;">
                    <div class="d-flex align-items-center gap-2 mb-5">
                        <div class="rounded p-2" style="background-color: var(--verde-vivo);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-0-5H20"/></svg>
                        </div>
                        <h4 class="text-white m-0 font-titulo fs-4">SafraWise</h4>
                    </div>
                    
                    <h1 class="text-white font-titulo display-4 fw-bold lh-1 mb-4">
                        Comece a gerenciar<br>sua safra de forma <em class="text-verde-claro">inteligente</em>.
                    </h1>
                    <p class="fs-5" style="color: var(--verde-menta); opacity: 0.8; max-width: 400px;">
                        Crie sua conta de proprietário, acompanhe talhões, controle insumos e tome decisões precisas com dados direto do campo.
                    </p>
                </div>
                
                <div class="d-flex gap-5" style="z-index: 2;">
                    <div>
                        <div class="font-titulo text-verde-claro fs-2 lh-1">100%</div>
                        <div class="text-uppercase fw-bold mt-1" style="color: var(--verde-menta); font-size: 0.75rem; opacity: 0.7;">Controle</div>
                    </div>
                    <div>
                        <div class="font-titulo text-verde-claro fs-2 lh-1">24/7</div>
                        <div class="text-uppercase fw-bold mt-1" style="color: var(--verde-menta); font-size: 0.75rem; opacity: 0.7;">Monitoramento</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-sm-5 bg-white">
                <div class="w-100" style="max-width: 420px;">
                    
                    <div class="text-uppercase fw-bold text-verde-vivo mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Nova Conta</div>
                    <h2 class="font-titulo text-dark mb-4 display-6">Cadastre-se</h2>

                    <form action="index.php?page=store_proprietario" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label text-uppercase fw-bold text-muted mb-1" style="font-size: 0.75rem;">Nome Completo</label>
                            <input type="text" name="nome" class="form-control form-control-lg bg-light" placeholder="João da Silva" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase fw-bold text-muted mb-1" style="font-size: 0.75rem;">CPF / CNPJ</label>
                                <input type="text" name="cpf_cnpj" class="form-control form-control-lg bg-light" placeholder="000.000.000-00" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase fw-bold text-muted mb-1" style="font-size: 0.75rem;">Telefone</label>
                                <input type="text" name="telefone" class="form-control form-control-lg bg-light" placeholder="(00) 00000-0000" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-uppercase fw-bold text-muted mb-1" style="font-size: 0.75rem;">E-mail</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0 bg-light px-0" placeholder="seu@email.com.br" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-uppercase fw-bold text-muted mb-1" style="font-size: 0.75rem;">Senha</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                </span>
                                <input type="password" name="senha" class="form-control border-start-0 bg-light px-0" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-safrawise btn-lg w-100 d-flex align-items-center justify-content-center gap-2 fw-bold">
                            Criar Conta
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </form>

                    <div class="text-center mt-4" style="font-size: 0.875rem; color: var(--texto-suave);">
                        Já tem uma conta? <a href="index.php?page=login" class="text-verde-vivo text-decoration-none fw-bold">Entre no sistema</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>