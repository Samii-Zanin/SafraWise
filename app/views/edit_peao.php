<?php
// Reutiliza a lógica de saudação e iniciais do seu dashboard
$user = $_SESSION['user'];
$tipo = $_SESSION['tipo'];
// ... (seu código de iniciais e saudação aqui ou use o que já definimos)
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>SafraWise — Editar Colaborador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/safrawise/public/css/safrawise.css">
    <link rel="stylesheet" href="/safrawise/public/css/equipe.css">
</head>
<body>

<div class="app-layout">
    <main class="main-content">
        <header class="topbar">
            <h1>Editar Colaborador</h1>
            <div class="topbar-avatar"><?= $iniciais ?></div>
        </header>

        <div class="page-body">
            <div class="mb-4">
                <h2 class="font-titulo fs-3">Atualizar Dados</h2>
                <p class="text-muted small">Modificando as informações de: <strong><?= htmlspecialchars($peao['nome']) ?></strong></p>
            </div>

            <div class="card-table-wrapper p-4" style="max-width: 700px;">
                <form action="index.php?page=update_peao" method="POST">
                    <input type="hidden" name="id" value="<?= $peao['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold small">NOME COMPLETO</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($peao['nome']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">CPF / Identificação</label>
                            <input type="text" name="cpf_cnpj" class="form-control" value="<?= htmlspecialchars($peao['cpf_cnpj']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">TELEFONE</label>
                            <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($peao['telefone']) ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">E-MAIL (OPCIONAL)</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($peao['email']) ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-safrawise-success">Salvar Alterações</button>
                        <a href="index.php?page=equipe" class="btn btn-light" style="border-radius: 12px;">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>