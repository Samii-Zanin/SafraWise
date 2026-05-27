<?php
/*
  app/controllers/OperacoesFinanceirasController.php
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/ProdutoController.php';

class OperacoesFinanceirasController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function storeCompraInsumo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $produto_id     = (int)   ($_POST['produto_id']     ?? 0);
        $valor_total    = (float) str_replace(',', '.', $_POST['valor_operacao'] ?? 0);
        $quantidade     = (float) str_replace(',', '.', $_POST['quantidade']     ?? 0);
        $descricao      = trim(   $_POST['descricao']       ?? '');
        $propriedade_id = (int)   ($_POST['propriedade_id'] ?? 0);
        $safra_id       = !empty($_POST['safra_id']) ? (int) $_POST['safra_id'] : null;

        if (!$produto_id) {
            $this->setToast('warning', 'Campo obrigatório', 'Selecione um produto.');
            $this->redirect('estoques');
            return;
        }
        if ($valor_total <= 0 || $quantidade <= 0) {
            $this->setToast('warning', 'Valores inválidos', 'Valor e quantidade devem ser maiores que zero.');
            $this->redirect('estoques');
            return;
        }
        if (!$propriedade_id) {
            $this->setToast('warning', 'Campo obrigatório', 'Selecione a propriedade de destino.');
            $this->redirect('estoques');
            return;
        }

        $valor_por_dose = round($valor_total / $quantidade, 4);

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO operacoes_financeiras
                    (tipo_operacao, valor_operacao, descricao, produto_id, quantidade, safra_id, data_operacao)
                 VALUES ('COMPRA DE INSUMOS', ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param("dsidi", $valor_total, $descricao, $produto_id, $quantidade, $safra_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir operação: {$this->db->error}");
            }
            $stmt->close();

            $stmt = $this->db->prepare("SELECT id, valor_por_dose FROM insumo WHERE produto_id = ? LIMIT 1");
            $stmt->bind_param("i", $produto_id);
            $stmt->execute();
            $insumo = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($insumo) {
            $insumo_id          = $insumo['id'];
            $valor_dose_atual   = (float) $insumo['valor_por_dose'];

            $stmtQtd = $this->db->prepare(
                "SELECT quantidade FROM estoque_insumos
                WHERE insumo_id = ? AND propriedade_id = ? LIMIT 1"
            );
            $stmtQtd->bind_param("ii", $insumo_id, $propriedade_id);
            $stmtQtd->execute();
            $estoqueAtual = $stmtQtd->get_result()->fetch_assoc();
            $stmtQtd->close();

            $qtd_atual = $estoqueAtual ? (float) $estoqueAtual['quantidade'] : 0;

            $total_qtd  = $qtd_atual + $quantidade;
            $valor_por_dose_medio = $total_qtd > 0
                ? ($qtd_atual * $valor_dose_atual + $quantidade * $valor_por_dose) / $total_qtd
                : $valor_por_dose;

            $stmt = $this->db->prepare("UPDATE insumo SET valor_por_dose = ? WHERE id = ?");
            $stmt->bind_param("di", $valor_por_dose_medio, $insumo_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar insumo: {$this->db->error}");
            }
            $stmt->close();

        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO insumo (produto_id, valor_por_dose) VALUES (?, ?)"
            );
            $stmt->bind_param("id", $produto_id, $valor_por_dose);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao criar insumo: {$this->db->error}");
            }
            $insumo_id = $this->db->insert_id;
            $stmt->close();
        }

            $stmt = $this->db->prepare(
                "SELECT id, quantidade FROM estoque_insumos
                 WHERE insumo_id = ? AND propriedade_id = ? LIMIT 1"
            );
            $stmt->bind_param("ii", $insumo_id, $propriedade_id);
            $stmt->execute();
            $estoque = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($estoque) {
                $nova_qtd = $estoque['quantidade'] + $quantidade;
                $stmt = $this->db->prepare(
                    "UPDATE estoque_insumos SET quantidade = ? WHERE id = ?"
                );
                $stmt->bind_param("di", $nova_qtd, $estoque['id']);
            } else {
                $stmt = $this->db->prepare(
                    "INSERT INTO estoque_insumos (insumo_id, quantidade, propriedade_id)
                     VALUES (?, ?, ?)"
                );
                $stmt->bind_param("idi", $insumo_id, $quantidade, $propriedade_id);
            }

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar estoque: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Compra Registrada!',
                'A operação financeira foi salva e o estoque atualizado.');
            $this->redirect('estoques');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível registrar a compra. Tente novamente.'. $e->getMessage());
            $this->redirect('estoques');
        }
    }

    public function VendaCereal(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $tipo_operacao  = 'VENDA DE CEREAIS';
        $valor_total    = (float) str_replace(',', '.', $_POST['valor_operacao'] ?? 0);
        $descricao      = trim(   $_POST['descricao']       ?? '');
        $quantidade     = (float) str_replace(',', '.', $_POST['quantidade']     ?? 0);
        $produto_id     = (int)   ($_POST['produto_id']     ?? 0);
        $propriedade_id = (int)   ($_POST['propriedade_id'] ?? 0);
        $safra_id       = !empty($_POST['safra_id']) ? (int) $_POST['safra_id'] : null;

        if (!$produto_id) {
            $this->setToast('warning', 'Campo obrigatório', 'Selecione um produto.');
            $this->redirect('estoques');
            return;
        }
        if ($valor_total <= 0 || $quantidade <= 0) {
            $this->setToast('warning', 'Valores inválidos', 'Valor e quantidade devem ser maiores que zero.');
            $this->redirect('estoques');
            return;
        }
        if (!$propriedade_id) {
            $this->setToast('warning', 'Campo obrigatório', 'Selecione a propriedade de destino.');
            $this->redirect('estoques');
            return;
        }

        try {

            $smtdProduto = $this->db->prepare("SELECT id, nome FROM produto WHERE id = ? AND tipo = 'CEREAL' LIMIT 1");
            $smtdProduto->bind_param("i", $produto_id);
            $smtdProduto->execute();
            $produto = $smtdProduto->get_result()->fetch_assoc();
            $smtdProduto->close();
            if (!$produto) {
                $this->setToast('error', 'Produto inválido', 'O produto selecionado não é um cereal válido.');
                $this->redirect('estoques');
                return;
            }

            $stmtQtd = $this->db->prepare(
                "SELECT quantidade_kg FROM silo
                WHERE cultura = ? AND propriedade_id = ? LIMIT 1"
            );
            $stmtQtd->bind_param("si", $produto['nome'], $propriedade_id);
            $stmtQtd->execute();
            $estoqueAtual = $stmtQtd->get_result()->fetch_assoc();
            $stmtQtd->close();
            

            $qtd_atual = $estoqueAtual ? (float) $estoqueAtual['quantidade_kg'] : 0;

            $nova_quantidade          = $qtd_atual - $quantidade;
            

            if ($estoqueAtual) {
                if ($nova_quantidade < 0) {
                $this->setToast('warning', 'Estoque insuficiente', 'Não há cereal suficiente no silo para registrar esta venda.');
                $this->redirect('estoques');
                return;
            }
                $nova_quantidade = $estoqueAtual['quantidade_kg'] - $quantidade;
                $stmt = $this->db->prepare(
                    "UPDATE silo SET quantidade_kg = ? WHERE cultura = ? AND propriedade_id = ?"
                );
                $stmt->bind_param("dsi", $nova_quantidade, $produto['nome'], $propriedade_id);
                if (!$stmt->execute()) {
                    throw new \RuntimeException("Falha ao atualizar silo: {$this->db->error}");
                }
                $stmt->close();
            } else {
                $this->setToast('error', 'Erro de estoque', 'Não foi possível encontrar o estoque do cereal para esta propriedade.');
                $this->redirect('estoques');
                return;
            }
            $stmt = $this->db->prepare(
                "INSERT INTO operacoes_financeiras
                    (tipo_operacao, valor_operacao, descricao, produto_id, quantidade, safra_id, data_operacao)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param("sdsidi",$tipo_operacao, $valor_total, $descricao, $produto_id, $quantidade, $safra_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir operação: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Compra Registrada!',
                'A operação financeira foi salva e o estoque atualizado.');
            $this->redirect('estoques');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível registrar a compra. Tente novamente.'. $e->getMessage());
            $this->redirect('estoques');
        }
    }


    public function registrarSaidaSimplesCereal(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $produto_id     = (int)   ($_POST['produto_id']     ?? 0);
        $quantidade     = (float) str_replace(',', '.', $_POST['quantidade'] ?? 0);
        $propriedade_id = (int)   ($_POST['propriedade_id'] ?? 0);
        $descricao      = trim(   $_POST['descricao']       ?? 'Saída de estoque sem vínculo financeiro');

        if (!$produto_id || $quantidade <= 0 || !$propriedade_id) {
            $this->setToast('warning', 'Campos obrigatórios', 'Preencha todos os campos.');
            $this->redirect('estoques');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT id, nome FROM produto WHERE id = ? AND tipo = 'CEREAL' LIMIT 1"
            );
            $stmt->bind_param("i", $produto_id);
            $stmt->execute();
            $produto = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$produto) {
                $this->setToast('error', 'Produto inválido', 'O produto não é um cereal válido.');
                $this->redirect('estoques');
                return;
            }

            $stmt = $this->db->prepare(
                "SELECT quantidade_kg FROM silo WHERE cultura = ? AND propriedade_id = ? LIMIT 1"
            );
            $stmt->bind_param("si", $produto['nome'], $propriedade_id);
            $stmt->execute();
            $estoque = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$estoque) {
                $this->setToast('error', 'Sem estoque', 'Nenhum silo encontrado para este cereal e propriedade.');
                $this->redirect('estoques');
                return;
            }

            $qtd_atual = (float) $estoque['quantidade_kg'];
            if ($qtd_atual < $quantidade) {
                $this->setToast('warning', 'Estoque insuficiente',
                    'Disponível: ' . number_format($qtd_atual, 2, ',', '.') . ' kg.');
                $this->redirect('estoques');
                return;
            }

            $nova_qtd = $qtd_atual - $quantidade;
            $stmt = $this->db->prepare(
                "UPDATE silo SET quantidade_kg = ? WHERE cultura = ? AND propriedade_id = ?"
            );
            $stmt->bind_param("dsi", $nova_qtd, $produto['nome'], $propriedade_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar silo: {$this->db->error}");
            }
            $stmt->close();

            $valor_zero = 0.0;
            $stmt = $this->db->prepare(
                "INSERT INTO operacoes_financeiras
                    (tipo_operacao, valor_operacao, descricao, produto_id, quantidade, data_operacao)
                VALUES ('SAÍDA DE CEREAIS', ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param("dsid", $valor_zero, $descricao, $produto_id, $quantidade);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir operação: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Saída Registrada!', 'O silo foi atualizado com sucesso.');
            $this->redirect('estoques');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível registrar a saída.');
            $this->redirect('estoques');
        }
    }


    public function getMovimentacoesEstoqueInsumos(): array
{
    if (!isset($_SESSION['user'])) {
        $this->redirect('login');
    }

        $stmt = $this->db->prepare("
        with safras as (
    select
        s.id, 
        s.data_inicio,
        s.data_fim,
        t.nome as talhao_nome,
        c.nome as cultura_plantada,
        p.nome as propriedade_nome
    from
        safra s
    left join talhoes t on
    s.talhao_id = t.id
    left join cultura c on 
    s.cultura_id= c.id
    join propriedade p on p.id = t.propriedade_id
    )
    -- Adicione o join de produto
select
    opf.id as operacao_id,
    opf.tipo_operacao,
    opf.descricao,
    opf.valor_operacao,
    opf.quantidade,
    ROUND(opf.valor_operacao / NULLIF(opf.quantidade, 0), 2) as valor_unitario,
    opf.data_operacao,
    UPPER(p.nome)      AS nome_produto,  
    p.marca     AS marca_produto, 
    p.tipo      AS tipo,
    s.talhao_nome,
    s.cultura_plantada,
    s.propriedade_nome
from operacoes_financeiras opf
left join produto p  ON p.id  = opf.produto_id 
left join safras s   ON s.id  = opf.safra_id
where opf.tipo_operacao in ('COMPRA DE INSUMOS', 'VENDA DE INSUMOS')
        ");
        $stmt->execute();
        $movimentacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $movimentacoes;
    }

    public function getMovimentacoesSilos(): array
{
    if (!isset($_SESSION['user'])) {
        $this->redirect('login');
    }

        $stmt = $this->db->prepare("
        with safras as (
    select
        s.id, 
        s.data_inicio,
        s.data_fim,
        t.nome as talhao_nome,
        c.nome as cultura_plantada,
        p.nome as propriedade_nome,
        p.id as propriedade_id
    from
        safra s
    left join talhoes t on
    s.talhao_id = t.id
    left join cultura c on 
    s.cultura_id= c.id
    join propriedade p on p.id = t.propriedade_id
    )
select
    opf.id as operacao_id,
    opf.tipo_operacao,
    opf.descricao,
    opf.valor_operacao,
    opf.quantidade,
    ROUND(opf.valor_operacao / NULLIF(opf.quantidade, 0), 2) * 60 as valor_unitario,
    opf.data_operacao,
    UPPER(p.nome)      AS nome_produto,  
    p.tipo      AS tipo,
    s.talhao_nome,
    s.cultura_plantada,
    si.nome as nome_silo,
    si.cultura as produto_armazenado
from operacoes_financeiras opf
left join produto p  ON p.id  = opf.produto_id 
left join safras s   ON s.id  = opf.safra_id
left join silo si on si.propriedade_id = s.propriedade_id
where opf.tipo_operacao in ('SAÍDA DE CEREAIS', 'VENDA DE CEREAIS')
        ");
        $stmt->execute();
        $movimentacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $movimentacoes;
    }

    public function getBySafra(int $safraId): array
    {
        if (!isset($_SESSION['user'])) return [];

        $stmt = $this->db->prepare("
            SELECT
                opf.id          AS operacao_id,
                opf.tipo_operacao,
                opf.descricao,
                opf.valor_operacao,
                opf.quantidade,
                opf.data_operacao,
                ROUND(opf.valor_operacao / NULLIF(opf.quantidade, 0), 2) AS valor_unitario,
                p.nome          AS nome_produto,
                p.tipo          AS tipo_produto
            FROM operacoes_financeiras opf
            LEFT JOIN produto p ON p.id = opf.produto_id
            WHERE opf.safra_id = ?
            ORDER BY opf.data_operacao ASC
        ");
        $stmt->bind_param("i", $safraId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }
}
