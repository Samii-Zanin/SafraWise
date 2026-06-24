<?php
// app/controllers/ProprietarioController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class ProprietarioController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    // ─── helper privado ──────────────────────────────────────────────────────

    private function getProprietarioId(): int
    {
        return (int) ($_SESSION['proprietario_id'] ?? 0);
    }

    // ─── registro público (sem sessão) ───────────────────────────────────────

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cadastro_proprietario');
            return;
        }

        $nome     = trim($_POST['nome']     ?? '');
        $cpf_cnpj = preg_replace('/\D/', '', trim($_POST['cpf_cnpj'] ?? ''));
        $telefone = trim($_POST['telefone'] ?? '');
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $senha    = $_POST['senha'] ?? '';

        if (empty($nome) || empty($cpf_cnpj) || empty($email) || empty($senha)) {
            $this->setToast('warning', 'Campos Obrigatórios',
                'Por favor, preencha todos os campos.');
            $this->redirect('cadastro_proprietario');
            return;
        }

        if (!$this->validarCPF($cpf_cnpj)) {
            $this->setToast('error', 'CPF Inválido',
                'O número de CPF informado não é válido. Verifique os dígitos.');
            $this->redirect('cadastro_proprietario');
            return;
        }

        try {
            if ($this->proprietarioJaExiste($email, $cpf_cnpj)) {
                $this->setToast('error', 'Cadastro Duplicado',
                    'Este e-mail ou CPF/CNPJ já está cadastrado.');
                $this->redirect('cadastro_proprietario');
                return;
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

            $this->setToast('success', 'Conta criada!',
                'Seu cadastro foi realizado. Faça login no sistema.');
            $this->redirect('login');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Ocorreu um problema ao salvar. Tente novamente.');
            $this->redirect('cadastro_proprietario');
        }
    }

    // ─── área logada (exclusivo para proprietário) ───────────────────────────

    public function configuracoes(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Peão não tem configurações de proprietário
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Esta área é exclusiva para proprietários.');
            $this->redirect('dashboard');
            return;
        }

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare(
            "SELECT id, nome, email, cpf_cnpj, telefone
             FROM proprietario
             WHERE id = ?
             LIMIT 1"
        );
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $this->setToast('error', 'Erro', 'Proprietário não encontrado.');
            $this->redirect('dashboard');
            return;
        }

        require_once __DIR__ . '/../views/configuracoes_proprietario.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Esta área é exclusiva para proprietários.');
            $this->redirect('dashboard');
            return;
        }

        $proprietario_id = $this->getProprietarioId();
        $nome            = trim($_POST['nome']  ?? '');
        $email           = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $senha           = $_POST['senha'] ?? '';

        if (empty($nome) || empty($email)) {
            $this->setToast('warning', 'Campos Obrigatórios',
                'Nome e e-mail são obrigatórios.');
            $this->redirect('configuracoes');
            return;
        }

        // Impede que o novo email já pertença a outro proprietário
        if ($this->emailJaUsadoPorOutro($email, $proprietario_id)) {
            $this->setToast('error', 'E-mail já cadastrado',
                'Este e-mail já está em uso por outra conta.');
            $this->redirect('configuracoes');
            return;
        }

        try {
            if (!empty($senha)) {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare(
                    "UPDATE proprietario SET nome = ?, email = ?, senha = ? WHERE id = ?"
                );
                $stmt->bind_param("sssi", $nome, $email, $senhaHash, $proprietario_id);
            } else {
                $stmt = $this->db->prepare(
                    "UPDATE proprietario SET nome = ?, email = ? WHERE id = ?"
                );
                $stmt->bind_param("ssi", $nome, $email, $proprietario_id);
            }

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar proprietário: {$this->db->error}");
            }
            $stmt->close();

            // Atualiza a sessão para refletir os novos dados imediatamente
            $_SESSION['user']['nome']  = $nome;
            $_SESSION['user']['email'] = $email;

            $this->setToast('success', 'Perfil Atualizado',
                'Suas alterações foram salvas com sucesso.');
            $this->redirect('configuracoes');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível atualizar o perfil. Tente novamente.');
            $this->redirect('configuracoes');
        }
    }

    // ─── helpers privados ────────────────────────────────────────────────────

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

    /**
     * Verifica se o email já pertence a outro proprietário (ignora o próprio).
     * Usado no update() para não bloquear quem mantém o mesmo email.
     */
    private function emailJaUsadoPorOutro(string $email, int $excluirId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM proprietario WHERE email = ? AND id != ? LIMIT 1"
        );
        $stmt->bind_param("si", $email, $excluirId);
        $stmt->execute();
        $existe = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $existe;
    }

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