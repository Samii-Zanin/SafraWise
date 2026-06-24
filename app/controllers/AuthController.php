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

        $identificacao = preg_replace('/\D/', '', trim($_POST['cpf'] ?? ''));
        $senha         = $_POST['senha'] ?? '';
        $tipo          = $_POST['tipo']  ?? 'proprietario';

        if (empty($identificacao) || empty($senha)) {
            $this->flashERedirect('error', 'Campos obrigatórios', 'Preencha o CPF e a senha para continuar.', 'login');
        }

        $user = $this->buscarUsuario($identificacao, $senha, $tipo);

        if (!$user) {
            $this->flashERedirect('error', 'Acesso negado', 'CPF ou senha incorretos. Verifique suas credenciais.', 'login');
        }

        session_regenerate_id(true);

        $_SESSION['user'] = $user;
        $_SESSION['tipo'] = $tipo;

        // Define quem é o dono da fazenda para o resto do sistema:
        // - Proprietário: ele mesmo
        // - Peão: o proprietário vinculado a ele na tabela peao
        if ($tipo === 'proprietario') {
            $_SESSION['proprietario_id'] = $user['id'];
        } else {
            if (empty($user['proprietario_id'])) {
                $this->flashERedirect('error', 'Conta inválida', 'Peão não possui proprietário vinculado. Contate o suporte.', 'login');
            }
            $_SESSION['proprietario_id'] = $user['proprietario_id'];
        }

        $primeiroNome = explode(' ', $user['nome'])[0];
        $labelTipo    = $tipo === 'proprietario' ? 'proprietário' : 'peão';

        $this->flashERedirect('success', "Bem-vindo(a), {$primeiroNome}!", "Você entrou como {$labelTipo}.", 'dashboard');
    }

    private function buscarUsuario(string $identificacao, string $senha, string $tipo): ?array
    {
        $conn = Conexao::getConexao();

        // Peão: inclui proprietario_id para sabermos quem é o dono da fazenda
        if ($tipo === 'peao') {
            $sql = "SELECT id, nome, email, cpf_cnpj, senha, proprietario_id 
                    FROM peao 
                    WHERE cpf_cnpj = ? 
                    LIMIT 1";
        } else {
            $sql = "SELECT id, nome, email, cpf_cnpj, senha 
                    FROM proprietario 
                    WHERE cpf_cnpj = ? 
                    LIMIT 1";
        }

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException("Erro ao preparar consulta: " . $conn->error);
        }

        $stmt->bind_param("s", $identificacao);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($senha, $user['senha'])) {
            return null;
        }

        unset($user['senha']);
        return $user;
    }

    private function flashERedirect(string $tipo, string $titulo, string $mensagem, string $page): never
    {
        $_SESSION['toast'] = [
            'tipo'     => $tipo,
            'titulo'   => $titulo,
            'mensagem' => $mensagem,
        ];
        header("Location: /public/?page={$page}");
        exit;
    }
}