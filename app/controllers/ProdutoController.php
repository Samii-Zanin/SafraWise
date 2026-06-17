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

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM produto ORDER BY nome ASC");
        $stmt->execute();
        $produtos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $produtos;
    }

    public function getAllnotCereal(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM produto WHERE tipo != 'CEREAL' ORDER BY nome ASC");
        $stmt->execute();
        $produtos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $produtos;
    }

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        //  Usa o getAll() para manter o código DRY (Don't Repeat Yourself)
        $produtos = $this->getAll();

        require_once __DIR__ . '/../views/produtos_culturas.php';
    }

    public function getAllByTipo(string $tipo): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nome, unidade_medida FROM produto WHERE tipo = ? ORDER BY nome ASC"
        );
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        //  Peão não pode cadastrar novos produtos no catálogo
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas o proprietário pode cadastrar novos produtos no catálogo.');
            $this->redirect('produtos_culturas');
            return;
        }

        $dados = [
            'nome'           => trim($_POST['nome']           ?? ''),
            'marca'          => trim($_POST['marca']          ?? ''),
            'un_medida'      => trim($_POST['un_medida']      ?? ''),
            'descricao'      => trim($_POST['descricao']      ?? ''),
            'tipo'           => trim($_POST['tipo']           ?? ''),
        ];

        if (empty($dados['nome']) || empty($dados['tipo'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome e tipo são obrigatórios.');
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produto (nome, marca, unidade_medida, descricao, tipo) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssss",
                $dados['nome'], $dados['marca'], $dados['un_medida'], $dados['descricao'], $dados['tipo']
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir produto: {$this->db->error}");
            }
            $produtoId = $this->db->insert_id;
            $stmt->close();

            $this->setToast('success', 'Produto Cadastrado!', "{$dados['nome']} foi adicionado ao catálogo.");
            $this->redirect('produtos_culturas');

            
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar o produto. Tente novamente.'. $e->getMessage());
            $this->redirect('produtos_culturas');
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        //  Peão não pode editar produtos do catálogo
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas o proprietário pode alterar os dados dos produtos.');
            $this->redirect('produtos_culturas');
            return;
        }

        $produtoId = (int) ($_POST['produto_id'] ?? 0);

        if (!$produtoId) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('produtos_culturas');
            return;
        }

        $dados = [
            'nome'           => trim($_POST['nome']           ?? ''),
            'marca'          => trim($_POST['marca']          ?? ''),
            'un_medida'      => trim($_POST['un_medida']      ?? ''),
            'descricao'      => trim($_POST['descricao']      ?? ''),
            'tipo'           => trim($_POST['tipo']           ?? ''),
        ];

        if (empty($dados['nome']) || empty($dados['marca'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome e marca são obrigatórios.');
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE produto SET nome=?, marca=?, unidade_medida=?, descricao=?, tipo=? WHERE id=?"
            );
            $stmt->bind_param(
                "sssssi",
                $dados['nome'], $dados['marca'], $dados['un_medida'], $dados['descricao'], $dados['tipo'], $produtoId
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar produto: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Produto Atualizado!', "{$dados['nome']} foi atualizado com sucesso.");
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível atualizar o produto. Tente novamente. erro: ' . $e->getMessage());
            $this->redirect('produtos_culturas');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        //  Peão não pode apagar produtos do catálogo
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas o proprietário pode remover itens do catálogo.');
            $this->redirect('produtos_culturas');
            return;
        }

        $produtoId = (int) ($_POST['produto_id'] ?? 0);

        if (!$produtoId) {
            $this->setToast('error', 'Erro', 'Identificador inválido.' . $produtoId);
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM produto WHERE id = ?");
            $stmt->bind_param("i", $produtoId);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao excluir produto: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Produto Removido', 'O produto foi excluído do catálogo com sucesso.');
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Não foi possível excluir', 'Este produto pode estar vinculado a operações agrícolas.');
            $this->redirect('produtos_culturas');
        }
    }
}