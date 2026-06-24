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

    // ─── helper privado ──────────────────────────────────────────────────────

    private function getProprietarioId(): int
    {
        return (int) ($_SESSION['proprietario_id'] ?? 0);
    }

    /**
     * Valida que o par insumo_id + produto_id é consistente no banco.
     * Impede que um POST forjado manipule insumos de outro produto.
     */
    private function validarVinculoInsumoProduto(int $insumo_id, int $produto_id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM insumo WHERE id = ? AND produto_id = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $insumo_id, $produto_id);
        $stmt->execute();
        $ok = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $ok;
    }

    /**
     * Verifica se o insumo está vinculado a operações agrícolas.
     * Usado para bloquear exclusão segura.
     */
    private function insumoEstaEmUso(int $insumo_id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM operacoes_agricolas WHERE insumo_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $insumo_id);
        $stmt->execute();
        $emUso = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $emUso;
    }

    // ─── leitura ─────────────────────────────────────────────────────────────

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Insumo é catálogo global — exibe todos independente de proprietário
        $stmt = $this->db->prepare("
            SELECT
                i.id,
                i.valor_por_dose,
                p.id             AS produto_id,
                p.nome,
                p.marca,
                p.tipo,
                p.unidade_medida,
                p.descricao
            FROM insumo i
            INNER JOIN produto p ON p.id = i.produto_id
            ORDER BY p.nome ASC
        ");
        $stmt->execute();
        $insumos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require_once __DIR__ . '/../views/insumos.php';
    }

    public function getAllComProduto(): array
    {
        if (!isset($_SESSION['user'])) return [];

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
        if (!isset($_SESSION['user'])) return [];

        $stmt = $this->db->prepare("
            SELECT
                i.id,
                p.nome,
                p.tipo,
                p.marca,
                i.valor_por_dose,
                p.unidade_medida,
                MAX(i.data_referencia) AS data_ref
            FROM insumo i
            LEFT JOIN produto p ON p.id = i.produto_id
            WHERE p.tipo <> 'CEREAL'
            GROUP BY
                i.id,
                p.nome,
                p.tipo,
                p.marca,
                i.valor_por_dose,
                p.unidade_medida
            ORDER BY p.nome ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    // ─── escrita (exclusivo para proprietário) ───────────────────────────────

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Peão não pode cadastrar insumos no catálogo
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode cadastrar novos insumos.');
            $this->redirect('insumos');
            return;
        }

        $dados = [
            'nome'           => trim($_POST['nome']           ?? ''),
            'marca'          => trim($_POST['marca']          ?? ''),
            'tipo'           => trim($_POST['tipo']           ?? ''),
            'unidade_medida' => trim($_POST['unidade_medida'] ?? ''),
            'descricao'      => trim($_POST['descricao']      ?? ''),
            'valor_por_dose' => str_replace(',', '.', trim($_POST['valor_por_dose'] ?? '')),
        ];

        if (empty($dados['nome']) || !is_numeric($dados['valor_por_dose'])
                || (float) $dados['valor_por_dose'] < 0) {
            $this->setToast('warning', 'Campos Obrigatórios',
                'Nome do produto e valor por dose são obrigatórios.');
            $this->redirect('insumos');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produto (nome, marca, tipo, unidade_medida, descricao)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssss",
                $dados['nome'], $dados['marca'], $dados['tipo'],
                $dados['unidade_medida'], $dados['descricao']
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir produto: {$this->db->error}");
            }
            $produto_id = $this->db->insert_id;
            $stmt->close();

            $valor = (float) $dados['valor_por_dose'];
            $stmt  = $this->db->prepare(
                "INSERT INTO insumo (valor_por_dose, produto_id) VALUES (?, ?)"
            );
            $stmt->bind_param("di", $valor, $produto_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir insumo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Insumo Cadastrado!',
                "{$dados['nome']} foi adicionado ao catálogo.");
            $this->redirect('insumos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível salvar o insumo. Tente novamente.');
            $this->redirect('insumos');
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Peão não pode editar insumos do catálogo
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode editar insumos.');
            $this->redirect('insumos');
            return;
        }

        $insumo_id  = (int) ($_POST['insumo_id']  ?? 0);
        $produto_id = (int) ($_POST['produto_id'] ?? 0);

        if (!$insumo_id || !$produto_id) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('insumos');
            return;
        }

        // Valida que o par insumo+produto é consistente — evita manipulação via POST
        if (!$this->validarVinculoInsumoProduto($insumo_id, $produto_id)) {
            $this->setToast('error', 'Acesso negado',
                'Insumo não encontrado ou vínculo inválido.');
            $this->redirect('insumos');
            return;
        }

        $dados = [
            'nome'           => trim($_POST['nome']           ?? ''),
            'marca'          => trim($_POST['marca']          ?? ''),
            'tipo'           => trim($_POST['tipo']           ?? ''),
            'unidade_medida' => trim($_POST['unidade_medida'] ?? ''),
            'descricao'      => trim($_POST['descricao']      ?? ''),
            'valor_por_dose' => str_replace(',', '.', trim($_POST['valor_por_dose'] ?? '')),
        ];

        if (empty($dados['nome']) || !is_numeric($dados['valor_por_dose'])
                || (float) $dados['valor_por_dose'] < 0) {
            $this->setToast('warning', 'Campos Obrigatórios',
                'Nome do produto e valor por dose são obrigatórios.');
            $this->redirect('insumos');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE produto
                 SET nome = ?, marca = ?, tipo = ?, unidade_medida = ?, descricao = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "sssssi",
                $dados['nome'], $dados['marca'], $dados['tipo'],
                $dados['unidade_medida'], $dados['descricao'], $produto_id
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar produto: {$this->db->error}");
            }
            $stmt->close();

            $valor = (float) $dados['valor_por_dose'];
            $stmt  = $this->db->prepare(
                "UPDATE insumo SET valor_por_dose = ? WHERE id = ?"
            );
            $stmt->bind_param("di", $valor, $insumo_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar insumo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Insumo Atualizado!',
                "{$dados['nome']} foi atualizado com sucesso.");
            $this->redirect('insumos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível atualizar o insumo. Tente novamente.');
            $this->redirect('insumos');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Peão não pode remover insumos do catálogo
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode remover insumos.');
            $this->redirect('insumos');
            return;
        }

        $insumo_id  = (int) ($_POST['insumo_id']  ?? 0);
        $produto_id = (int) ($_POST['produto_id'] ?? 0);

        if (!$insumo_id) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('insumos');
            return;
        }

        // Valida vínculo insumo+produto se produto_id foi enviado
        if ($produto_id && !$this->validarVinculoInsumoProduto($insumo_id, $produto_id)) {
            $this->setToast('error', 'Acesso negado',
                'Insumo não encontrado ou vínculo inválido.');
            $this->redirect('insumos');
            return;
        }

        // Bloqueia exclusão se o insumo estiver vinculado a operações agrícolas
        if ($this->insumoEstaEmUso($insumo_id)) {
            $this->setToast('warning', 'Insumo em uso',
                'Este insumo está vinculado a operações agrícolas e não pode ser excluído.');
            $this->redirect('insumos');
            return;
        }

        try {
            // Remove apenas estoque das propriedades do proprietário logado
            // Outros proprietários que usam o mesmo insumo não são afetados
            $proprietario_id = $this->getProprietarioId();
            $stmt = $this->db->prepare("
                DELETE ei FROM estoque_insumos ei
                JOIN propriedade p ON p.id = ei.propriedade_id
                WHERE ei.insumo_id = ? AND p.proprietario_id = ?
            ");
            $stmt->bind_param("ii", $insumo_id, $proprietario_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->db->prepare("DELETE FROM insumo WHERE id = ?");
            $stmt->bind_param("i", $insumo_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao excluir insumo: {$this->db->error}");
            }
            $stmt->close();

            // Remove o produto vinculado apenas se não houver outros insumos usando-o
            if ($produto_id) {
                $stmt = $this->db->prepare(
                    "SELECT id FROM insumo WHERE produto_id = ? LIMIT 1"
                );
                $stmt->bind_param("i", $produto_id);
                $stmt->execute();
                $ainda_em_uso = (bool) $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$ainda_em_uso) {
                    $stmt = $this->db->prepare("DELETE FROM produto WHERE id = ?");
                    $stmt->bind_param("i", $produto_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $this->setToast('success', 'Insumo Removido',
                'O insumo foi excluído do catálogo com sucesso.');
            $this->redirect('insumos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Não foi possível excluir',
                'Este insumo pode estar vinculado a operações agrícolas.');
            $this->redirect('insumos');
        }
    }
}