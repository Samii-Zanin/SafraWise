<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class ProdutoController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    // ─── leitura (sem restrição de proprietário — catálogo é global) ─────────

    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nome, marca, unidade_medida, descricao, tipo
             FROM produto
             ORDER BY nome ASC"
        );
        $stmt->execute();
        $produtos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $produtos;
    }

    public function getAllnotCereal(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nome, marca, unidade_medida, descricao, tipo
             FROM produto
             WHERE tipo != 'CEREAL'
             ORDER BY nome ASC"
        );
        $stmt->execute();
        $produtos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $produtos;
    }

    public function getAllByTipo(string $tipo): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nome, unidade_medida
             FROM produto
             WHERE tipo = ?
             ORDER BY nome ASC"
        );
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        $produtos = $this->getAll();

        require_once __DIR__ . '/../views/produtos_culturas.php';
    }

    // ─── escrita (exclusivo para proprietário) ───────────────────────────────

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode cadastrar novos produtos no catálogo.');
            $this->redirect('produtos_culturas');
            return;
        }

        $dados = [
            'nome'      => trim($_POST['nome']      ?? ''),
            'marca'     => trim($_POST['marca']     ?? ''),
            'un_medida' => trim($_POST['un_medida'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'tipo'      => trim($_POST['tipo']      ?? ''),
        ];

        if (empty($dados['nome']) || empty($dados['tipo'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome e tipo são obrigatórios.');
            $this->redirect('produtos_culturas');
            return;
        }

        // Impede duplicata de nome+tipo no catálogo
        if ($this->nomeJaExiste($dados['nome'], $dados['tipo'])) {
            $this->setToast('warning', 'Produto duplicado',
                "Já existe um produto \"{$dados['nome']}\" do tipo \"{$dados['tipo']}\" no catálogo.");
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produto (nome, marca, unidade_medida, descricao, tipo)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssss",
                $dados['nome'], $dados['marca'], $dados['un_medida'],
                $dados['descricao'], $dados['tipo']
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir produto: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Produto Cadastrado!',
                "{$dados['nome']} foi adicionado ao catálogo.");
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível salvar o produto. Tente novamente.');
            $this->redirect('produtos_culturas');
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode alterar os dados dos produtos.');
            $this->redirect('produtos_culturas');
            return;
        }

        $produto_id = (int) ($_POST['produto_id'] ?? 0);

        if (!$produto_id) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('produtos_culturas');
            return;
        }

        $dados = [
            'nome'      => trim($_POST['nome']      ?? ''),
            'marca'     => trim($_POST['marca']     ?? ''),
            'un_medida' => trim($_POST['un_medida'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'tipo'      => trim($_POST['tipo']      ?? ''),
        ];

        // Original exigia nome+marca; alinhado com save() que exige nome+tipo
        if (empty($dados['nome']) || empty($dados['tipo'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome e tipo são obrigatórios.');
            $this->redirect('produtos_culturas');
            return;
        }

        // Impede duplicata excluindo o próprio registro da checagem
        if ($this->nomeJaExiste($dados['nome'], $dados['tipo'], $produto_id)) {
            $this->setToast('warning', 'Produto duplicado',
                "Já existe outro produto \"{$dados['nome']}\" do tipo \"{$dados['tipo']}\" no catálogo.");
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE produto
                 SET nome = ?, marca = ?, unidade_medida = ?, descricao = ?, tipo = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "sssssi",
                $dados['nome'], $dados['marca'], $dados['un_medida'],
                $dados['descricao'], $dados['tipo'], $produto_id
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar produto: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Produto Atualizado!',
                "{$dados['nome']} foi atualizado com sucesso.");
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível atualizar o produto. Tente novamente.');
            $this->redirect('produtos_culturas');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode remover itens do catálogo.');
            $this->redirect('produtos_culturas');
            return;
        }

        $produto_id = (int) ($_POST['produto_id'] ?? 0);

        if (!$produto_id) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('produtos_culturas');
            return;
        }

        // Bloqueia exclusão se o produto estiver vinculado a insumos ou operações
        if ($this->produtoEstaEmUso($produto_id)) {
            $this->setToast('warning', 'Produto em uso',
                'Este produto está vinculado a insumos ou operações e não pode ser excluído.');
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM produto WHERE id = ?");
            $stmt->bind_param("i", $produto_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao excluir produto: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Produto Removido',
                'O produto foi excluído do catálogo com sucesso.');
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Não foi possível excluir',
                'Este produto pode estar vinculado a operações e não pode ser removido.');
            $this->redirect('produtos_culturas');
        }
    }

    // ─── helpers privados ────────────────────────────────────────────────────

    /**
     * Verifica duplicata de nome+tipo no catálogo.
     * Passa $excluirId para ignorar o próprio registro no UPDATE.
     */
    private function nomeJaExiste(string $nome, string $tipo, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM produto
             WHERE UPPER(nome) = UPPER(?) AND tipo = ? AND id != ?
             LIMIT 1"
        );
        $stmt->bind_param("ssi", $nome, $tipo, $excluirId);
        $stmt->execute();
        $existe = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $existe;
    }

    /**
     * Verifica se o produto está em uso antes de permitir exclusão.
     * Checa vínculos em insumo e operacoes_financeiras.
     */
    private function produtoEstaEmUso(int $produto_id): bool
    {
        // Vinculado a insumo?
        $stmt = $this->db->prepare(
            "SELECT id FROM insumo WHERE produto_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            return true;
        }
        $stmt->close();

        // Vinculado a operação financeira?
        $stmt = $this->db->prepare(
            "SELECT id FROM operacoes_financeiras WHERE produto_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $emUso = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $emUso;
    }
}