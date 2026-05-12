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

    public function index(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado', 'Apenas proprietários podem acessar as safras.');
            $this->redirect('dashboard');
        }

        $proprietarioId = $_SESSION['user']['id'];

        $stmt = $this->db->prepare(
            "select
                s.id,
                c.nome as cultura,
                c.variedade, 
                t.nome as nome_talhao,
                t.area_hectare as area_talhao,
                t.status as status_talhao,
                p.nome as nome_propriedade,
                p.municipio,
                p.estado, 
                s.data_inicio,
                s.data_fim
            from
                safra s
            left join cultura c on c.id = s.cultura_id
            left join talhoes t on t.id = s.talhao_id
            left join propriedade p on t.propriedade_id = p.id
             WHERE proprietario_id = ?
             ORDER BY data_inicio DESC"
        );
        $stmt->bind_param("i", $proprietarioId);
        $stmt->execute();

        $safras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        require_once __DIR__ . '/../views/safras.php';
    }

    public function save(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cadastro_safra');
        }

        $data_inicio = trim($_POST['data_inicio'] ?? '');
        $data_fim = trim($_POST['data_fim'] ?? '');
        $cultura_id = trim($_POST['cultura_id'] ?? '');
        $talhao_id = trim($_POST['talhao_id'] ?? '');

        if (empty($data_inicio) || empty($data_fim) || empty($cultura_id) || empty($talhao_id)) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Por favor, preencha todos os campos.');
            $this->redirect('cadastro_safra');
        }

        if (empty($nome) || empty($cpf_cnpj) || empty($email) || empty($senha)) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Por favor, preencha todos os campos.');
            $this->redirect('cadastro_proprietario');
        }

        try {
            // if ($this->proprietarioJaExiste($email, $cpf_cnpj)) {
            //     $this->setToast('error', 'Cadastro Duplicado', 'Este e-mail ou CPF/CNPJ já está cadastrado.');
            //     $this->redirect('cadastro_proprietario');
            // }

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare(
                "INSERT INTO proprietario (nome, cpf_cnpj, telefone, email, senha)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $nome, $cpf_cnpj, $telefone, $email, $senhaHash);

            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir proprietário: {$this->db->error}");
            }

            $stmt->close();

            $this->setToast('success', 'Conta criada!', 'Seu cadastro foi realizado. Faça login no sistema.');
            $this->redirect('login');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno', 'Ocorreu um problema ao salvar. Tente novamente.');
            $this->redirect('cadastro_proprietario');
        }
    }
}
