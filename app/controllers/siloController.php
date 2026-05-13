<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';
// require_once __DIR__ . '/validator/siloValidator.php';

class SiloController extends BaseController
{
    private mysqli $db;
    // private SiloValidator $validator;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
        // $this->validator = new SiloValidator();
    }

    public function getAll(): array
{
    $proprietarioId = $_SESSION['user']['id'];

    $stmt = $this->db->prepare("
        WITH culturas_distintas AS (
            SELECT DISTINCT nome FROM cultura
        )
        SELECT
            cd.nome          AS cultura,
            p.nome           AS propriedade,
            p.municipio,
            p.estado,
            SUM(silo.quantidade_kg) AS total_kg
        FROM silo
        JOIN cultura c           ON silo.cultura_id     = c.id
        JOIN propriedade p       ON silo.propriedade_id = p.id
        JOIN culturas_distintas cd ON cd.nome = c.nome
        WHERE p.proprietario_id = ?
        GROUP BY cd.nome, p.nome, p.municipio, p.estado
        ORDER BY cd.nome, p.nome
    ");
    $stmt->bind_param("s", $proprietarioId);
    $stmt->execute();
    $estoques = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $estoques;
}

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        $stmt = $this->db->prepare("
    WITH culturas_distintas AS (
    SELECT DISTINCT nome
    FROM cultura)
SELECT
    cd.nome as nome_cultura,
    p.nome as nome_propriedade,
    p.municipio,
    p.estado,
    SUM(silo.quantidade_kg) AS total_kg
    FROM silo
    JOIN cultura c
        ON silo.cultura_id = c.id
    JOIN propriedade p
        ON silo.propriedade_id = p.id
    JOIN culturas_distintas cd
        ON cd.nome = c.nome
    WHERE p.proprietario_id = ?
    GROUP BY 
    cd.nome,
    p.nome,
    p.municipio,
    p.estado");
        $stmt->bind_param("i", $_SESSION['user']['id']);
        $stmt->execute();
        $estoques = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
        }

        // if ($this->validator->SiloJaExiste((int) $dados['silo_id'])) {
        //     $this->setToast('error', 'Erro', 'Já existe um silo cadastrado com para esta cultura.');
        //     $this->redirect('estoques');
        // }

        $dados = [
            'propriedade_id'           => trim($_POST['propriedade_id']           ?? ''),
            'silo_id'      => trim($_POST['silo_id']      ?? ''),
            'quantidade_kg'      => trim($_POST['quantidade_kg']      ?? 0),
        ];

        if (empty($dados['silo_id'])) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Silo é obrigatório.');
            $this->redirect('estoques');
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO silo (propriedade_id, silo_id, quantidade_kg) VALUES (?, ?, ?)"
            );
            $stmt->bind_param(
                "iif",
                $dados['propriedade_id'], $dados['silo_id'], $dados['quantidade_kg']
            );
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir silo: {$this->db->error}");
            }
            $siloId = $this->db->insert_id;
            $stmt->close();

            $this->setToast('success', 'Silo Cadastrado!', "O silo foi adicionado com sucesso.");
            $this->redirect('estoques');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Não foi possível salvar o silo. Tente novamente.'. $e->getMessage());
            $this->redirect('estoques');
        }
    }
}
