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

        $identificacao = trim($_POST['cpf'] ?? ''); 
        // Mantém a limpeza para remover a máscara do front antes de buscar no banco
        $identificacao = preg_replace('/\D/', '', $identificacao); 
        
        $senha = $_POST['senha'] ?? '';
        $tipo  = $_POST['tipo']  ?? 'proprietario';

        if (empty($identificacao) || empty($senha)) {
            $_SESSION['toast'] = [
                'tipo'     => 'error',
                'titulo'   => 'Campos obrigatórios',
                'mensagem' => 'Preencha o CPF e a senha para continuar.',
            ];
            header("Location: /public/?page=login");
            exit;
        }

        $user = $this->buscarUsuario($identificacao, $senha, $tipo);

        if (!$user) {
            $_SESSION['toast'] = [
                'tipo'     => 'error',
                'titulo'   => 'Acesso negado',
                'mensagem' => 'CPF ou senha incorretos. Verifique suas credenciais.',
            ];
            header("Location: /public/?page=login");
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['user'] = $user;
        $_SESSION['tipo'] = $tipo;

        $_SESSION['toast'] = [
            'tipo'     => 'success',
            'titulo'   => 'Bem-vindo(a), ' . explode(' ', $user['nome'])[0] . '!',
            'mensagem' => 'Você entrou como ' . ($tipo === 'proprietario' ? 'proprietário' : 'peão') . '.',
        ];

        header("Location: /public/?page=dashboard");
        exit;
    }

    private function buscarUsuario(string $identificacao, string $senha, string $tipo): ?array {
        require_once __DIR__ . '/../../config/Conexao.php';

        $conn = Conexao::getConexao();
        $tabela = ($tipo === 'peao') ? 'peao' : 'proprietario';

        $sql = "SELECT id, nome, email, cpf_cnpj, senha FROM $tabela WHERE cpf_cnpj = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Erro SQL: " . $conn->error);
        }

        $stmt->bind_param("s", $identificacao);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($senha, $user['senha'])) {
            return null;
        }

        unset($user['senha']);
        return $user;
    }
}