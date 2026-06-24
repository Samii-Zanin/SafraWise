<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class DashboardController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    // ─── helper privado ──────────────────────────────────────────────────────

    /**
     * Lê o proprietario_id da sessão — setado pelo AuthController no login
     * tanto para proprietário quanto para peão.
     */
    private function getProprietarioId(): int
    {
        return (int) ($_SESSION['proprietario_id'] ?? 0);
    }

    // ─── ação pública ────────────────────────────────────────────────────────

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        $pid = $this->getProprietarioId();

        if (!$pid) {
            $this->setToast('error', 'Sessão inválida',
                'Não foi possível identificar o proprietário. Faça login novamente.');
            $this->redirect('login');
            return;
        }

        $metricas          = $this->getMetricas($pid);
        $talhoes           = $this->getTalhoes($pid);
        $progressoCulturas = $this->getProgressoCulturas($pid);
        $propriedades      = $this->getPropriedades($pid);

        require_once __DIR__ . '/../views/dashboard.php';
    }

    // ─── queries privadas ────────────────────────────────────────────────────

    private function getMetricas(int $pid): array
    {
        // Total de talhões cadastrados nas propriedades do proprietário
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM talhoes t
            JOIN propriedade pr ON pr.id = t.propriedade_id
            WHERE pr.proprietario_id = ?
        ");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $totalTalhoes = (int) $stmt->get_result()->fetch_row()[0];
        $stmt->close();

        // Área produtiva somada de todas as propriedades
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(area_produtiva), 0)
            FROM propriedade
            WHERE proprietario_id = ?
        ");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $areaTotal = (float) $stmt->get_result()->fetch_row()[0];
        $stmt->close();

        // Itens com alguma quantidade em estoque
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM estoque_insumos ei
            JOIN propriedade pr ON pr.id = ei.propriedade_id
            WHERE pr.proprietario_id = ? AND ei.quantidade > 0
        ");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $totalInsumos = (int) $stmt->get_result()->fetch_row()[0];
        $stmt->close();

        // Safra com data_fim mais próxima no futuro
        $stmt = $this->db->prepare("
            SELECT s.data_fim, c.nome AS cultura, t.nome AS talhao
            FROM safra s
            JOIN talhoes     t  ON t.id  = s.talhao_id
            JOIN propriedade pr ON pr.id = t.propriedade_id
            JOIN cultura     c  ON c.id  = s.cultura_id
            WHERE pr.proprietario_id = ? AND s.data_fim > CURDATE()
            ORDER BY s.data_fim ASC
            LIMIT 1
        ");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $proximaSafra = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $diasParaColheita = null;
        if ($proximaSafra) {
            $diasParaColheita = (int) (new DateTime($proximaSafra['data_fim']))
                ->diff(new DateTime('today'))->days;
        }

        return [
            'total_talhoes'      => $totalTalhoes,
            'area_total'         => $areaTotal,
            'total_insumos'      => $totalInsumos,
            'proxima_safra'      => $proximaSafra,
            'dias_para_colheita' => $diasParaColheita,
        ];
    }

    private function getTalhoes(int $pid): array
    {
        // Talhões com a safra ativa (data_fim futura ou nula) se existir
        $stmt = $this->db->prepare("
            SELECT
                t.nome,
                t.area_hectare,
                t.status,
                pr.nome AS propriedade,
                c.nome  AS cultura
            FROM talhoes t
            JOIN propriedade pr ON pr.id = t.propriedade_id
            LEFT JOIN safra s   ON s.talhao_id = t.id
                                AND (s.data_fim IS NULL OR s.data_fim >= CURDATE())
            LEFT JOIN cultura c ON c.id = s.cultura_id
            WHERE pr.proprietario_id = ?
            ORDER BY t.nome ASC
            LIMIT 10
        ");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    private function getPropriedades(int $pid): array
    {
        $stmt = $this->db->prepare("
            SELECT id, nome, municipio, estado
            FROM propriedade
            WHERE proprietario_id = ?
            ORDER BY nome ASC
        ");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    private function getProgressoCulturas(int $pid): array
    {
        // Progresso médio de cada cultura nas safras ativas (baseado em dias percorridos)
        $stmt = $this->db->prepare("
            SELECT
                c.nome AS cultura,
                ROUND(AVG(
                    LEAST(100,
                        GREATEST(0,
                            DATEDIFF(CURDATE(), s.data_inicio) /
                            GREATEST(1, DATEDIFF(s.data_fim, s.data_inicio))
                        ) * 100
                    )
                )) AS progresso,
                COUNT(*) AS talhoes
            FROM safra s
            JOIN cultura     c  ON c.id  = s.cultura_id
            JOIN talhoes     t  ON t.id  = s.talhao_id
            JOIN propriedade pr ON pr.id = t.propriedade_id
            WHERE pr.proprietario_id = ?
              AND (s.data_fim IS NULL OR s.data_fim >= CURDATE())
            GROUP BY c.id, c.nome
            ORDER BY progresso DESC
        ");
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}