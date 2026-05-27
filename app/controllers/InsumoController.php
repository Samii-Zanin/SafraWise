<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class InsumoController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $stmt = $this->db->prepare(
            "SELECT i.id, i.valor_por_dose,
                    p.id AS produto_id, p.nome, p.marca, p.tipo,
                    p.unidade_medida, p.descricao
             FROM insumo i
             INNER JOIN produto p ON p.id = i.produto_id
             ORDER BY p.nome ASC"
        );
        $stmt->execute();
        $insumos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require_once __DIR__ . '/../views/insumos.php';
    }



    public function getAllComProduto(): array
    {
        $stmt = $this->db->prepare("
            SELECT i.id, p.nome, p.marca, p.unidade_medida
            FROM insumo i
            JOIN produto p ON p.id = i.produto_id
            ORDER BY p.nome ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }


    public function getAllNotCereal(): array
    {
        $stmt = $this->db->prepare("
            select
                i.id,
                p.nome,
                p.tipo,
                p.marca, 
                i.valor_por_dose, 
                p.unidade_medida,
                MAX(data_referencia) as data_ref
            from
                insumo i
            left join produto p on
                i.produto_id = p.id
            where p.tipo <> 'CEREAL' 
                group by
                i.id,
                p.nome,
                p.tipo,
                p.marca, 
                i.valor_por_dose,
                p.unidade_medida
            order by p.nome asc
        ");
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

        $dados = [
            'nome'           => trim($_POST['nome']           ?? ''),
            'marca'          => trim($_POST['marca']          ?? ''),
            'tipo'           => trim($_POST['tipo']           ?? ''),
            'unidade_medida' => trim($_POST['unidade_medida'] ?? ''),
            'descricao'      => trim($_POST['descricao']      ?? ''),
            'valor_por_dose' => str_replace(',', '.', trim($_POST['valor_por_dose'] ?? '')),
        ];

        if (empty($dados['nome']) || !is_numeric($dados['valor_por_dose'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome do produto e valor por dose são obrigatórios.');
            $this->redirect('insumos');
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produto (nome, marca, tipo, unidade_medida, descricao) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssss",
                $dados['nome'], $dados['marca'], $dados['tipo'],
                $dados['unidade_medida'], $dados['descricao']
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir produto: {$this->db->error}");
            }
            $produtoId = $this->db->insert_id;
            $stmt->close();

            $valor = (float) $dados['valor_por_dose'];
            $stmt  = $this->db->prepare("INSERT INTO insumo (valor_por_dose, produto_id) VALUES (?, ?)");
            $stmt->bind_param("di", $valor, $produtoId);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir insumo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Insumo Cadastrado!', "{$dados['nome']} foi adicionado ao catálogo.");
            $this->redirect('insumos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar o insumo. Tente novamente.');
            $this->redirect('insumos');
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $insumoId  = (int) ($_POST['insumo_id']  ?? 0);
        $produtoId = (int) ($_POST['produto_id'] ?? 0);

        if (!$insumoId || !$produtoId) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('insumos');
        }

        $dados = [
            'nome'           => trim($_POST['nome']           ?? ''),
            'marca'          => trim($_POST['marca']          ?? ''),
            'tipo'           => trim($_POST['tipo']           ?? ''),
            'unidade_medida' => trim($_POST['unidade_medida'] ?? ''),
            'descricao'      => trim($_POST['descricao']      ?? ''),
            'valor_por_dose' => str_replace(',', '.', trim($_POST['valor_por_dose'] ?? '')),
        ];

        if (empty($dados['nome']) || !is_numeric($dados['valor_por_dose'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome do produto e valor por dose são obrigatórios.');
            $this->redirect('insumos');
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE produto SET nome=?, marca=?, tipo=?, unidade_medida=?, descricao=? WHERE id=?"
            );
            $stmt->bind_param(
                "sssssi",
                $dados['nome'], $dados['marca'], $dados['tipo'],
                $dados['unidade_medida'], $dados['descricao'], $produtoId
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar produto: {$this->db->error}");
            }
            $stmt->close();

            $valor = (float) $dados['valor_por_dose'];
            $stmt  = $this->db->prepare("UPDATE insumo SET valor_por_dose=? WHERE id=?");
            $stmt->bind_param("di", $valor, $insumoId);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar insumo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Insumo Atualizado!', "{$dados['nome']} foi atualizado com sucesso.");
            $this->redirect('insumos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível atualizar o insumo. Tente novamente.');
            $this->redirect('insumos');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $insumoId  = (int) ($_POST['insumo_id']  ?? 0);
        $produtoId = (int) ($_POST['produto_id'] ?? 0);

        if (!$insumoId) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('insumos');
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM estoque_insumos WHERE insumo_id = ?");
            $stmt->bind_param("i", $insumoId);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->db->prepare("DELETE FROM insumo WHERE id = ?");
            $stmt->bind_param("i", $insumoId);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao excluir insumo: {$this->db->error}");
            }
            $stmt->close();

            if ($produtoId) {
                $stmt = $this->db->prepare("DELETE FROM produto WHERE id = ?");
                $stmt->bind_param("i", $produtoId);
                $stmt->execute();
                $stmt->close();
            }

            $this->setToast('success', 'Insumo Removido', 'O insumo foi excluído do catálogo com sucesso.');
            $this->redirect('insumos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Não foi possível excluir', 'Este insumo pode estar vinculado a operações agrícolas.');
            $this->redirect('insumos');
        }
    }
}
