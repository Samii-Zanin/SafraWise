<?php
require_once "../app/controllers/BaseController.php";
require_once "../config/conexao.php";

class ProprietarioController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cadastro_proprietario');
        }

        $nome      = trim($_POST['nome']    ?? '');
        $cpf_cnpj  = trim($_POST['cpf_cnpj'] ?? '');
        $telefone  = trim($_POST['telefone'] ?? '');
        $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $senha     = $_POST['senha'] ?? '';

        if (empty($nome) || empty($cpf_cnpj) || empty($email) || empty($senha)) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Por favor, preencha todos os campos.');
            $this->redirect('cadastro_proprietario');
        }

        try {
            if ($this->proprietarioJaExiste($email, $cpf_cnpj)) {
                $this->setToast('error', 'Cadastro Duplicado', 'Este e-mail ou CPF/CNPJ já está cadastrado.');
                $this->redirect('cadastro_proprietario');
            }

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare(
                "INSERT INTO proprietario (nome, cpf_cnpj, telefone, email, senha)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $nome, $cpf_cnpj, $telefone, $email, $senhaHash);

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir proprietário: {$this->db->error}");
            }

            $stmt->close();

            $this->setToast('success', 'Conta criada!', 'Seu cadastro foi realizado. Faça login no sistema.');
            $this->redirect('login');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Ocorreu um problema ao salvar. Tente novamente.');
            $this->redirect('cadastro_proprietario');
        }
    }

    // ESSA FUNÇÃO VAI PARA O VALIDATOR
    private function proprietarioJaExiste(string $email, string $cpf_cnpj): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM proprietario WHERE email = ? OR cpf_cnpj = ? LIMIT 1"
        );
        $stmt->bind_param("ss", $email, $cpf_cnpj);
        $stmt->execute();
        $stmt->store_result();

        $existe = $stmt->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    // Exibe a tela de perfil/configurações
    public function configuracoes(): void {
        $id = $_SESSION['user']['id'];
        
        $stmt = $this->db->prepare("SELECT id, nome, email, cpf_cnpj FROM proprietario WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        require_once "../app/views/configuracoes_proprietario.php";
    }

    // Processa a atualização do perfil
    public function update(): void {
        $id = $_SESSION['user']['id'];
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = $_POST['senha'] ?? '';

        if (!empty($senha)) {
            // Atualiza com nova senha criptografada
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE proprietario SET nome = ?, email = ?, senha = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sssi", $nome, $email, $senhaHash, $id);
        } else {
            // Atualiza apenas nome e email
            $sql = "UPDATE proprietario SET nome = ?, email = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssi", $nome, $email, $id);
        }

        if ($stmt->execute()) {
            // Atualiza a sessão para o nome mudar na barra lateral imediatamente
            $_SESSION['user']['nome'] = $nome;
            $_SESSION['user']['email'] = $email;
            $_SESSION['toast'] = ['tipo' => 'success', 'titulo' => 'Perfil Atualizado', 'mensagem' => 'Suas alterações foram salvas.'];
        }
        
        header("Location: index.php?page=configuracoes");
    }
}