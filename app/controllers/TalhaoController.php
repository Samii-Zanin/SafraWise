<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/SafraController.php';
require_once __DIR__ . '/../../config/conexao.php';
class TalhaoController {
    private mysqli $db;

    public function __construct() {
        $this->db= Conexao::getConexao();
    }

    public function index(): void {
        //  Puxa o ID do dono da fazenda
        $proprietario_id = $_SESSION['proprietario_id'];

        // Busca propriedades para o Select do modal
        $stmtProp = $this->db->prepare("SELECT id, nome, area_produtiva FROM propriedade WHERE proprietario_id = ?");
        $stmtProp->bind_param("i", $proprietario_id);
        $stmtProp->execute();
        $propriedades = $stmtProp->get_result()->fetch_all(MYSQLI_ASSOC);

        // Busca talhões e o nome da propriedade vinculada
        $sql = "SELECT t.*, p.nome as propriedade_nome 
                FROM talhoes t 
                JOIN propriedade p ON t.propriedade_id = p.id 
                WHERE p.proprietario_id = ?";
        $stmtTal = $this->db->prepare($sql);
        $stmtTal->bind_param("i", $proprietario_id);
        $stmtTal->execute();
        $talhoes = $stmtTal->get_result()->fetch_all(MYSQLI_ASSOC);

        require_once "../app/views/talhoes.php";
    }
    
