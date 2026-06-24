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

    // ─── helper privado ──────────────────────────────────────────────────────

    private function getProprietarioId(): int
    {
        return (int) ($_SESSION['proprietario_id'] ?? 0);
    }

    /**
     * Valida que o silo pertence a uma propriedade do proprietário logado.
     */
    private function validarSilo(int $silo_id): bool
    {
        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare("
            SELECT s.id FROM silo s
            JOIN propriedade p ON p.id = s.propriedade_id
            WHERE s.id = ? AND p.proprietario_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $silo_id, $proprietario_id);
        $stmt->execute();
        $ok = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $ok;
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

    /**
     * Retorna totais agrupados por cultura e propriedade (usado em dashboards).
     */
    public function getAll(): array
    {
        if (!isset($_SESSION['user'])) return [];

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare("
            SELECT
                s.cultura,
                p.nome      AS propriedade,
                p.municipio,
                p.estado,
                SUM(s.quantidade_kg) AS total_kg
            FROM silo s
            JOIN propriedade p ON p.id = s.propriedade_id
            WHERE p.proprietario_id = ?
            GROUP BY s.cultura, p.nome, p.municipio, p.estado
            ORDER BY s.cultura, p.nome
        ");
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * Retorna todos os silos individuais com percentual de ocupação.
     */
    public function getAllSilos(): array
    {
        if (!isset($_SESSION['user'])) return [];

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.nome,
                s.cultura,
                s.capacidade_kg,
                s.quantidade_kg,
                p.id    AS propriedade_id,
                p.nome  AS propriedade,
                p.municipio,
                p.estado,
                CASE
                    WHEN s.capacidade_kg > 0
                    THEN ROUND((s.quantidade_kg / s.capacidade_kg) * 100, 1)
                    ELSE 0
                END AS ocupacao_pct
            FROM silo s
            JOIN propriedade p ON p.id = s.propriedade_id
            WHERE p.proprietario_id = ?
            ORDER BY p.nome, s.nome
        ");
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

        require_once __DIR__ . '/CulturaController.php';
        require_once __DIR__ . '/PropriedadeController.php';

        $silos        = $this->getAllSilos();
        $culturas     = (new CulturaController())->getNomeDistintas();
        $propriedades = (new PropriedadeController())->getAll();

        require_once __DIR__ . '/../views/silos.php';
    }

    // ─── escrita (exclusivo para proprietário) ───────────────────────────────

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Peão não pode cadastrar silos
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode cadastrar novos silos.');
            $this->redirect('silos');
            return;
        }

        $propriedade_id = (int)   ($_POST['propriedade_id'] ?? 0);
        $nome           = trim(    $_POST['nome']            ?? '');
        $cultura        = strtoupper(trim($_POST['cultura']  ?? ''));
        $capacidade_kg  = (float)  ($_POST['capacidade_kg']  ?? 0);
        $quantidade_kg  = (float)  ($_POST['quantidade_kg']  ?? 0);

        if (!$propriedade_id || empty($cultura) || $capacidade_kg <= 0) {
            $this->setToast('warning', 'Campos Obrigatórios',
                'Propriedade, cultura e capacidade são obrigatórios.');
            $this->redirect('silos');
            return;
        }

        if ($quantidade_kg > $capacidade_kg) {
            $this->setToast('warning', 'Quantidade inválida',
                'A quantidade não pode ser maior que a capacidade do silo.');
            $this->redirect('silos');
            return;
        }

        // Segurança: propriedade deve pertencer ao proprietário logado
        if (!$this->validarPropriedade($propriedade_id)) {
            $this->setToast('error', 'Acesso negado',
                'Esta propriedade não pertence à sua conta.');
            $this->redirect('silos');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO silo (propriedade_id, nome, cultura, capacidade_kg, quantidade_kg)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "issdd",
                $propriedade_id, $nome, $cultura, $capacidade_kg, $quantidade_kg
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir silo: {$this->db->error}");
            }
            $stmt->close();

            $label = !empty($nome) ? "O silo \"{$nome}\"" : 'O silo';
            $this->setToast('success', 'Silo Cadastrado!',
                "{$label} foi adicionado com sucesso.");
            $this->redirect('silos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível salvar o silo. Tente novamente.');
            $this->redirect('silos');
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Peão não pode editar silos
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode editar silos.');
            $this->redirect('silos');
            return;
        }

        $silo_id       = (int)   ($_POST['id']            ?? 0);
        $cultura       = strtoupper(trim($_POST['cultura'] ?? ''));
        $capacidade_kg = (float)  ($_POST['capacidade_kg'] ?? 0);

        if (!$silo_id || empty($cultura) || $capacidade_kg <= 0) {
            $this->setToast('warning', 'Campos Obrigatórios',
                'Cultura e capacidade são obrigatórios.');
            $this->redirect('silos');
            return;
        }

        // Segurança: silo deve pertencer ao proprietário logado
        if (!$this->validarSilo($silo_id)) {
            $this->setToast('error', 'Acesso negado',
                'Este silo não pertence à sua conta.');
            $this->redirect('silos');
            return;
        }

        // Não permite reduzir capacidade abaixo do estoque atual
        $stmt = $this->db->prepare(
            "SELECT quantidade_kg FROM silo WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $silo_id);
        $stmt->execute();
        $atual = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($atual && $capacidade_kg < (float) $atual['quantidade_kg']) {
            $this->setToast('warning', 'Capacidade inválida',
                'A nova capacidade não pode ser menor que o estoque atual do silo (' .
                number_format($atual['quantidade_kg'], 2, ',', '.') . ' kg).');
            $this->redirect('silos');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE silo SET cultura = ?, capacidade_kg = ? WHERE id = ?"
            );
            $stmt->bind_param("sdi", $cultura, $capacidade_kg, $silo_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar silo: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Silo Atualizado!',
                'As alterações foram salvas com sucesso.');
            $this->redirect('silos');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível atualizar o silo.');
            $this->redirect('silos');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        // Peão não pode remover silos
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode remover silos.');
            $this->redirect('silos');
            return;
        }

        $silo_id = (int) ($_POST['id'] ?? 0);

        if (!$silo_id) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('silos');
            return;
        }

        // Segurança: silo deve pertencer ao proprietário logado
        if (!$this->validarSilo($silo_id)) {
            $this->setToast('error', 'Acesso negado',
                'Este silo não pertence à sua conta.');
            $this->redirect('silos');
            return;
        }

        // Impede excluir silo com estoque
        $stmt = $this->db->prepare(
            "SELECT quantidade_kg FROM silo WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $silo_id);
        $stmt->execute();
        $silo = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($silo && (float) $silo['quantidade_kg'] > 0) {
            $this->setToast('warning', 'Silo com estoque',
                'Não é possível remover um silo que ainda possui estoque (' .
                number_format($silo['quantidade_kg'], 2, ',', '.') . ' kg).');
            $this->redirect('silos');
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM silo WHERE id = ?");
            $stmt->bind_param("i", $silo_id);
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