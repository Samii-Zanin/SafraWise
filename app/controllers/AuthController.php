<?php
// app/controllers/AuthController.php

class AuthController
{

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /safrawise/public/?page=login");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $tipo  = $_POST['tipo']  ?? 'proprietario';

        // ── Validação básica ────────────────────────────────────
        if (empty($email) || empty($senha)) {
            $_SESSION['toast'] = [
                'tipo'     => 'error',
                'titulo'   => 'Campos obrigatórios',
                'mensagem' => 'Preencha o e-mail e a senha para continuar.',
            ];
            header("Location: /safrawise/public/?page=login");
            exit;
        }

        $user = $this->buscarUsuario($email, $senha, $tipo);

        if (!$user) {
            $_SESSION['toast'] = [
                'tipo'     => 'error',
                'titulo'   => 'Acesso negado',
                'mensagem' => 'E-mail ou senha incorretos. Verifique suas credenciais.',
            ];
            header("Location: /safrawise/public/?page=login");
            exit;
        }

        // ── Login bem-sucedido ───────────────────────────────────
        session_regenerate_id(true);

        $_SESSION['user'] = $user;
        $_SESSION['tipo'] = $tipo;

        $_SESSION['toast'] = [
            'tipo'     => 'success',
            'titulo'   => 'Bem-vindo, ' . explode(' ', $user['nome'])[0] . '!',
            'mensagem' => 'Você entrou como ' . ($tipo === 'proprietario' ? 'proprietário' : 'peão de campo') . '.',
        ];

        header("Location: /safrawise/public/?page=dashboard");
        exit;
    }

    private function buscarUsuario(string $email, string $senha, string $tipo): ?array {
    require_once __DIR__ . '/../../config/Conexao.php';

    $conn = Conexao::getConexao();

    $tabela = ($tipo === 'peao') ? 'peao' : 'proprietario';

    $sql = "SELECT id, nome, email, senha FROM $tabela WHERE email = ? LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro SQL: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || $senha !== $user['senha']) {
        return null;
    }

    return $user;
}
}
