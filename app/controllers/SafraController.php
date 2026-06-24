<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class SafraController extends BaseController
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

    // ─── leitura ─────────────────────────────────────────────────────────────

    public function getAll(): array
    {
        if (!isset($_SESSION['user'])) return [];

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.talhao_id,
                s.cultura_id,
                c.nome          AS cultura,
                c.variedade,
                t.nome          AS nome_talhao,
                t.area_hectare  AS area_talhao,
                t.status        AS status_talhao,
                p.nome          AS nome_propriedade,
                p.municipio,
                p.estado,
                s.data_inicio,
                s.data_fim,
                CASE
                    WHEN s.data_fim IS NULL        THEN 'Ativa'
                    WHEN s.data_fim >= CURDATE()   THEN 'Ativa'
                    ELSE 'Encerrada'
                END AS status_safra
            FROM safra s
            LEFT JOIN cultura     c ON c.id = s.cultura_id
            LEFT JOIN talhoes     t ON t.id = s.talhao_id
            LEFT JOIN propriedade p ON p.id = t.propriedade_id
            WHERE p.proprietario_id = ?
            ORDER BY s.data_inicio DESC
        ");
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $safras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $safras;
    }

    public function getAtivas(): array
    {
        if (!isset($_SESSION['user'])) return [];

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.talhao_id,
                s.data_inicio,
                s.data_fim,
                c.nome          AS cultura,
                c.variedade,
                t.nome          AS nome_talhao,
                t.area_hectare  AS area_talhao,
                p.id            AS propriedade_id,
                p.nome          AS nome_propriedade,
                p.municipio,
                p.estado
            FROM safra s
            LEFT JOIN cultura     c ON c.id = s.cultura_id
            LEFT JOIN talhoes     t ON t.id = s.talhao_id
            LEFT JOIN propriedade p ON p.id = t.propriedade_id
            WHERE p.proprietario_id = ?
              AND (s.data_fim IS NULL OR s.data_fim >= CURDATE())
            ORDER BY s.data_inicio DESC
        ");
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $safras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $safras;
    }

    public function getById(int $id): ?array
    {
        if (!isset($_SESSION['user'])) return null;

        $proprietario_id = $this->getProprietarioId();

        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.data_inicio,
                s.data_fim,
                c.nome          AS cultura,
                c.variedade,
                t.id            AS talhao_id,
                t.nome          AS nome_talhao,
                t.area_hectare  AS area_talhao,
                t.status        AS status_talhao,
                p.id            AS propriedade_id,
                p.nome          AS nome_propriedade,
                p.municipio,
                p.estado
            FROM safra s
            LEFT JOIN cultura     c ON c.id = s.cultura_id
            LEFT JOIN talhoes     t ON t.id = s.talhao_id
            LEFT JOIN propriedade p ON p.id = t.propriedade_id
            WHERE s.id = ? AND p.proprietario_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $id, $proprietario_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: null;
    }

    // ─── escrita ─────────────────────────────────────────────────────────────

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode iniciar um novo ciclo de safra.');
            $this->redirect('safras');
            return;
        }

        $cultura_id  = (int)  ($_POST['cultura_id']  ?? 0);
        $talhao_id   = (int)  ($_POST['talhao_id']   ?? 0);
        $data_inicio = trim(   $_POST['data_inicio']  ?? '');
        $data_fim    = !empty($_POST['data_fim']) ? trim($_POST['data_fim']) : null;

        if (!$cultura_id || !$talhao_id || empty($data_inicio)) {
            $this->setToast('warning', 'Campos obrigatórios',
                'Cultura, talhão e data de início são obrigatórios.');
            $this->redirect('safras');
            return;
        }

        if ($data_fim && $data_fim < $data_inicio) {
            $this->setToast('warning', 'Data inválida',
                'A data de fim não pode ser anterior à data de início.');
            $this->redirect('safras');
            return;
        }

        $proprietario_id = $this->getProprietarioId();

        // Valida que o talhão pertence ao proprietário logado
        $stmt = $this->db->prepare("
            SELECT t.id, t.status FROM talhoes t
            JOIN propriedade p ON p.id = t.propriedade_id
            WHERE t.id = ? AND p.proprietario_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $talhao_id, $proprietario_id);
        $stmt->execute();
        $talhao = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$talhao) {
            $this->setToast('error', 'Acesso negado', 'Talhão não encontrado.');
            $this->redirect('safras');
            return;
        }

        // Impede iniciar safra em talhão já ocupado
        if ($talhao['status'] === 'Em Safra' || $talhao['status'] === 'Plantado') {
            $this->setToast('warning', 'Talhão ocupado',
                'Este talhão já possui uma safra em andamento.');
            $this->redirect('safras');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE talhoes SET status = 'Em Safra' WHERE id = ?"
            );
            $stmt->bind_param("i", $talhao_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar talhão: {$this->db->error}");
            }
            $stmt->close();

            $stmt = $this->db->prepare(
                "INSERT INTO safra (cultura_id, talhao_id, data_inicio, data_fim)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("iiss", $cultura_id, $talhao_id, $data_inicio, $data_fim);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir safra: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Safra Cadastrada!', 'A safra foi registrada com sucesso.');
            $this->redirect('safras');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível cadastrar a safra.');
            $this->redirect('safras');
        }
    }

    public function encerrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode encerrar a safra e liberar o talhão.');
            $this->redirect('safras');
            return;
        }

        $safra_id        = (int) ($_POST['safra_id'] ?? 0);
        $proprietario_id = $this->getProprietarioId();

        if (!$safra_id) {
            $this->setToast('error', 'Erro', 'Safra não identificada.');
            $this->redirect('safras');
            return;
        }

        // Valida que a safra pertence ao proprietário e ainda está ativa
        $stmt = $this->db->prepare("
            SELECT s.talhao_id, s.data_fim
            FROM safra s
            JOIN talhoes     t ON t.id = s.talhao_id
            JOIN propriedade p ON p.id = t.propriedade_id
            WHERE s.id = ? AND p.proprietario_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $safra_id, $proprietario_id);
        $stmt->execute();
        $safra = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$safra) {
            $this->setToast('error', 'Acesso negado', 'Safra não encontrada.');
            $this->redirect('safras');
            return;
        }
        if (!empty($safra['data_fim'])) {
            $this->setToast('warning', 'Safra já encerrada',
                'Esta safra já foi encerrada anteriormente.');
            $this->redirect('safra_detalhe&id=' . $safra_id);
            return;
        }

        $talhao_id = (int) $safra['talhao_id'];

        try {
            $stmt = $this->db->prepare(
                "UPDATE safra SET data_fim = CURDATE() WHERE id = ?"
            );
            $stmt->bind_param("i", $safra_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao encerrar safra: {$this->db->error}");
            }
            $stmt->close();

            $stmt = $this->db->prepare(
                "UPDATE talhoes SET status = 'Vazio' WHERE id = ?"
            );
            $stmt->bind_param("i", $talhao_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar talhão: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Safra Encerrada!',
                'A safra foi encerrada e o talhão liberado.');
            $this->redirect('safras');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível encerrar a safra.');
            $this->redirect('safra_detalhe&id=' . $safra_id);
        }
    }
}