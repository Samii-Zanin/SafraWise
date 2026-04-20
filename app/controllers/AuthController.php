<?php
// app/controllers/AuthController.php

class AuthController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /public/?page=login");
            exit;
        }

        // Mudei o nome da variável de $email para $identificacao (pode ser email ou cpf)
        $identificacao = trim($_POST['email'] ?? ''); 
        $senha = $_POST['senha'] ?? '';
        $tipo  = $_POST['tipo']  ?? 'proprietario';

        // ── Validação básica ────────────────────────────────────
        if (empty($identificacao) || empty($senha)) {
            $_SESSION['toast'] = [
                'tipo'     => 'error',
                'titulo'   => 'Campos obrigatórios',
                'mensagem' => 'Preencha o e-mail/CPF e a senha para continuar.',
            ];
            header("Location: /public/?page=login");
            exit;
        }

        $user = $this->buscarUsuario($identificacao, $senha, $tipo);

        if (!$user) {
            $_SESSION['toast'] = [
                'tipo'     => 'error',
                'titulo'   => 'Acesso negado',
                'mensagem' => 'E-mail, CPF ou senha incorretos. Verifique suas credenciais.',
            ];
            header("Location: /public/?page=login");
            exit;
        }

        // ── Login bem-sucedido ───────────────────────────────────
        session_regenerate_id(true); // Previne Session Fixation

        $_SESSION['user'] = $user;
        $_SESSION['tipo'] = $tipo;

        $_SESSION['toast'] = [
            'tipo'     => 'success',
            'titulo'   => 'Bem-vindo(a), ' . explode(' ', $user['nome'])[0] . '!',
            'mensagem' => 'Você entrou como ' . ($tipo === 'proprietario' ? 'proprietário' : 'peão de campo') . '.',
        ];

        header("Location: /public/?page=dashboard");
        exit;
    }

    private function buscarUsuario(string $identificacao, string $senha, string $tipo): ?array {
        require_once __DIR__ . '/../../config/Conexao.php';

        $conn = Conexao::getConexao();
        $tabela = ($tipo === 'peao') ? 'peao' : 'proprietario';

        // Agora busca tanto pelo E-mail quanto pelo CPF_CNPJ
        $sql = "SELECT id, nome, email, cpf_cnpj, senha FROM $tabela WHERE email = ? OR cpf_cnpj = ? LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Erro SQL: " . $conn->error);
        }

        // Passa a mesma variável duas vezes: uma para o email, outra para o CPF
        $stmt->bind_param("ss", $identificacao, $identificacao);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Usa password_verify para checar a senha criptografada!
        if (!$user || !password_verify($senha, $user['senha'])) {
            return null;
        }

        // Remove a senha do array por segurança antes de retornar para a sessão
        unset($user['senha']);

        return $user;
    }
}