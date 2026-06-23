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

    public function index(): void
    {
        // Garante que o usuário está logado antes de carregar o painel
        if (!isset($_SESSION['user']) || !isset($_SESSION['proprietario_id'])) {
            $this->redirect('login');
        }

        // Puxa direto da variável de sessão, sem fazer queries extras
        $pid = (int) $_SESSION['proprietario_id'];

        $metricas          = $this->getMetricas($pid);
        $talhoes           = $this->getTalhoes($pid);
        $progressoCulturas = $this->getProgressoCulturas($pid);
        $propriedades      = $this->getPropriedades($pid);

        require_once __DIR__ . '/../views/dashboard.php';
    }

    // ── Queries ──────────────────────────────────────────────────────

    private function getMetricas(int $pid): array
    {
        // total de talhões cadastrados
        $stmtT = $this->db->prepare(
            'SELECT COUNT(*) FROM talhoes t
             JOIN propriedade pr ON t.propriedade_id = pr.id
             WHERE pr.proprietario_id = ?'
        );
        $stmtT->bind_param('i', $pid);
        $stmtT->execute();
        $totalTalhoes = (int) $stmtT->get_result()->fetch_row()[0];
        $stmtT->close();

        // área produtiva somada de todas as propriedades
        $stmtA = $this->db->prepare(
            'SELECT COALESCE(SUM(area_produtiva), 0) FROM propriedade WHERE proprietario_id = ?'
        );
        $stmtA->bind_param('i', $pid);
        $stmtA->execute();
        $areaTotal = (float) $stmtA->get_result()->fetch_row()[0];
        $stmtA->close();

        // itens com alguma quantidade em estoque
        $stmtI = $this->db->prepare(
            'SELECT COUNT(*) FROM estoque_insumos ei
             JOIN propriedade pr ON ei.propriedade_id = pr.id
             WHERE pr.proprietario_id = ? AND ei.quantidade > 0'
        );
        $stmtI->bind_param('i', $pid);
        $stmtI->execute();
        $totalInsumos = (int) $stmtI->get_result()->fetch_row()[0];
        $stmtI->close();

        // safra com data_fim mais próxima no futuro
        $stmtS = $this->db->prepare(
            'SELECT s.data_fim, c.nome AS cultura, t.nome AS talhao
             FROM safra s
             JOIN talhoes t     ON s.talhao_id  = t.id
             JOIN propriedade pr ON t.propriedade_id = pr.id
             JOIN cultura c     ON s.cultura_id = c.id
             WHERE pr.proprietario_id = ? AND s.data_fim >= CURDATE()
             ORDER BY s.data_fim ASC LIMIT 1'
        );
        $stmtS->bind_param('i', $pid);
        $stmtS->execute();
        $proximaSafra = $stmtS->get_result()->fetch_assoc();
        $stmtS->close();

        $diasParaColheita = null;
        if ($proximaSafra) {
            $diasParaColheita = (int) (new DateTime($proximaSafra['data_fim']))
                ->diff(new DateTime('today'))->days;
        }

        return [
            'total_talhoes'     => $totalTalhoes,
            'area_total'        => $areaTotal,
            'total_insumos'     => $totalInsumos,
            'proxima_safra'     => $proximaSafra,
            'dias_para_colheita'=> $diasParaColheita,
        ];
    }

    private function getTalhoes(int $pid): array
    {
        // talhões com a safra ativa (data_fim futura) se existir, senão sem safra
        $stmt = $this->db->prepare(
            'SELECT t.nome, t.area_hectare, t.status, pr.nome AS propriedade,
                    c.nome AS cultura
             FROM talhoes t
             JOIN propriedade pr ON t.propriedade_id = pr.id
             LEFT JOIN safra s   ON s.talhao_id = t.id AND s.data_fim >= CURDATE()
             LEFT JOIN cultura c ON s.cultura_id = c.id
             WHERE pr.proprietario_id = ?
             ORDER BY t.nome
             LIMIT 10'
        );
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    private function getPropriedades(int $pid): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nome, municipio, estado FROM propriedade WHERE proprietario_id = ? ORDER BY nome'
        );
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    private function getProgressoCulturas(int $pid): array
    {
        // progresso médio de cada cultura nas safras ativas (baseado em dias percorridos)
        $stmt = $this->db->prepare(
            'SELECT c.nome AS cultura,
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
             JOIN cultura c      ON s.cultura_id = c.id
             JOIN talhoes t      ON s.talhao_id  = t.id
             JOIN propriedade pr ON t.propriedade_id = pr.id
             WHERE pr.proprietario_id = ?
               AND s.data_fim >= CURDATE()
             GROUP BY c.id, c.nome
             ORDER BY progresso DESC'
        );
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}