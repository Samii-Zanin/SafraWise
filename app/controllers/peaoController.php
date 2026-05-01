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

        $erros = $this->validator->validar($dados);
        if (!empty($erros)) {
            $this->setToast('warning', 'Campos Obrigatórios', implode(' ', $erros));
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

            $this->setToast('success', 'Peão Cadastrado!', "{$dados['nome']} foi adicionado à sua equipe.");
            $this->redirect('equipe');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar o peão. Tente novamente.');
            $this->redirect('equipe');
        }
    }
}