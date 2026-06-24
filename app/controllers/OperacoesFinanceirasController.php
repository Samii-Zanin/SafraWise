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

    // ─── helpers privados ────────────────────────────────────────────────────

    /** Retorna o proprietario_id do usuário logado (funciona para peão e proprietário). */
    private function getProprietarioId(): int
    {
        return (int) ($_SESSION['proprietario_id'] ?? 0);
    }

    /**
     * Garante que a propriedade informada pertence ao proprietário logado.
     * Evita que um usuário manipule dados de outra fazenda via POST forjado.
     */
    private function validarPropriedade(int $propriedade_id): bool
    {
        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare(
            "SELECT id FROM propriedade WHERE id = ? AND proprietario_id = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $propriedade_id, $proprietario_id);
        $stmt->execute();
        $ok = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $ok;
    }

    // ─── ações ───────────────────────────────────────────────────────────────

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

        // Segurança: propriedade deve pertencer ao proprietário logado
        if (!$this->validarPropriedade($propriedade_id)) {
            $this->setToast('error', 'Acesso negado', 'Esta propriedade não pertence à sua conta.');
            $this->redirect('estoques');
            return;
        }

        $valor_por_dose = round($valor_total / $quantidade, 4);

        try {
            // 1. Registra a operação financeira
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

            // 2. Busca ou cria o insumo vinculado ao produto
            $stmt = $this->db->prepare(
                "SELECT id, valor_por_dose FROM insumo WHERE produto_id = ? LIMIT 1"
            );
            $stmt->bind_param("i", $produto_id);
            $stmt->execute();
            $insumo = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($insumo) {
                $insumo_id        = $insumo['id'];
                $valor_dose_atual = (float) $insumo['valor_por_dose'];

                // Custo médio ponderado
                $stmtQtd = $this->db->prepare(
                    "SELECT quantidade FROM estoque_insumos
                     WHERE insumo_id = ? AND propriedade_id = ? LIMIT 1"
                );
                $stmtQtd->bind_param("ii", $insumo_id, $propriedade_id);
                $stmtQtd->execute();
                $estoqueAtual = $stmtQtd->get_result()->fetch_assoc();
                $stmtQtd->close();

                $qtd_atual           = $estoqueAtual ? (float) $estoqueAtual['quantidade'] : 0;
                $total_qtd           = $qtd_atual + $quantidade;
                $valor_por_dose_medio = $total_qtd > 0
                    ? ($qtd_atual * $valor_dose_atual + $quantidade * $valor_por_dose) / $total_qtd
                    : $valor_por_dose;

                $stmt = $this->db->prepare(
                    "UPDATE insumo SET valor_por_dose = ?, data_referencia = CURDATE() WHERE id = ?"
                );
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

            // 3. Atualiza ou cria o estoque da propriedade
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
            $this->setToast('error', 'Erro interno',
                'Não foi possível registrar a compra. Tente novamente.' . $e->getMessage());
            $this->redirect('estoques');
        }
    }

    public function VendaCereal(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

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

        if (!$this->validarPropriedade($propriedade_id)) {
            $this->setToast('error', 'Acesso negado', 'Esta propriedade não pertence à sua conta.');
            $this->redirect('estoques');
            return;
        }

        try {
            // Valida que o produto é um cereal
            $stmt = $this->db->prepare(
                "SELECT id, nome FROM produto WHERE id = ? AND tipo = 'CEREAL' LIMIT 1"
            );
            $stmt->bind_param("i", $produto_id);
            $stmt->execute();
            $produto = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$produto) {
                $this->setToast('error', 'Produto inválido', 'O produto selecionado não é um cereal válido.');
                $this->redirect('estoques');
                return;
            }

            // Verifica saldo no silo da propriedade
            $stmt = $this->db->prepare(
                "SELECT quantidade_kg FROM silo
                 WHERE cultura = ? AND propriedade_id = ? LIMIT 1"
            );
            $stmt->bind_param("si", $produto['nome'], $propriedade_id);
            $stmt->execute();
            $estoqueAtual = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$estoqueAtual) {
                $this->setToast('error', 'Sem estoque',
                    'Nenhum silo encontrado para este cereal nesta propriedade.');
                $this->redirect('estoques');
                return;
            }

            $qtd_atual     = (float) $estoqueAtual['quantidade_kg'];
            $nova_quantidade = $qtd_atual - $quantidade;

            if ($nova_quantidade < 0) {
                $this->setToast('warning', 'Estoque insuficiente',
                    'Não há cereal suficiente no silo para registrar esta venda.');
                $this->redirect('estoques');
                return;
            }

            // Débita do silo
            $stmt = $this->db->prepare(
                "UPDATE silo SET quantidade_kg = ? WHERE cultura = ? AND propriedade_id = ?"
            );
            $stmt->bind_param("dsi", $nova_quantidade, $produto['nome'], $propriedade_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar silo: {$this->db->error}");
            }
            $stmt->close();

            // Registra a operação
            $tipo_operacao = 'VENDA DE CEREAIS';
            $stmt = $this->db->prepare(
                "INSERT INTO operacoes_financeiras
                    (tipo_operacao, valor_operacao, descricao, produto_id, quantidade, safra_id, data_operacao)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param("sdsidi", $tipo_operacao, $valor_total, $descricao,
                              $produto_id, $quantidade, $safra_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir operação: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Venda Registrada!',
                'A operação financeira foi salva e o silo atualizado.');
            $this->redirect('estoques');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível registrar a venda. Tente novamente.' . $e->getMessage());
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

        if (!$this->validarPropriedade($propriedade_id)) {
            $this->setToast('error', 'Acesso negado', 'Esta propriedade não pertence à sua conta.');
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
                $this->setToast('error', 'Sem estoque',
                    'Nenhum silo encontrado para este cereal e propriedade.');
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

    // ─── queries de leitura ──────────────────────────────────────────────────

    public function getMovimentacoesEstoqueInsumos(): array
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $proprietario_id = $this->getProprietarioId();

        // Filtra pelas propriedades do proprietário logado
        $stmt = $this->db->prepare("
            WITH safras AS (
                SELECT
                    s.id,
                    s.data_inicio,
                    s.data_fim,
                    t.nome  AS talhao_nome,
                    c.nome  AS cultura_plantada,
                    p.nome  AS propriedade_nome,
                    p.id    AS propriedade_id
                FROM safra s
                LEFT JOIN talhoes    t ON t.id = s.talhao_id
                LEFT JOIN cultura    c ON c.id = s.cultura_id
                JOIN  propriedade    p ON p.id = t.propriedade_id
                WHERE p.proprietario_id = ?
            )
            SELECT
                opf.id              AS operacao_id,
                opf.tipo_operacao,
                opf.descricao,
                opf.valor_operacao,
                opf.quantidade,
                ROUND(opf.valor_operacao / NULLIF(opf.quantidade, 0), 2) AS valor_unitario,
                opf.data_operacao,
                UPPER(p.nome)       AS nome_produto,
                p.marca             AS marca_produto,
                p.tipo              AS tipo,
                s.talhao_nome,
                s.cultura_plantada,
                s.propriedade_nome
            FROM operacoes_financeiras opf
            LEFT JOIN produto p ON p.id = opf.produto_id
            LEFT JOIN safras  s ON s.id = opf.safra_id
            -- Inclui compras sem safra vinculada, desde que o produto esteja
            -- no estoque de alguma propriedade do proprietário logado
            WHERE opf.tipo_operacao IN ('COMPRA DE INSUMOS', 'VENDA DE INSUMOS')
              AND (
                  s.propriedade_id IS NOT NULL
                  OR EXISTS (
                      SELECT 1 FROM estoque_insumos ei
                      JOIN insumo i ON i.id = ei.insumo_id
                      JOIN propriedade pr ON pr.id = ei.propriedade_id
                      WHERE i.produto_id = opf.produto_id
                        AND pr.proprietario_id = ?
                  )
              )
        ");
        $stmt->bind_param("ii", $proprietario_id, $proprietario_id);
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

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare("
            WITH safras AS (
                SELECT
                    s.id,
                    s.data_inicio,
                    s.data_fim,
                    t.nome  AS talhao_nome,
                    c.nome  AS cultura_plantada,
                    p.nome  AS propriedade_nome,
                    p.id    AS propriedade_id
                FROM safra s
                LEFT JOIN talhoes  t ON t.id = s.talhao_id
                LEFT JOIN cultura  c ON c.id = s.cultura_id
                JOIN  propriedade  p ON p.id = t.propriedade_id
                WHERE p.proprietario_id = ?
            )
            SELECT
                opf.id              AS operacao_id,
                opf.tipo_operacao,
                opf.descricao,
                opf.valor_operacao,
                opf.quantidade,
                ROUND(opf.valor_operacao / NULLIF(opf.quantidade, 0), 2) * 60 AS valor_unitario,
                opf.data_operacao,
                UPPER(p.nome)       AS nome_produto,
                p.tipo              AS tipo,
                s.talhao_nome,
                s.cultura_plantada,
                si.nome             AS nome_silo,
                si.cultura          AS produto_armazenado
            FROM operacoes_financeiras opf
            LEFT JOIN produto     p  ON p.id  = opf.produto_id
            LEFT JOIN safras      s  ON s.id  = opf.safra_id
            -- Silos da propriedade correta (evita produto de silo de outro dono)
            LEFT JOIN silo        si ON si.propriedade_id = s.propriedade_id
                                    AND UPPER(si.cultura) = UPPER(p.nome)
            WHERE opf.tipo_operacao IN ('SAÍDA DE CEREAIS', 'VENDA DE CEREAIS')
              AND s.propriedade_id IS NOT NULL
        ");
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $movimentacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $movimentacoes;
    }

    public function getBySafra(int $safraId): array
    {
        if (!isset($_SESSION['user'])) return [];

        $proprietario_id = $this->getProprietarioId();

        // Confirma que a safra pertence ao proprietário antes de expor os dados
        $stmt = $this->db->prepare("
            SELECT s.id FROM safra s
            JOIN talhoes   t ON t.id = s.talhao_id
            JOIN propriedade p ON p.id = t.propriedade_id
            WHERE s.id = ? AND p.proprietario_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $safraId, $proprietario_id);
        $stmt->execute();
        $safraValida = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$safraValida) return [];

        $stmt = $this->db->prepare("
            WITH op_agricolas AS (
                SELECT
                    oa.id,
                    oa.tipo_operacao,
                    oa.data_operacao,
                    oa.descricao,
                    oa.custos_operacao   AS valor_operacao,
                    oa.quantidade_insumo AS quantidade,
                    p.nome               AS produto_nome,
                    p.tipo               AS tipo_produto,
                    'Operação Agrícola'  AS operacao,
                    pe.nome              AS nome_peao
                FROM operacoes_agricolas oa
                LEFT JOIN insumo  i  ON i.id  = oa.insumo_id
                LEFT JOIN produto p  ON p.id  = i.produto_id
                LEFT JOIN peao    pe ON pe.id = oa.peao_id
                WHERE oa.safra_id = ?
            ),
            op_financeiras AS (
                SELECT
                    opf.id,
                    opf.tipo_operacao,
                    opf.data_operacao,
                    opf.descricao,
                    0                       AS valor_operacao,
                    opf.quantidade,
                    p.nome                  AS produto_nome,
                    p.tipo                  AS tipo_produto,
                    'Operação Financeira'   AS operacao,
                    NULL                    AS nome_peao
                FROM operacoes_financeiras opf
                LEFT JOIN produto p ON p.id = opf.produto_id
                WHERE opf.safra_id = ?
            )
            SELECT * FROM op_financeiras
            UNION ALL
            SELECT * FROM op_agricolas
            ORDER BY data_operacao ASC
        ");
        $stmt->bind_param("ii", $safraId, $safraId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    public function getReceitaSafra(int $safra_id): float
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $proprietario_id = $this->getProprietarioId();
        $stmt = $this->db->prepare("
            SELECT SUM(opf.valor_operacao)
            FROM operacoes_financeiras opf
            JOIN safra       s  ON s.id  = opf.safra_id
            JOIN talhoes     t  ON t.id  = s.talhao_id
            JOIN propriedade p  ON p.id  = t.propriedade_id
            WHERE opf.safra_id = ?
              AND opf.tipo_operacao = 'VENDA DE CEREAIS'
              AND p.proprietario_id = ?
        ");
        $stmt->bind_param("ii", $safra_id, $proprietario_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_row();
        $stmt->close();

        return $row ? (float) ($row[0] ?? 0) : 0.0;
    }
}