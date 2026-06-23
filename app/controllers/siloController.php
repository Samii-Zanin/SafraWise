<?php
/*
 * app/controllers/SiloController.php
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class SiloController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function getAll(): array
    {
        // Usa o ID do proprietário
        $proprietarioId = (int) $_SESSION['proprietario_id'];

        $stmt = $this->db->prepare("
            SELECT
                s.cultura,
                p.nome           AS propriedade,
                p.municipio,
                p.estado,
                SUM(s.quantidade_kg) AS total_kg
            FROM silo s
            JOIN propriedade p ON s.propriedade_id = p.id
            WHERE p.proprietario_id = ?
            GROUP BY s.cultura, p.nome, p.municipio, p.estado
            ORDER BY s.cultura, p.nome
        ");
        $stmt->bind_param("i", $proprietarioId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    public function getAllSilos(): array
    {
        // Usa o ID correto do dono da fazenda
        $proprietarioId = (int) $_SESSION['proprietario_id'];

        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.nome,
                s.cultura,
                s.capacidade_kg,
                s.quantidade_kg,
                p.id   AS propriedade_id,
                p.nome AS propriedade,
                p.municipio,
                p.estado,
                CASE
                    WHEN s.capacidade_kg > 0
                    THEN ROUND((s.quantidade_kg / s.capacidade_kg) * 100, 1)
                    ELSE 0
                END AS ocupacao_pct
            FROM silo s
            JOIN propriedade p ON s.propriedade_id = p.id
            WHERE p.proprietario_id = ?
            ORDER BY p.nome, s.nome
        ");
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

        require_once __DIR__ . '/CulturaController.php';
        require_once __DIR__ . '/PropriedadeController.php';

        $silos        = $this->getAllSilos();
        $culturas     = (new CulturaController())->getNomeDistintas();
        $propriedades = (new PropriedadeController())->getAll();

        require_once __DIR__ . '/../views/silos.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        // Peão não pode cadastrar novos silos
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas o proprietário pode cadastrar novos silos.');
            $this->redirect('silos');
            return;
        }

        $dados = [
            'propriedade_id' => (int)   ($_POST['propriedade_id'] ?? 0),
            'nome'           => trim(    $_POST['nome']            ?? ''),
            'cultura'        => trim(    $_POST['cultura']         ?? ''),
            'capacidade_kg'  => (float)  ($_POST['capacidade_kg']  ?? 0),
            'quantidade_kg'  => (float)  ($_POST['quantidade_kg']  ?? 0),
        ];

        if (!$dados['propriedade_id'] || empty($dados['cultura']) || $dados['capacidade_kg'] <= 0) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Propriedade, cultura e capacidade são obrigatórios.');
            $this->redirect('silos');
            return;
        }

        if ($dados['quantidade_kg'] > $dados['capacidade_kg']) {
            $this->setToast('warning', 'Quantidade inválida', 'A quantidade não pode ser maior que a capacidade do silo.');
            $this->redirect('silos');
            return;
        }

        // Usa a sessão do proprietário_id para checagem
        $proprietarioId = (int) $_SESSION['proprietario_id'];
        $stmtCheck = $this->db->prepare(
            "SELECT id FROM propriedade WHERE id = ? AND proprietario_id = ?"
        );
        
        $stmtCheck->bind_param("ii", $dados['propriedade_id'], $proprietarioId);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows === 0) {
            $this->setToast('error', 'Acesso negado', 'Propriedade não encontrada.');
            $this->redirect('silos');
            return;
        }
        $stmtCheck->close();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO silo (propriedade_id, nome, cultura, capacidade_kg, quantidade_kg)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "issdd",
                $dados['propriedade_id'],
                $dados['nome'],
                $dados['cultura'],
                $dados['capacidade_kg'],
                $dados['quantidade_kg']
            );

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir silo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Silo Cadastrado!', "O silo \"{$dados['nome']}\" foi adicionado com sucesso.");
            $this->redirect('silos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar o silo. Tente novamente.');
            $this->redirect('silos');
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        // Peão não pode alterar silos
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas o proprietário pode alterar a capacidade ou cultura de um silo.');
            $this->redirect('silos');
            return;
        }

        $id            = (int)   ($_POST['id']            ?? 0);
        $cultura       = trim(    $_POST['cultura']        ?? '');
        $capacidade_kg = (float)  ($_POST['capacidade_kg'] ?? 0);

        if (!$id || empty($cultura) || $capacidade_kg <= 0) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Cultura e capacidade são obrigatórios.');
            $this->redirect('silos');
            return;
        }

        // Usa a sessão do proprietário_id para checagem
        $proprietarioId = (int) $_SESSION['proprietario_id'];
        $stmtCheck = $this->db->prepare("
            SELECT s.id FROM silo s
            JOIN propriedade p ON s.propriedade_id = p.id
            WHERE s.id = ? AND p.proprietario_id = ?
        ");
        $stmtCheck->bind_param("ii", $id, $proprietarioId);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows === 0) {
            $this->setToast('error', 'Acesso negado', 'Silo não encontrado.');
            $this->redirect('silos');
            return;
        }
        $stmtCheck->close();

        try {
            $stmt = $this->db->prepare(
                "UPDATE silo SET cultura = ?, capacidade_kg = ? WHERE id = ?"
            );
            $stmt->bind_param("sdi", $cultura, $capacidade_kg, $id);

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar silo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Silo Atualizado!', 'As alterações foram salvas com sucesso.');
            $this->redirect('silos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível atualizar o silo.');
            $this->redirect('silos');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        // Peão não pode demolir/apagar silos
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas o proprietário pode remover silos do sistema.');
            $this->redirect('silos');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        
        // Usa a sessão do proprietário_id para checagem
        $proprietarioId = (int) $_SESSION['proprietario_id'];

        $stmtCheck = $this->db->prepare("
            SELECT s.id FROM silo s
            JOIN propriedade p ON s.propriedade_id = p.id
            WHERE s.id = ? AND p.proprietario_id = ?
        ");
        $stmtCheck->bind_param("ii", $id, $proprietarioId);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows === 0) {
            $this->setToast('error', 'Acesso negado', 'Silo não encontrado.');
            $this->redirect('silos');
            return;
        }
        $stmtCheck->close();

        try {
            $stmt = $this->db->prepare("DELETE FROM silo WHERE id = ?");
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao remover silo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Silo Removido!', 'O silo foi removido com sucesso.');
            $this->redirect('silos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível remover o silo.');
            $this->redirect('silos');
        }
    }
}