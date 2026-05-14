<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/validator/estoqueInsumosValidator.php';

class EstoqueInsumosController extends BaseController
{
    private mysqli $db;
    private EstoqueInsumosValidator $validator;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
        $this->validator = new EstoqueInsumosValidator();
    }

    public function getAll(): array
{
    $stmt = $this->db->prepare("
    with produto_insumo as (
        select
        p.nome,
        p.marca,
        p.unidade_medida,
        p.tipo,
        i.id as insumo_id,
        i.data_referencia,
        i.valor_por_dose 
    from
        insumo i
    join produto p on
        i.produto_id = p.id
    )
    select 
        *,
        quantidade * valor_por_dose as valor_total_em_produto
    from estoque_insumos ei 
    join produto_insumo pi on pi.insumo_id = ei.insumo_id 
    ");
    $stmt->execute();
    $estoques = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $estoques;
}

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM estoque_insumos"
        );
        $stmt->execute();
        $estoques = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require_once __DIR__ . '/../views/estoques.php';
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        if ($this->validator->EstoqueParaEsteInsumoJaExiste((int) $dados['insumo_id'])) {
            $this->setToast('error', 'Erro', 'Já existe um estoque cadastrado para este insumo. Atualize o valor gerando uma entrada de insumo via operação financeira.');
            $this->redirect('estoques');
        }

        $dados = [
            'propriedade_id'           => trim($_POST['propriedade_id']           ?? ''),
            'insumo_id'      => trim($_POST['insumo_id']      ?? ''),
            'quantidade'      => trim($_POST['quantidade']      ?? 0),
        ];

        if (empty($dados['insumo_id'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Insumo é obrigatório.');
            $this->redirect('estoque_insumos');
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO estoque_insumos (propriedade_id, insumo_id, quantidade) VALUES (?, ?, ?)"
            );
            $stmt->bind_param(
                "iif",
                $dados['propriedade_id'], $dados['insumo_id'], $dados['quantidade']
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir estoque: {$this->db->error}");
            }
            $estoqueId = $this->db->insert_id;
            $stmt->close();

            $this->setToast('success', 'Estoque Cadastrado!', "O estoque foi adicionado com sucesso.");
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar o estoque. Tente novamente.'. $e->getMessage());
            $this->redirect('produtos_culturas');
        }
    }
}
