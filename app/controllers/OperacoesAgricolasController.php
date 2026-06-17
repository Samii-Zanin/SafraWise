<?php
/*
  app/controllers/OperacoesAgricolasController.php
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/ProdutoController.php';
require_once __DIR__ . '/validator/OperacoesAgricolasValidator.php';

class OperacoesAgricolasController extends BaseController
{
    private mysqli $db;
    private OperacoesAgricolasValidator $validator;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
        $this->validator = new OperacoesAgricolasValidator();
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            select
                oa.id,
                oa.tipo_operacao,
                oa.data_operacao ,
                oa.descricao,
                i.valor_por_dose as valor_pago_por_un,
                p.nome as nome_produto,
                p.tipo as tipo_produto,
                p.unidade_medida,
                oa.quantidade_insumo, 
                s.data_inicio as data_inicio_safra,
                s.data_fim as data_fim_safra,
                c.nome as nome_cultura,
                t.nome as nome_talhao,
                prprdd.nome as nome_propriedade,
                prprdd.estado as estado_propriedade,
                pe.nome as nome_peao
            from
                operacoes_agricolas oa
            left join insumo i on
                i.id = insumo_id
                left join produto p on
                i.produto_id = p.id 
            left join safra s on
                s.id = safra_id
                left join cultura c on
                s.cultura_id = c.id
            left join talhoes t on
                t.id = oa.talhao_id
                left join propriedade prprdd
                on prprdd.id = t.propriedade_id 
            left join peao pe on
                pe.id = oa.peao_id
            where
                prprdd.proprietario_id = ?
        ");
        
        // Usa o ID do dono da fazenda para listar todo o histórico
        $proprietarioId = $_SESSION['proprietario_id'];
        $stmt->bind_param("i", $proprietarioId);
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $operacoes = $this->getAll();

        require_once __DIR__ . '/../views/operacoes_agricolas.php';
    }
    
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $tipo_operacao = trim($_POST['tipo_operacao']    ?? '');
        $data_operacao = trim($_POST['data_operacao']    ?? '');
        $descricao     = trim($_POST['descricao']        ?? '');
        $quantidade    = (float) ($_POST['quantidade_insumo'] ?? 0);
        $custo         = (float) ($_POST['custo_operacao']    ?? 0);
        $talhao_id     = (int)   ($_POST['talhao_id']    ?? 0);
        $safra_id      = (int)   ($_POST['safra_id']     ?? 0);

        $insumo_id = !empty($_POST['insumo_id']) && (int)$_POST['insumo_id'] > 0
            ? (int) $_POST['insumo_id'] : null;

        //  Se for peão, amarra a operação no nome dele.
        if ($_SESSION['tipo'] === 'peao') {
            $peao_id = $_SESSION['user']['id'];
        } else {
            // Se for proprietário, ele pode escolher no select quem fez a operação
            $peao_id = !empty($_POST['peao_id']) && (int)$_POST['peao_id'] > 0
                ? (int) $_POST['peao_id'] : null;
        }

        // ── Validações ───────────────────────────────────────────
        if (!$this->validator->StatusSafraVinculada($safra_id)) {
            $this->setToast('error', 'Safra Inativa',
                'A safra vinculada está inativa ou o talhão está vazio.');
            $this->redirect('safra_detalhe&id=' . $safra_id);
            return;
        }

        if ($insumo_id && !$this->validator->EstoqueInsumoDisponivel($insumo_id, $quantidade)) {
            $this->setToast('error', 'Estoque Insuficiente',
                'Quantidade de insumo insuficiente em estoque.');
            $this->redirect('safra_detalhe&id=' . $safra_id);
            return;
        }

        try {
            if ($insumo_id && $quantidade > 0) {
                $propriedade_id = $this->validator->getPropriedadeIdByTalhao($talhao_id);
                $estoque        = $this->validator->BuscaDadosdeEstoque($insumo_id, $propriedade_id);

                if (!empty($estoque)) {
                    $estoque_id   = (int)   $estoque['id'];
                    $qtd_restante = (float) $estoque['quantidade'] - $quantidade;

                    $stmt = $this->db->prepare(
                        "UPDATE estoque_insumos SET quantidade = ? WHERE id = ?"
                    );
                    $stmt->bind_param("di", $qtd_restante, $estoque_id);
                    if (!$stmt->execute()) {
                        throw new \RuntimeException("Falha ao atualizar estoque: {$this->db->error}");
                    }
                    $stmt->close();

                    if ($qtd_restante < ($quantidade * 0.1)) {
                        $this->setToast('warning', 'Estoque Crítico',
                            'O estoque do insumo está muito baixo. Considere reabastecer.');
                    }
                    $stmt = $this->db->prepare("
                    INSERT INTO operacoes_agricolas
                        (tipo_operacao, data_operacao, descricao, quantidade_insumo,
                        insumo_id, talhao_id, peao_id, safra_id, custos_operacao)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param(
                        "sssdiiiid",
                        $tipo_operacao,
                        $data_operacao,
                        $descricao,
                        $quantidade,
                        $insumo_id,
                        $talhao_id,
                        $peao_id,
                        $safra_id,
                        $custo
                    );
                    if (!$stmt->execute()) {
                        throw new \RuntimeException("Falha ao inserir operação: {$this->db->error}");
                    }
                    $stmt->close();

                } else {
                    $this->setToast('warning', 'Sem Estoque',
                        'Não possui nenhum estoque nesta propriedade para este insumo.');
                    $this->redirect('safra_detalhe&id=' . $safra_id);
                    return;
                }
            }

            $this->setToast('success', 'Operação Registrada!',
                'A operação agrícola foi salva e o estoque atualizado.');
            $this->redirect('safra_detalhe&id=' . $safra_id);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', $e->getMessage());
            $this->redirect('safra_detalhe&id=' . $safra_id);
        }
    }
    
    public function storeColheita(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $safra_id         = (int)   ($_POST['safra_id']         ?? 0);
        $silo_id          = (int)   ($_POST['silo_id']          ?? 0);
        $quantidade       = (float) ($_POST['quantidade_colhida'] ?? 0);
        $talhao_id        = (int)   ($_POST['talhao_id']        ?? 0);
        $data_operacao    = trim(    $_POST['data_operacao']     ?? date('Y-m-d'));
        $descricao        = trim(    $_POST['descricao']         ?? '');
        $custo            = (float) ($_POST['custo_operacao']    ?? 0);

        //  Se for peão, amarra a operação no nome dele.
        if ($_SESSION['tipo'] === 'peao') {
            $peao_id = $_SESSION['user']['id'];
        } else {
            $peao_id = !empty($_POST['peao_id']) ? (int)$_POST['peao_id'] : null;
        }

        if (strlen($data_operacao) === 10) {
            $data_operacao .= ' ' . date('H:i:s');
        }

        if (!$safra_id || !$silo_id || $quantidade <= 0) {
            $this->setToast('warning', 'Campos obrigatórios',
                'Safra, silo e quantidade são obrigatórios.');
            $this->redirect('estoques');
            return;
        }

        if (!$this->validator->StatusSafraVinculada($safra_id)) {
            $this->setToast('error', 'Safra Inativa', 'A safra selecionada está inativa.');
            $this->redirect('estoques');
            return;
        }

        try {
            $stmtSilo = $this->db->prepare(
                "SELECT quantidade_kg, capacidade_kg FROM silo WHERE id = ? LIMIT 1"
            );
            $stmtSilo->bind_param("i", $silo_id);
            $stmtSilo->execute();
            $silo = $stmtSilo->get_result()->fetch_assoc();
            $stmtSilo->close();

            if (!$silo) {
                $this->setToast('error', 'Silo não encontrado', 'O silo selecionado não existe.');
                $this->redirect('estoques');
                return;
            }

            $disponivel = (float)$silo['capacidade_kg'] - (float)$silo['quantidade_kg'];
            if ($quantidade > $disponivel) {
                $this->setToast('error', 'Capacidade excedida',
                    'A quantidade colhida excede a capacidade disponível do silo (' .
                    number_format($disponivel, 2, ',', '.') . ' kg disponíveis).');
                $this->redirect('estoques');
                return;
            }

            // ── INSERT operação agrícola ─────────────────────────
            $tipo = 'COLHEITA';
            $stmt = $this->db->prepare("
                INSERT INTO operacoes_agricolas
                    (tipo_operacao, data_operacao, descricao, quantidade_insumo,
                     insumo_id, talhao_id, peao_id, safra_id, custos_operacao)
                VALUES (?, ?, ?, 0, NULL, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "sssiiid",
                $tipo,
                $data_operacao,
                $descricao,
                $talhao_id,
                $peao_id,
                $safra_id,
                $custo
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir colheita: {$this->db->error}");
            }
            $stmt->close();

            // ── Atualiza silo ────────────────────────────────────
            $nova_qtd = (float)$silo['quantidade_kg'] + $quantidade;
            $stmt = $this->db->prepare(
                "UPDATE silo SET quantidade_kg = ? WHERE id = ?"
            );
            $stmt->bind_param("di", $nova_qtd, $silo_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar silo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Colheita Registrada!',
                'A operação de colheita foi salva e o silo atualizado.');
            $this->redirect('estoques');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', $e->getMessage());
            $this->redirect('estoques');
        }
    }
}