<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class PropriedadeController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    // ─── helpers privados ────────────────────────────────────────────────────

    private function getProprietarioId(): int
    {
        return (int) ($_SESSION['proprietario_id'] ?? 0);
    }

    /**
     * Valida as regras de negócio das áreas.
     * Retorna a mensagem de erro ou null se tudo estiver OK.
     */
    private function validarAreas(float $area_total, float $area_produtiva): ?string
    {
        if ($area_total <= 0) {
            return 'A área total da propriedade deve ser maior que zero.';
        }
        if ($area_produtiva < 0) {
            return 'A área produtiva não pode ser um valor negativo.';
        }
        if ($area_produtiva > $area_total) {
            return 'A área produtiva não pode ser maior do que a área total da fazenda.';
        }
        return null;
    }

    /**
     * Garante que a propriedade pertence ao proprietário logado.
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

    public function getAll(): array
    {
        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare(
            "SELECT id, nome, localizacao, municipio, estado, area_total, area_produtiva
         FROM propriedade
         WHERE proprietario_id = ?
         ORDER BY nome ASC"
        );
        $stmt->bind_param("i", $proprietario_id);
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

        $propriedades = $this->getAll();

        require_once __DIR__ . '/../views/propriedade.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode cadastrar novas fazendas.');
            $this->redirect('propriedades');
            return;
        }

        $proprietario_id = $this->getProprietarioId();
        $nome            = trim($_POST['nome']        ?? '');
        $localizacao     = trim($_POST['localizacao'] ?? '');
        $municipio       = trim($_POST['municipio']   ?? '');
        $estado          = strtoupper(trim($_POST['estado'] ?? ''));
        $area_total      = (float) ($_POST['area_total']      ?? 0);
        $area_produtiva  = (float) ($_POST['area_produtiva']  ?? 0);

        if (empty($nome) || empty($municipio) || empty($estado)) {
            $this->setToast('warning', 'Campos Obrigatórios',
                'Nome, município e estado são obrigatórios.');
            $this->redirect('propriedades');
            return;
        }

        $erroArea = $this->validarAreas($area_total, $area_produtiva);
        if ($erroArea) {
            $this->setToast('error', 'Área Inválida', $erroArea);
            $this->redirect('propriedades');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO propriedade
                    (nome, localizacao, municipio, estado, area_total, area_produtiva, proprietario_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssssddi",
                $nome, $localizacao, $municipio, $estado,
                $area_total, $area_produtiva, $proprietario_id
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir propriedade: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Sucesso', 'Propriedade cadastrada com sucesso!');
            $this->redirect('propriedades');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível cadastrar a propriedade. Tente novamente.');
            $this->redirect('propriedades');
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
                'Apenas o proprietário pode editar os dados da fazenda.');
            $this->redirect('propriedades');
            return;
        }

        $propriedade_id = (int) ($_POST['id'] ?? 0);

        if (!$propriedade_id || !$this->validarPropriedade($propriedade_id)) {
            $this->setToast('error', 'Acesso Negado',
                'Esta propriedade não pertence à sua conta.');
            $this->redirect('propriedades');
            return;
        }

        $proprietario_id = $this->getProprietarioId();
        $nome            = trim($_POST['nome']        ?? '');
        $localizacao     = trim($_POST['localizacao'] ?? '');
        $municipio       = trim($_POST['municipio']   ?? '');
        $estado          = strtoupper(trim($_POST['estado'] ?? ''));
        $area_total      = (float) ($_POST['area_total']     ?? 0);
        $area_produtiva  = (float) ($_POST['area_produtiva'] ?? 0);

        if (empty($nome) || empty($municipio) || empty($estado)) {
            $this->setToast('warning', 'Campos Obrigatórios',
                'Nome, município e estado são obrigatórios.');
            $this->redirect('propriedades');
            return;
        }

        $erroArea = $this->validarAreas($area_total, $area_produtiva);
        if ($erroArea) {
            $this->setToast('error', 'Área Inválida', $erroArea);
            $this->redirect('propriedades');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE propriedade
                 SET nome = ?, localizacao = ?, municipio = ?, estado = ?,
                     area_total = ?, area_produtiva = ?
                 WHERE id = ? AND proprietario_id = ?"
            );
            $stmt->bind_param(
                "ssssddii",
                $nome, $localizacao, $municipio, $estado,
                $area_total, $area_produtiva, $propriedade_id, $proprietario_id
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar propriedade: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Atualizado', 'Dados da propriedade atualizados!');
            $this->redirect('propriedades');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível atualizar a propriedade. Tente novamente.');
            $this->redirect('propriedades');
        }
    }

    public function delete(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode excluir uma fazenda.');
            $this->redirect('propriedades');
            return;
        }

        $propriedade_id  = (int) ($_GET['id'] ?? 0);
        $proprietario_id = $this->getProprietarioId();

        if (!$propriedade_id || !$this->validarPropriedade($propriedade_id)) {
            $this->setToast('error', 'Acesso Negado',
                'Esta propriedade não pertence à sua conta.');
            $this->redirect('propriedades');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "DELETE FROM propriedade WHERE id = ? AND proprietario_id = ?"
            );
            $stmt->bind_param("ii", $propriedade_id, $proprietario_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao excluir propriedade: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Removida', 'Propriedade excluída com sucesso.');
            $this->redirect('propriedades');

        } catch (\mysqli_sql_exception $e) {
            // FK violation: existem talhões, silos ou estoques vinculados
            $this->setToast('error', 'Não permitido',
                'Esta propriedade possui talhões ou insumos vinculados e não pode ser excluída.');
            $this->redirect('propriedades');
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível excluir a propriedade. Tente novamente.');
            $this->redirect('propriedades');
        }
    }

    public function detalhes(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        $propriedade_id  = (int) ($_GET['id'] ?? 0);
        $proprietario_id = $this->getProprietarioId();

        if (!$propriedade_id || !$this->validarPropriedade($propriedade_id)) {
            $this->redirect('propriedades');
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT id, nome, localizacao, municipio, estado, area_total, area_produtiva
             FROM propriedade
             WHERE id = ? AND proprietario_id = ?
             LIMIT 1"
        );
        $stmt->bind_param("ii", $propriedade_id, $proprietario_id);
        $stmt->execute();
        $propriedade = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmtTal = $this->db->prepare(
            "SELECT id, nome, area_hectare, status, tipo_posse, custo_arrendamento_sacas
             FROM talhoes
             WHERE propriedade_id = ?
             ORDER BY nome ASC"
        );
        $stmtTal->bind_param("i", $propriedade_id);
        $stmtTal->execute();
        $talhoes = $stmtTal->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtTal->close();

        $area_ocupada = array_sum(array_column($talhoes, 'area_hectare'));

        require_once __DIR__ . '/../views/detalhes_propriedade.php';
    }
}