<?php

class PropriedadeController {
    private mysqli $db;

    public function __construct()
    {
        $this->db= Conexao::getConexao();
    }

    public function getAll(): array
{
    $uid  = (int) $_SESSION['user']['id'];
    $stmt = $this->db->prepare(
        "SELECT id, nome, municipio, estado FROM propriedade WHERE proprietario_id = ? ORDER BY nome"
    );
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

    // Listagem de propriedades
    public function index(): void {
        $proprietario_id = $_SESSION['user']['id'];

        $stmt = $this->db->prepare("SELECT * FROM propriedade WHERE proprietario_id = ?");
        $stmt->bind_param("i", $proprietario_id);
        $stmt->execute();
        $propriedades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        require_once "../app/views/propriedade.php";
    }

    // Salvar nova propriedade
    public function store(): void {
        $proprietario_id = $_SESSION['user']['id'];
        $nome = trim($_POST['nome']);
        $localizacao = trim($_POST['localizacao']);
        $municipio = trim($_POST['municipio']);
        $estado = strtoupper(trim($_POST['estado']));
        $area_total = floatval($_POST['area_total']);
        $area_produtiva = floatval($_POST['area_produtiva']);


        if ($area_total <= 0) {
            $_SESSION['toast'] = [
                'tipo' => 'error', 
                'titulo' => 'Área Inválida', 
                'mensagem' => 'A área total da propriedade deve ser maior que zero.'
            ];
            header("Location: index.php?page=propriedades");
            exit;
        }

        if ($area_produtiva < 0) {
            $_SESSION['toast'] = [
                'tipo' => 'error', 
                'titulo' => 'Área Inválida', 
                'mensagem' => 'A área produtiva não pode ser um valor negativo.'
            ];
            header("Location: index.php?page=propriedades");
            exit;
        }

        if ($area_produtiva > $area_total) {
            $_SESSION['toast'] = [
                'tipo' => 'error', 
                'titulo' => 'Conflito de Áreas', 
                'mensagem' => 'A área produtiva não pode ser maior do que a área total da fazenda.'
            ];
            header("Location: index.php?page=propriedades");
            exit;
        }

        $stmt = $this->db->prepare("INSERT INTO propriedade (nome, localizacao, municipio, estado, area_total, area_produtiva, proprietario_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddi", $nome, $localizacao, $municipio, $estado, $area_total, $area_produtiva, $proprietario_id);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['tipo' => 'success', 'titulo' => 'Sucesso', 'mensagem' => 'Propriedade cadastrada com sucesso!'];
        } else {
            $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'Erro', 'mensagem' => 'Falha ao cadastrar propriedade.'];
        }

        header("Location: index.php?page=propriedades");
        exit;
    }

    // Atualizar propriedade existente
    public function update(): void {
        $proprietario_id = $_SESSION['user']['id'];
        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $localizacao = trim($_POST['localizacao']);
        $municipio = trim($_POST['municipio']);
        $estado = strtoupper(trim($_POST['estado']));
        $area_total = floatval($_POST['area_total']);
        $area_produtiva = floatval($_POST['area_produtiva']);


        if ($area_total <= 0) {
            $_SESSION['toast'] = [
                'tipo' => 'error', 
                'titulo' => 'Área Inválida', 
                'mensagem' => 'A área total da propriedade deve ser maior que zero.'
            ];
            header("Location: index.php?page=propriedades");
            exit;
        }

        if ($area_produtiva < 0) {
            $_SESSION['toast'] = [
                'tipo' => 'error', 
                'titulo' => 'Área Inválida', 
                'mensagem' => 'A área produtiva não pode ser um valor negativo.'
            ];
            header("Location: index.php?page=propriedades");
            exit;
        }

        if ($area_produtiva > $area_total) {
            $_SESSION['toast'] = [
                'tipo' => 'error', 
                'titulo' => 'Conflito de Áreas', 
                'mensagem' => 'A área produtiva não pode ser maior do que a área total da fazenda.'
            ];
            header("Location: index.php?page=propriedades");
            exit;
        }
        
        $stmt = $this->db->prepare("UPDATE propriedade SET nome = ?, localizacao = ?, municipio = ?, estado = ?, area_total = ?, area_produtiva = ? WHERE id = ? AND proprietario_id = ?");
        $stmt->bind_param("ssssddii", $nome, $localizacao, $municipio, $estado, $area_total, $area_produtiva, $id, $proprietario_id);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['tipo' => 'success', 'titulo' => 'Atualizado', 'mensagem' => 'Dados da propriedade atualizados!'];
        } else {
            $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'Erro', 'mensagem' => 'Falha ao atualizar propriedade.'];
        }

        header("Location: index.php?page=propriedades");
        exit;
    }

    // Excluir propriedade
    public function delete(): void {
        $proprietario_id = $_SESSION['user']['id'];
        $id = intval($_GET['id']);

        // IMPORTANTE: Antes de excluir, o MySQL vai checar se existem talhões ou insumos amarrados a ela por conta da FK.
        try {
            $stmt = $this->db->prepare("DELETE FROM propriedade WHERE id = ? AND proprietario_id = ?");
            $stmt->bind_param("ii", $id, $proprietario_id);
            
            if ($stmt->execute()) {
                $_SESSION['toast'] = ['tipo' => 'success', 'titulo' => 'Removida', 'mensagem' => 'Propriedade excluída com sucesso.'];
            }
        } catch (mysqli_sql_exception $e) {
            // Se houver talhões vinculados, o banco impede a exclusão física para não corromper os dados
            $_SESSION['toast'] = ['tipo' => 'error', 'titulo' => 'Não permitido', 'mensagem' => 'Esta propriedade possui talhões ou insumos vinculados e não pode ser excluída.'];
        }

        header("Location: index.php?page=propriedades");
        exit;
    }
}