    public function getVazios(): array {
        // Puxa o ID do dono da fazenda
        $proprietario_id = $_SESSION['proprietario_id'];

        $stmtProp = $this->db->prepare("
        select
            t.id, t.nome, p.nome as propriedade_nome, t.area_hectare, t.status
        from
            talhoes t
        left join propriedade p on t.propriedade_id = p.id
        where
            t.status = 'Vazio'
            and
            t.id not in (
            select
                talhao_id
            from
                safra
            where
                data_fim is null)
            and p.proprietario_id = ?
                ");
        $stmtProp->bind_param("i", $proprietario_id);
        $stmtProp->execute();
        $talhoes = $stmtProp->get_result()->fetch_all(MYSQLI_ASSOC);

        return $talhoes;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM talhoes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public function getAll(): array {
        // Puxa o ID do dono da fazenda
        $proprietario_id = $_SESSION['proprietario_id'];

        $stmtProp = $this->db->prepare("
        select
            t.id, t.nome, p.nome as propriedade_nome, t.area_hectare, t.status
        from
            talhoes t
        left join propriedade p on t.propriedade_id = p.id
        where
            t.id not in (
            select
                talhao_id
            from
                safra
            where
                data_fim is null)
            and p.proprietario_id = ?
                ");
        $stmtProp->bind_param("i", $proprietario_id);
        $stmtProp->execute();
        $talhoes = $stmtProp->get_result()->fetch_all(MYSQLI_ASSOC);

        return $talhoes;
    }

    public function store(): void {
        //  Peão não pode criar talhão
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->redirectError("Apenas o proprietário pode criar novos talhões.");
        }

        $nome = trim($_POST['nome']);
        $area = floatval($_POST['area_hectare']);
        $propriedade_id = intval($_POST['propriedade_id']);
        $tipo_posse = $_POST['tipo_posse']; // PROPRIO ou ARRENDADO
        $custo = ($tipo_posse === 'ARRENDADO') ? floatval($_POST['custo_arrendamento_sacas']) : 0;

        // 🛑 TRAVA DE SEGURANÇA: Área
        if ($area <= 0) {
            $this->redirectError("A área do talhão deve ser maior que zero.");
        }

        // 🛑 TRAVA DE SEGURANÇA: Custo
        if ($tipo_posse === 'ARRENDADO' && $custo < 0) {
            $this->redirectError("O custo do arrendamento não pode ser um valor negativo.");
        }
        
        if (!$this->validarAreaDisponivel($propriedade_id, $area)) {
            $this->redirectError("Área excede o limite produtivo da fazenda.");
        }

        $stmt = $this->db->prepare("INSERT INTO talhoes (nome, area_hectare, propriedade_id, tipo_posse, custo_arrendamento_sacas, status) VALUES (?, ?, ?, ?, ?, 'Vazio')");
        $stmt->bind_param("sdisd", $nome, $area, $propriedade_id, $tipo_posse, $custo);
        
        $this->finalizarAcao($stmt->execute(), "Talhão criado!", "Erro ao criar.");
    }

    public function update(): void {
        //  Peão não pode editar talhão
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->redirectError("Apenas o proprietário pode editar os dados dos talhões.");
        }

        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $area = floatval($_POST['area_hectare']);
        $propriedade_id = intval($_POST['propriedade_id']);
        $tipo_posse = $_POST['tipo_posse'];
        $custo = ($tipo_posse === 'ARRENDADO') ? floatval($_POST['custo_arrendamento_sacas']) : 0;

        // 🛑 TRAVA DE SEGURANÇA: Área
        if ($area <= 0) {
            $this->redirectError("A área do talhão deve ser maior que zero.");
        }

        // 🛑 TRAVA DE SEGURANÇA: Custo
        if ($tipo_posse === 'ARRENDADO' && $custo < 0) {
            $this->redirectError("O custo do arrendamento não pode ser um valor negativo.");
        }
        
        //  Passa o $id para não somar a área antiga com a nova durante a edição
        if (!$this->validarAreaDisponivel($propriedade_id, $area, $id)) {
            $this->redirectError("Área excede o limite produtivo da fazenda.");
        }
        
        $stmt = $this->db->prepare("UPDATE talhoes SET nome = ?, area_hectare = ?, tipo_posse = ?, custo_arrendamento_sacas = ? WHERE id = ?");
        $stmt->bind_param("sdsdi", $nome, $area, $tipo_posse, $custo, $id);
        
        $this->finalizarAcao($stmt->execute(), "Talhão atualizado!", "Erro na atualização.");
    }

    public function delete(): void {
        // Peão não pode excluir talhão
        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->redirectError("Apenas o proprietário pode excluir talhões da fazenda.");
        }

        $id = intval($_GET['id']);
        
        try {
            $stmt = $this->db->prepare("DELETE FROM talhoes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $this->finalizarAcao($stmt->execute(), "Talhão removido.", "Erro ao remover.");
        } catch (Exception $e) {
            $this->redirectError("Não é possível excluir: este talhão possui safras vinculadas.");
        }
    }

    // Auxiliar: Verifica se a soma dos talhões não estoura a fazenda
    private function validarAreaDisponivel($propId, $novaArea, $talhaoIdExcluir = 0): bool {
        // 1. Busca o limite da área produtiva da fazenda
        $stmt = $this->db->prepare("SELECT area_produtiva FROM propriedade WHERE id = ?");
        $stmt->bind_param("i", $propId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $limite = $res['area_produtiva'] ?? 0;

        // 2. Soma a área dos talhões já existentes (ignorando o atual em caso de edição)
        $stmtSum = $this->db->prepare("SELECT SUM(area_hectare) as total FROM talhoes WHERE propriedade_id = ? AND id != ?");
        $stmtSum->bind_param("ii", $propId, $talhaoIdExcluir);
        $stmtSum->execute();
        $resSum = $stmtSum->get_result()->fetch_assoc();
        $atual = $resSum['total'] ?? 0;

        // Retorna true se a nova soma couber no limite da fazenda
        return ($atual + $novaArea) <= $limite;
    }

    private function redirectError($msg) {
        $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'Ops!', 'mensagem' => $msg];
        $this->voltarParaPaginaCorreta();
    }

    // Auxiliar: Envia sucesso/erro e volta para a tela certa
    private function finalizarAcao($sucesso, $msgS, $msgE) {
        if ($sucesso) {
            $_SESSION['toast'] = ['tipo' => 'success', 'titulo' => 'Sucesso', 'mensagem' => $msgS];
        } else {
            $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'Erro', 'mensagem' => $msgE];
        }
        $this->voltarParaPaginaCorreta();
    }

    // Lógica para saber pra qual tela a página deve redirecionar
    private function voltarParaPaginaCorreta() {
        // Tenta pegar o ID da propriedade via POST (quando cria/edita) ou via GET (quando exclui)
        $prop_id = $_POST['propriedade_id'] ?? $_GET['propriedade_id'] ?? null;
        
        if ($prop_id) {
            header("Location: index.php?page=detalhes_propriedade&id=" . intval($prop_id));
        } else {
            // Fallback de segurança: se o ID se perder, volta para a listagem de propriedades
            header("Location: index.php?page=propriedades");
        }
        exit;
    }
}