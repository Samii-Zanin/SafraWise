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

    public function getAll(): array
    {
        // 🌟 CORREÇÃO: Puxa o ID do dono da fazenda
        $proprietarioId = $_SESSION['proprietario_id'];

        $stmt = $this->db->prepare(
            "select
                s.id,
                s.talhao_id,
                s.cultura_id,
                c.nome as cultura,
                c.variedade, 
                t.nome as nome_talhao,
                t.area_hectare as area_talhao,
                t.status as status_talhao,
                p.nome as nome_propriedade,
                p.municipio,
                p.estado, 
                s.data_inicio,
                s.data_fim,
                CASE WHEN s.data_fim IS NULL THEN 'Ativa'
                     WHEN s.data_fim >= CURDATE() THEN 'Ativa'
                     ELSE 'Encerrada'
                END as status_safra
            from
                safra s
            left join cultura c on c.id = s.cultura_id
            left join talhoes t on t.id = s.talhao_id
            left join propriedade p on t.propriedade_id = p.id
             WHERE p.proprietario_id = ?
             ORDER BY data_inicio DESC"
        );
        $stmt->bind_param("i", $proprietarioId);
        $stmt->execute();

        $safras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $safras;
    }

    public function getAtivas(): array
    {
        // 🌟 CORREÇÃO: Puxa o ID do dono da fazenda
        $proprietarioId = $_SESSION['proprietario_id'];

        $stmt = $this->db->prepare(
            " 
            SELECT
            s.id,
            s.talhao_id,
            s.data_inicio,
            s.data_fim,
            c.nome        AS cultura,
            c.variedade,
            t.nome        AS nome_talhao,
            t.area_hectare AS area_talhao,
            p.id          AS propriedade_id,
            p.nome        AS nome_propriedade,
            p.municipio,
            p.estado
        FROM safra s
        LEFT JOIN cultura c     ON c.id = s.cultura_id
        LEFT JOIN talhoes t     ON t.id = s.talhao_id
        LEFT JOIN propriedade p ON p.id = t.propriedade_id
        WHERE p.proprietario_id = ?
          AND (s.data_fim IS NULL OR s.data_fim >= CURDATE())
        ORDER BY s.data_inicio DESC
            "
        );
        $stmt->bind_param("i", $proprietarioId);
        $stmt->execute();

        $safras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $safras;
    }

    public function getById(int $id): ?array
    {
        // 🌟 CORREÇÃO: Puxa o ID do dono da fazenda
        $proprietarioId = (int) $_SESSION['proprietario_id'];

        $stmt = $this->db->prepare("
            SELECT
                s.id, s.data_inicio, s.data_fim,
                c.nome      AS cultura,
                c.variedade,
                t.id        AS talhao_id,
                t.nome      AS nome_talhao,
                t.area_hectare AS area_talhao,
                t.status    AS status_talhao,
                p.nome      AS nome_propriedade,
                p.municipio, p.estado,
                p.id        AS propriedade_id
            FROM safra s
            LEFT JOIN cultura c     ON c.id = s.cultura_id
            LEFT JOIN talhoes t     ON t.id = s.talhao_id
            LEFT JOIN propriedade p ON p.id = t.propriedade_id
            WHERE s.id = ? AND p.proprietario_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $id, $proprietarioId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: null;
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        // 🔒 SEGURANÇA: Peão não pode criar nova safra
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas o proprietário pode iniciar um novo ciclo de safra.');
            $this->redirect('safras');
            return;
        }

        $cultura_id  = (int)   ($_POST['cultura_id']  ?? 0);
        $talhao_id   = (int)   ($_POST['talhao_id']   ?? 0);
        $data_inicio = trim(    $_POST['data_inicio']  ?? '');
        $data_fim    = !empty($_POST['data_fim']) ? trim($_POST['data_fim']) : null;

        if (!$cultura_id || !$talhao_id || empty($data_inicio)) {
            $this->setToast('warning', 'Campos obrigatórios', 'Cultura, talhão e data de início são obrigatórios.');
            $this->redirect('safras');
            return;
        }

        if ($data_fim && $data_fim < $data_inicio) {
            $this->setToast('warning', 'Data inválida', 'A data de fim não pode ser anterior à data de início.');
            $this->redirect('safras');
            return;
        }
        
        // 🌟 CORREÇÃO: Usa o ID do dono da fazenda
        $uid       = (int) $_SESSION['proprietario_id'];
        
        $stmtCheck = $this->db->prepare("
            SELECT t.id FROM talhoes t
            JOIN propriedade p ON p.id = t.propriedade_id
            WHERE t.id = ? AND p.proprietario_id = ?
            LIMIT 1
        ");
        $stmtCheck->bind_param("ii", $talhao_id, $uid);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows === 0) {
            $this->setToast('error', 'Acesso negado', 'Talhão não encontrado.');
            $this->redirect('safras');
            return;
        }
        $stmtCheck->close();

        try {
            $stmt = $this->db->prepare("
                UPDATE talhoes SET status = 'Em Safra'
                WHERE id = ?
            ");
            $stmt->bind_param("i", $talhao_id);

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar talhão: {$this->db->error}");
            }
            $stmt->close();

            $stmt = $this->db->prepare("
                INSERT INTO safra (cultura_id, talhao_id, data_inicio, data_fim)
                VALUES (?, ?, ?, ?)
            ");
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
        }

        // 🔒 SEGURANÇA: Peão não pode encerrar safra
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas o proprietário pode encerrar a safra e liberar o talhão.');
            $this->redirect('safras');
            return;
        }

        $safra_id = (int) ($_POST['safra_id'] ?? 0);
        
        // 🌟 CORREÇÃO: Usa o ID do dono da fazenda
        $uid      = (int) $_SESSION['proprietario_id'];

        if (!$safra_id) {
            $this->setToast('error', 'Erro', 'Safra não identificada.');
            $this->redirect('safras');
            return;
        }

        // Busca talhao_id e valida que a safra pertence ao proprietário
        $stmt = $this->db->prepare("
            SELECT s.talhao_id
            FROM safra s
            JOIN talhoes t     ON t.id  = s.talhao_id
            JOIN propriedade p ON p.id  = t.propriedade_id
            WHERE s.id = ? AND p.proprietario_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $safra_id, $uid);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$result) {
            $this->setToast('error', 'Acesso negado', 'Safra não encontrada.');
            $this->redirect('safras');
            return;
        }

        $talhao_id = (int) $result['talhao_id'];

        try {
            // 1. Encerra a safra com a data de hoje
            $stmt = $this->db->prepare(
                "UPDATE safra SET data_fim = CURDATE() WHERE id = ?"
            );
            $stmt->bind_param("i", $safra_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao encerrar safra: {$this->db->error}");
            }
            $stmt->close();

            // 2. Libera o talhão
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