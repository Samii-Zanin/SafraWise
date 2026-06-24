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
        $this->db        = Conexao::getConexao();
        $this->validator = new EstoqueInsumosValidator();
    }

    // ─── helper privado ──────────────────────────────────────────────────────

    private function getProprietarioId(): int
    {
        return (int) ($_SESSION['proprietario_id'] ?? 0);
    }

    /**
     * Valida que a propriedade pertence ao proprietário logado.
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

    // ─── leitura ─────────────────────────────────────────────────────────────

    public function getAll(): array
    {
        if (!isset($_SESSION['user'])) return [];

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare("
            WITH produto_insumo AS (
                SELECT
                    p.nome,
                    p.marca,
                    p.unidade_medida,
                    p.tipo,
                    i.id             AS insumo_id,
                    i.data_referencia,
                    i.valor_por_dose
                FROM insumo i
                JOIN produto p ON p.id = i.produto_id
            )
            SELECT
                ei.*,
                pi.*,
                pr.nome                             AS propriedade_nome,
                (ei.quantidade * pi.valor_por_dose) AS valor_total_em_produto
            FROM estoque_insumos ei
            JOIN produto_insumo pi ON pi.insumo_id = ei.insumo_id
            JOIN propriedade    pr ON pr.id         = ei.propriedade_id
            WHERE pr.proprietario_id = ?
            ORDER BY pr.nome, pi.nome
        ");
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $estoques = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $estoques;
    }

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        $estoques = $this->getAll();

        require_once __DIR__ . '/../views/estoques.php';
    }

    // ─── escrita ─────────────────────────────────────────────────────────────

    /**
     * Inicializa um registro de estoque do zero (saldo inicial).
     * Apenas proprietário. Entradas posteriores devem usar storeEntrada().
     */
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode inicializar um novo controle de estoque.');
            $this->redirect('estoques');
            return;
        }

        $propriedade_id = (int)   ($_POST['propriedade_id'] ?? 0);
        $insumo_id      = (int)   ($_POST['insumo_id']      ?? 0);
        $quantidade     = (float) ($_POST['quantidade']      ?? 0);

        // Validações básicas primeiro, antes de qualquer query
        if (!$insumo_id) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Insumo é obrigatório.');
            $this->redirect('estoques');
            return;
        }

        if (!$propriedade_id) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Propriedade é obrigatória.');
            $this->redirect('estoques');
            return;
        }

        if ($quantidade < 0) {
            $this->setToast('warning', 'Quantidade inválida',
                'A quantidade inicial não pode ser negativa.');
            $this->redirect('estoques');
            return;
        }

        // Segurança: propriedade deve pertencer ao proprietário logado
        if (!$this->validarPropriedade($propriedade_id)) {
            $this->setToast('error', 'Acesso Negado',
                'Esta propriedade não pertence à sua conta.');
            $this->redirect('estoques');
            return;
        }

        // Duplicata: só verifica após validações básicas passarem
        if ($this->validator->EstoqueParaEsteInsumoJaExiste($insumo_id, $propriedade_id)) {
            $this->setToast('error', 'Estoque já existente',
                'Já existe um estoque cadastrado para este insumo nesta propriedade. ' .
                'Para adicionar quantidade, registre uma entrada via operação financeira.');
            $this->redirect('estoques');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO estoque_insumos (propriedade_id, insumo_id, quantidade)
                 VALUES (?, ?, ?)"
            );
            // "iid": int, int, double — "f" (float de 4 bytes) estava errado para decimal
            $stmt->bind_param("iid", $propriedade_id, $insumo_id, $quantidade);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir estoque: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Estoque Cadastrado!',
                'O saldo inicial de estoque foi registrado com sucesso.');
            $this->redirect('estoques');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível salvar o estoque. Tente novamente.');
            $this->redirect('estoques');
        }
    }

    /**
     * Registra uma entrada manual de insumo no estoque.
     * Atualiza se já existe, cria se não existe.
     */
    public function storeEntrada(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        $insumo_id      = (int)   ($_POST['insumo_id']      ?? 0);
        $quantidade     = (float) ($_POST['quantidade']      ?? 0);
        $propriedade_id = (int)   ($_POST['propriedade_id'] ?? 0);

        if (!$insumo_id || $quantidade <= 0 || !$propriedade_id) {
            $this->setToast('warning', 'Campos obrigatórios', 'Preencha todos os campos.');
            $this->redirect('estoques');
            return;
        }

        // Segurança: propriedade deve pertencer ao proprietário logado
        if (!$this->validarPropriedade($propriedade_id)) {
            $this->setToast('error', 'Acesso Negado',
                'Esta propriedade não pertence à sua conta.');
            $this->redirect('estoques');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT id, quantidade FROM estoque_insumos
                 WHERE insumo_id = ? AND propriedade_id = ? LIMIT 1"
            );
            $stmt->bind_param("ii", $insumo_id, $propriedade_id);
            $stmt->execute();
            $estoque = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($estoque) {
                $nova_qtd = (float) $estoque['quantidade'] + $quantidade;
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

            $this->setToast('success', 'Entrada Registrada!',
                'O estoque foi atualizado com sucesso.');
            $this->redirect('estoques');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível registrar a entrada.');
            $this->redirect('estoques');
        }
    }
}