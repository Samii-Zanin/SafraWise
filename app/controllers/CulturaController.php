<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class CulturaController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function getAll(): array
{
    $stmt = $this->db->prepare("SELECT * FROM cultura ORDER BY nome ASC");
    $stmt->execute();
    $culturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $culturas;
}


    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM cultura"
        );
        $stmt->execute();
        $culturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require_once __DIR__ . '/../views/produtos_culturas.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $dados = [
            'nome'      => trim($_POST['nome']      ?? ''),
            'variedade' => trim($_POST['variedade'] ?? ''),
        ];

        if (empty($dados['nome'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome é obrigatório.');
            $this->redirect('produtos_culturas');
        }

        try {
            // ── 1. Insere a cultura ──────────────────────────────
            $stmt = $this->db->prepare(
                "INSERT INTO cultura (nome, variedade) VALUES (?, ?)"
            );
            $stmt->bind_param("ss", $dados['nome'], $dados['variedade']);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir cultura: {$this->db->error}");
            }
            $stmt->close();

            // ── 2. Verifica se produto CEREAL já existe ──────────
            $stmtCheck = $this->db->prepare(
                "SELECT id FROM produto WHERE nome = ? AND tipo = 'CEREAL' LIMIT 1"
            );
            $stmtCheck->bind_param("s", $dados['nome']);
            $stmtCheck->execute();
            $produtoExistente = $stmtCheck->get_result()->fetch_assoc();
            $stmtCheck->close();

            // ── 3. Só insere produto se não existir ──────────────
            if (!$produtoExistente) {
                $stmt = $this->db->prepare(
                    "INSERT INTO produto (nome, descricao, unidade_medida, tipo) VALUES (?, ?, 'sc', 'CEREAL')"
                );
                $stmt->bind_param("ss", $dados['nome'], $dados['variedade']);
                if (!$stmt->execute()) {
                    throw new \RuntimeException("Falha ao inserir produto: {$this->db->error}");
                }
                $stmt->close();
            }

            $this->setToast('success', 'Cultura Cadastrada!', "{$dados['nome']} foi adicionada ao catálogo.");
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar a cultura. Tente novamente.');
            $this->redirect('produtos_culturas');
        }
    }

    public function getDistintas(): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT nome FROM cultura ORDER BY nome ASC"
        );
        $stmt->execute();
        $culturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $culturas;
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $culturaId = (int) ($_POST['cultura_id'] ?? 0);

        if (!$culturaId) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('produtos_culturas');
        }

        $dados = [
            'nome'           => trim($_POST['nome']           ?? ''),
            'variedade'      => trim($_POST['variedade']      ?? ''),
        ];

        if (empty($dados['nome']) || empty($dados['variedade'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome e variedade são obrigatórios.');
            $this->redirect('produtos_culturas');
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE cultura SET nome=?, variedade=? WHERE id=?"
            );
            $stmt->bind_param(
                "ssi",
                $dados['nome'], $dados['variedade'], $culturaId
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar cultura: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Cultura Atualizada!', "{$dados['nome']} foi atualizada com sucesso.");
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível atualizar a cultura. Tente novamente.');
            $this->redirect('produtos_culturas');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $culturaId = (int) ($_POST['cultura_id'] ?? 0);

        if (!$culturaId) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('produtos_culturas');
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM cultura WHERE id = ?");
            $stmt->bind_param("i", $culturaId);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao excluir cultura: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Cultura Removida', 'A cultura foi excluída do catálogo com sucesso.');
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Não foi possível excluir', 'Esta cultura pode estar vinculada a operações agrícolas.');
            $this->redirect('produtos_culturas');
        }
    }
}
