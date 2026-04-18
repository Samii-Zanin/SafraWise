<?php
require_once "../app/models/peao.php"; 
require_once "../config/conexao.php";

class PeaoController {
    
    private mysqli $db;

    public function __construct() {
        $this->db = Conexao::getConexao();
    }

    public function index(): void {
        // Bloqueia acesso se não estiver logado ou se não for proprietário
        if (!isset($_SESSION['user']) || $_SESSION['tipo'] !== 'proprietario') {
            $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'Acesso Negado', 'mensagem' => 'Apenas proprietários podem gerenciar a equipe.'];
            header("Location: index.php?page=dashboard");
            exit;
        }

        $proprietario_id = $_SESSION['user']['id'];

        // Busca os peões apenas desta fazenda (deste proprietário)
        $sql = "SELECT id, nome, cpf_cnpj, telefone, email FROM peao WHERE proprietario_id = ? ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        // Transforma o resultado do banco num array associativo para usarmos no HTML
        $equipe = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Carrega a View passando a variável $equipe "embutida"
        require_once "../app/views/equipe.php";
    }

    public function store(): void {
        // Verifica se é POST e se o usuário está logado (proteção extra de rota)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            header("Location: index.php?page=login");
            exit;
        }

        // Pega o ID do proprietário logado que está criando esse peão
        // Ajuste 'id' de acordo com a chave que você salva no seu AuthController
        $proprietario_id = $_SESSION['user']['id']; 

        // 1. Captura de dados do formulário
        $nome = trim($_POST['nome'] ?? '');
        $cpf_cnpj = trim($_POST['cpf_cnpj'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha_pura = $_POST['senha'] ?? '';

        if (empty($nome) || empty($cpf_cnpj) || empty($senha_pura)) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome, CPF e Senha são obrigatórios.');
            header("Location: index.php?page=cadastro_peao");
            exit;
        }

        try {
            // 2. Verificar se o CPF já existe para a tabela de peões
            $stmtCheck = $this->db->prepare("SELECT id FROM peao WHERE cpf_cnpj = ?");
            $stmtCheck->bind_param("s", $cpf_cnpj);
            $stmtCheck->execute();
            $stmtCheck->store_result();
            
            if ($stmtCheck->num_rows > 0) {
                $this->setToast('error', 'Erro', 'Já existe um peão cadastrado com este CPF.');
                $stmtCheck->close();
                header("Location: index.php?page=cadastro_peao");
                exit;
            }
            $stmtCheck->close();

            // 3. Criptografar a senha do peão
            $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);
            
            // O e-mail é opcional, então se vier vazio, garantimos que seja null no banco
            $email_banco = empty($email) ? null : filter_var($email, FILTER_SANITIZE_EMAIL);

            // 4. Inserção
            $sql = "INSERT INTO peao (nome, cpf_cnpj, telefone, email, senha, proprietario_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmtInsert = $this->db->prepare($sql);
            
            // "sssssi" -> 5 Strings e 1 Inteiro (proprietario_id)
            $stmtInsert->bind_param("sssssi", $nome, $cpf_cnpj, $telefone, $email_banco, $senha_hash, $proprietario_id);
            
            if ($stmtInsert->execute()) {
                $this->setToast('success', 'Peão Cadastrado!', "$nome foi adicionado à sua equipe com sucesso.");
                header("Location: index.php?page=dashboard"); // Volta pro dashboard após sucesso
            } else {
                throw new Exception("Erro ao executar a query de inserção na tabela peao.");
            }
            
            $stmtInsert->close();
            exit;

        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar o peão. Tente novamente.');
            header("Location: index.php?page=cadastro_peao");
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

    // Exibe o formulário de edição
    public function edit(): void {
        $id = $_GET['id'] ?? null;
        $proprietario_id = $_SESSION['user']['id'];

        $stmt = $this->db->prepare("SELECT * FROM peao WHERE id = ? AND proprietario_id = ?");
        $stmt->bind_param("ii", $id, $proprietario_id);
        $stmt->execute();
        $peao = $stmt->get_result()->fetch_assoc();

        if (!$peao) {
            $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'Erro', 'mensagem' => 'Peão não encontrado.'];
            header("Location: index.php?page=equipe");
            exit;
        }

        require_once "../app/views/edit_peao.php";
    }

    // Processa a atualização dos dados
    public function update(): void {
        $id = $_POST['id'];
        $proprietario_id = $_SESSION['user']['id'];
        $nome = trim($_POST['nome']);
        $cpf = trim($_POST['cpf_cnpj']);
        $telefone = trim($_POST['telefone']);
        $email = trim($_POST['email']);

        $sql = "UPDATE peao SET nome = ?, cpf_cnpj = ?, telefone = ?, email = ? WHERE id = ? AND proprietario_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssii", $nome, $cpf, $telefone, $email, $id, $proprietario_id);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['tipo' => 'success', 'titulo' => 'Sucesso', 'mensagem' => 'Dados atualizados!'];
        } else {
            $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'Erro', 'mensagem' => 'Falha ao atualizar.'];
        }
        header("Location: index.php?page=equipe");
    }

    // Exclui o peão
    public function delete(): void {
        $id = $_GET['id'];
        $proprietario_id = $_SESSION['user']['id'];

        $stmt = $this->db->prepare("DELETE FROM peao WHERE id = ? AND proprietario_id = ?");
        $stmt->bind_param("ii", $id, $proprietario_id);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['tipo' => 'success', 'titulo' => 'Removido', 'mensagem' => 'Colaborador excluído da equipe.'];
        }
        header("Location: index.php?page=equipe");
    }
}