<?php
require_once "../app/models/proprietario.php";
require_once "../config/conexao.php"; 

class ProprietarioController {
    
    private mysqli $db;

    public function __construct() {
        
        $this->db = Conexao::getConexao();
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=cadastro_proprietario");
            exit;
        }

        // 1. Captura e limpeza de dados
        $nome = trim($_POST['nome'] ?? '');
        $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $senha_pura = $_POST['senha'] ?? '';

        if (empty($nome) || empty($cpf_cnpj) || empty($email) || empty($senha_pura)) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Por favor, preencha todos os campos.');
            header("Location: index.php?page=cadastro_proprietario");
            exit;
        }

        try {
            // 2. Verificar se e-mail ou CPF/CNPJ já existem
            $stmtCheck = $this->db->prepare("SELECT id FROM proprietario WHERE email = ? OR cpf_cnpj = ?");
            // "ss" significa que estamos passando duas Strings
            $stmtCheck->bind_param("ss", $email, $cpf_cnpj); 
            $stmtCheck->execute();
            $stmtCheck->store_result();
            
            if ($stmtCheck->num_rows > 0) {
                $this->setToast('error', 'Cadastro Duplicado', 'Este E-mail ou CPF/CNPJ já está cadastrado.');
                $stmtCheck->close();
                header("Location: index.php?page=cadastro_proprietario");
                exit;
            }
            $stmtCheck->close();

            // 3. Criptografia da Senha
            $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

            // 4. Instancia o Model
            $proprietario = new Proprietario($nome, $cpf_cnpj, $telefone, $email, $senha_hash);

            // 5. Inserção no banco
            $sql = "INSERT INTO proprietario (nome, cpf_cnpj, telefone, email, senha) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $this->db->prepare($sql);
            
            // Pega os dados do objeto
            $p_nome = $proprietario->getNome();
            $p_cpf = $proprietario->getCpfCnpj();
            $p_tel = $proprietario->getTelefone();
            $p_email = $proprietario->getEmail();
            
            // "sssss" = 5 strings
            $stmtInsert->bind_param("sssss", $p_nome, $p_cpf, $p_tel, $p_email, $senha_hash);
            
            if ($stmtInsert->execute()) {
                $this->setToast('success', 'Conta criada!', 'Seu cadastro foi realizado. Faça login no sistema.');
                header("Location: index.php?page=login");
            } else {
                throw new Exception("Erro ao executar a query de inserção.");
            }
            
            $stmtInsert->close();
            exit;

        } catch (Exception $e) {
            error_log($e->getMessage()); // Registra o erro real nos logs do servidor
            $this->setToast('error', 'Erro interno', 'Ocorreu um problema ao salvar. Tente novamente.');
            header("Location: index.php?page=cadastro_proprietario");
            exit;
        }
    }

    private function setToast(string $tipo, string $titulo, string $mensagem): void {
        $_SESSION['toast'] = [
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem
        ];
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