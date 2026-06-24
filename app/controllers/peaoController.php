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

    // ─── helpers privados ────────────────────────────────────────────────────

    private function getProprietarioId(): int
    {
        return (int) ($_SESSION['proprietario_id'] ?? 0);
    }

    /**
     * Garante que o peão informado pertence ao proprietário logado
     * antes de qualquer leitura ou escrita sensível.
     */
    private function validarPeao(int $peao_id): bool
    {
        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare(
            "SELECT id FROM peao WHERE id = ? AND proprietario_id = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $peao_id, $proprietario_id);
        $stmt->execute();
        $ok = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $ok;
    }

    // ─── ações ───────────────────────────────────────────────────────────────

    public function index(): void
    {
        // Apenas proprietários gerenciam a equipe — peão não tem acesso
        if (!isset($_SESSION['user']) || $_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas proprietários podem gerenciar a equipe.');
            $this->redirect('dashboard');
            return;
        }

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare(
            "SELECT id, nome, cpf_cnpj, telefone, email
             FROM peao
             WHERE proprietario_id = ?
             ORDER BY nome ASC"
        );
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $equipe = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require_once __DIR__ . '/../views/equipe.php';
    }

    public function getAll(): array
    {
        if (!isset($_SESSION['user'])) return [];

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare(
            "SELECT id, nome, cpf_cnpj, telefone, email
             FROM peao
             WHERE proprietario_id = ?
             ORDER BY nome ASC"
        );
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $peoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $peoes;
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Apenas proprietários podem cadastrar peões
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas proprietários podem cadastrar colaboradores.');
            $this->redirect('dashboard');
            return;
        }

        $proprietario_id = $this->getProprietarioId();

        $dados = [
            'nome'     => trim($_POST['nome']     ?? ''),
            'cpf_cnpj' => preg_replace('/\D/', '', trim($_POST['cpf_cnpj'] ?? '')),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'email'    => trim($_POST['email']    ?? ''),
            'senha'    => $_POST['senha']         ?? '',
        ];

        $erros = $this->validator->validar($dados);
        if (!empty($erros)) {
            $this->setToast('warning', 'Campos Obrigatórios', implode(' ', $erros));
            $this->redirect('equipe');
            return;
        }

        if (!$this->validarCPF($dados['cpf_cnpj'])) {
            $this->setToast('error', 'CPF Inválido',
                'O número de CPF informado não é válido. Verifique os dígitos.');
            $this->redirect('equipe');
            return;
        }

        if ($this->validator->cpfJaExiste($dados['cpf_cnpj'])) {
            $this->setToast('error', 'CPF já cadastrado',
                'Já existe um peão cadastrado com este CPF.');
            $this->redirect('equipe');
            return;
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
                $proprietario_id
            );

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir peão: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Colaborador Cadastrado!',
                "{$dados['nome']} foi adicionado à sua equipe.");
            $this->redirect('equipe');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível salvar o colaborador. Tente novamente.');
            $this->redirect('equipe');
        }
    }

    public function edit(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas proprietários podem editar colaboradores.');
            $this->redirect('dashboard');
            return;
        }

        $peao_id = (int) ($_GET['id'] ?? 0);

        if (!$peao_id || !$this->validarPeao($peao_id)) {
            $this->setToast('error', 'Erro', 'Peão não encontrado.');
            $this->redirect('equipe');
            return;
        }

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare(
            "SELECT * FROM peao WHERE id = ? AND proprietario_id = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $peao_id, $proprietario_id);
        $stmt->execute();
        $peao = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        require_once __DIR__ . '/../views/edit_peao.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas proprietários podem editar colaboradores.');
            $this->redirect('dashboard');
            return;
        }

        $peao_id         = (int) ($_POST['id'] ?? 0);
        $proprietario_id = $this->getProprietarioId();

        if (!$peao_id || !$this->validarPeao($peao_id)) {
            $this->setToast('error', 'Erro', 'Peão não encontrado.');
            $this->redirect('equipe');
            return;
        }

        $nome     = trim($_POST['nome']     ?? '');
        $cpf      = preg_replace('/\D/', '', trim($_POST['cpf_cnpj'] ?? ''));
        $telefone = trim($_POST['telefone'] ?? '');
        $email    = trim($_POST['email']    ?? '');

        if (!$this->validarCPF($cpf)) {
            $this->setToast('error', 'CPF Inválido',
                'O número de CPF informado não é válido.');
            $this->redirect('equipe');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE peao
                 SET nome = ?, cpf_cnpj = ?, telefone = ?, email = ?
                 WHERE id = ? AND proprietario_id = ?"
            );
            $stmt->bind_param("ssssii", $nome, $cpf, $telefone, $email,
                              $peao_id, $proprietario_id);

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar peão: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Sucesso', 'Dados atualizados!');
            $this->redirect('equipe');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível atualizar o colaborador. Tente novamente.');
            $this->redirect('equipe');
        }
    }

    public function delete(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas proprietários podem remover colaboradores.');
            $this->redirect('dashboard');
            return;
        }

        $peao_id         = (int) ($_GET['id'] ?? 0);
        $proprietario_id = $this->getProprietarioId();

        if (!$peao_id || !$this->validarPeao($peao_id)) {
            $this->setToast('error', 'Erro', 'Peão não encontrado.');
            $this->redirect('equipe');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "DELETE FROM peao WHERE id = ? AND proprietario_id = ?"
            );
            $stmt->bind_param("ii", $peao_id, $proprietario_id);

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao excluir peão: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Removido', 'Colaborador excluído da equipe.');
            $this->redirect('equipe');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível remover o colaborador. Tente novamente.');
            $this->redirect('equipe');
        }
    }

    // ─── utilitários ─────────────────────────────────────────────────────────

    /**
     * Algoritmo de Validação Oficial de CPF (Módulo 11)
     */
    private function validarCPF(string $cpf): bool
    {
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