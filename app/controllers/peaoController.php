<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/validator/PeaoValidator.php';

class PeaoController extends BaseController
{
    private mysqli $db;
    private PeaoValidator $validator;

    public function __construct()
    {
        $this->db        = Conexao::getConexao();
        $this->validator = new PeaoValidator();
    }

    public function index(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas proprietários podem gerenciar a equipe.');
            $this->redirect('dashboard');
        }

        $proprietarioId = $_SESSION['user']['id'];

        $stmt = $this->db->prepare(
            "SELECT id, nome, cpf_cnpj, telefone, email
             FROM peao
             WHERE proprietario_id = ?
             ORDER BY nome ASC"
        );
        $stmt->bind_param("i", $proprietarioId);
        $stmt->execute();

        $equipe = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require_once __DIR__ . '/../views/equipe.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $proprietarioId = $_SESSION['user']['id'];

        $dados = [
            'nome'     => trim($_POST['nome']     ?? ''),
            'cpf_cnpj' => trim($_POST['cpf_cnpj'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'email'    => trim($_POST['email']    ?? ''),
            'senha'    => $_POST['senha']         ?? '',
        ];

        // 🌟 HIGIENIZAÇÃO: Remove pontos/traços vindo da máscara do formulário
        $dados['cpf_cnpj'] = preg_replace('/\D/', '', $dados['cpf_cnpj']);

        $erros = $this->validator->validar($dados);
        if (!empty($erros)) {
            $this->setToast('warning', 'Campos Obrigatórios', implode(' ', $erros));
            $this->redirect('equipe');
        }

        // 🌟 VALIDAÇÃO MATEMÁTICA: Barra se o CPF for inválido no algoritmo de Módulo 11
        if (!$this->validarCPF($dados['cpf_cnpj'])) {
            $this->setToast('error', 'CPF Inválido', 'O número de CPF informado não é válido. Verifique os dígitos.');
            $this->redirect('equipe');
        }

        if ($this->validator->cpfJaExiste($dados['cpf_cnpj'])) {
            $this->setToast('error', 'Erro', 'Já existe um peão cadastrado com este CPF.');
            $this->redirect('equipe');
        }

        try {
            $senhaHash  = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $emailBanco = !empty($dados['email'])
                ? filter_var($dados['email'], FILTER_SANITIZE_EMAIL)
                : null;

            $stmt = $this->db->prepare(
                "INSERT INTO peao (nome, cpf_cnpj, telefone, email, senha, proprietario_id)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssssi",
                $dados['nome'],
                $dados['cpf_cnpj'],
                $dados['telefone'],
                $emailBanco,
                $senhaHash,
                $proprietarioId
            );

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir peão: {$this->db->error}");
            }

            $stmt->close();

            $this->setToast('success', 'Colaborador Cadastrado!', "{$dados['nome']} foi adicionado à sua equipe.");
            $this->redirect('equipe');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar o peão. Tente novamente.');
            $this->redirect('equipe');
        }
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

        // 🌟 HIGIENIZAÇÃO: Remove pontos/traços na atualização também
        $cpf = preg_replace('/\D/', '', $cpf);

        // 🌟 VALIDAÇÃO MATEMÁTICA: Valida o CPF na edição para evitar corrupção de dados antigos
        if (!$this->validarCPF($cpf)) {
            $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'CPF Inválido', 'mensagem' => 'O número de CPF informado não é válido.'];
            header("Location: index.php?page=equipe");
            exit;
        }

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

    /**
     * Algoritmo de Validação Oficial de CPF (Módulo 11)
     */
    private function validarCPF(string $cpf): bool {
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}