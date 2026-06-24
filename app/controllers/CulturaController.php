<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/conexao.php';

class CulturaController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    // ─── leitura (catálogo global — sem filtro de proprietário) ─────────────

    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nome, variedade FROM cultura ORDER BY nome ASC"
        );
        $stmt->execute();
        $culturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $culturas;
    }

    public function getDistintas(): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT id, nome, variedade FROM cultura ORDER BY nome ASC"
        );
        $stmt->execute();
        $culturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $culturas;
    }

    public function getNomeDistintas(): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT nome FROM cultura ORDER BY nome ASC"
        );
        $stmt->execute();
        $culturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $culturas;
    }

    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        $culturas = $this->getAll();

        require_once __DIR__ . '/../views/produtos_culturas.php';
    }

    // ─── escrita (exclusivo para proprietário) ───────────────────────────────

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode cadastrar novas culturas no catálogo.');
            $this->redirect('produtos_culturas');
            return;
        }

        $nome      = strtoupper(trim($_POST['nome']      ?? ''));
        $variedade = strtoupper(trim($_POST['variedade'] ?? ''));

        if (empty($nome)) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome é obrigatório.');
            $this->redirect('produtos_culturas');
            return;
        }

        // Impede duplicata de nome+variedade no catálogo
        if ($this->nomeVariedadeJaExiste($nome, $variedade)) {
            $this->setToast('warning', 'Cultura duplicada',
                "Já existe a cultura \"{$nome}\" com a variedade \"{$variedade}\" no catálogo.");
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            // 1. Insere a cultura
            $stmt = $this->db->prepare(
                "INSERT INTO cultura (nome, variedade) VALUES (?, ?)"
            );
            $stmt->bind_param("ss", $nome, $variedade);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao inserir cultura: {$this->db->error}");
            }
            $stmt->close();

            // 2. Cria o produto CEREAL correspondente se ainda não existir
            $stmt = $this->db->prepare(
                "SELECT id FROM produto WHERE UPPER(nome) = ? AND tipo = 'CEREAL' LIMIT 1"
            );
            $stmt->bind_param("s", $nome);
            $stmt->execute();
            $produtoExistente = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$produtoExistente) {
                $stmt = $this->db->prepare(
                    "INSERT INTO produto (nome, descricao, unidade_medida, tipo)
                     VALUES (?, ?, 'sc', 'CEREAL')"
                );
                $stmt->bind_param("ss", $nome, $variedade);
                if (!$stmt->execute()) {
                    throw new \RuntimeException("Falha ao inserir produto: {$this->db->error}");
                }
                $stmt->close();
            }

            $this->setToast('success', 'Cultura Cadastrada!',
                "{$nome} foi adicionada ao catálogo.");
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível salvar a cultura. Tente novamente.');
            $this->redirect('produtos_culturas');
        }
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode alterar os dados das culturas.');
            $this->redirect('produtos_culturas');
            return;
        }

        $cultura_id = (int) ($_POST['cultura_id'] ?? 0);

        if (!$cultura_id) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('produtos_culturas');
            return;
        }

        $nome      = strtoupper(trim($_POST['nome']      ?? ''));
        $variedade = strtoupper(trim($_POST['variedade'] ?? ''));

        // Alinhado com save(): apenas nome é obrigatório, variedade é opcional
        if (empty($nome)) {
            $this->setToast('warning', 'Campos Obrigatórios', 'Nome é obrigatório.');
            $this->redirect('produtos_culturas');
            return;
        }

        // Verifica duplicata excluindo o próprio registro
        if ($this->nomeVariedadeJaExiste($nome, $variedade, $cultura_id)) {
            $this->setToast('warning', 'Cultura duplicada',
                "Já existe outra cultura \"{$nome}\" com a variedade \"{$variedade}\" no catálogo.");
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE cultura SET nome = ?, variedade = ? WHERE id = ?"
            );
            $stmt->bind_param("ssi", $nome, $variedade, $cultura_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao atualizar cultura: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Cultura Atualizada!',
                "{$nome} foi atualizada com sucesso.");
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Erro interno',
                'Não foi possível atualizar a cultura. Tente novamente.');
            $this->redirect('produtos_culturas');
        }
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }

        if ($_SESSION['tipo'] !== 'proprietario') {
            $this->setToast('error', 'Acesso Negado',
                'Apenas o proprietário pode remover itens do catálogo.');
            $this->redirect('produtos_culturas');
            return;
        }

        $cultura_id = (int) ($_POST['cultura_id'] ?? 0);

        if (!$cultura_id) {
            $this->setToast('error', 'Erro', 'Identificador inválido.');
            $this->redirect('produtos_culturas');
            return;
        }

        // Bloqueia exclusão se a cultura estiver vinculada a safras
        if ($this->culturaEstaEmUso($cultura_id)) {
            $this->setToast('warning', 'Cultura em uso',
                'Esta cultura está vinculada a safras e não pode ser excluída.');
            $this->redirect('produtos_culturas');
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM cultura WHERE id = ?");
            $stmt->bind_param("i", $cultura_id);
            if (!$stmt->execute()) {
                throw new \RuntimeException("Falha ao excluir cultura: {$this->db->error}");
            }
            $stmt->close();

            $this->setToast('success', 'Cultura Removida',
                'A cultura foi excluída do catálogo com sucesso.');
            $this->redirect('produtos_culturas');

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->setToast('error', 'Não foi possível excluir',
                'Esta cultura pode estar vinculada a safras.');
            $this->redirect('produtos_culturas');
        }
    }

    // ─── helpers privados ────────────────────────────────────────────────────

    /**
     * Verifica duplicata de nome+variedade.
     * $excluirId ignora o próprio registro no UPDATE.
     */
    private function nomeVariedadeJaExiste(
        string $nome,
        string $variedade,
        int $excluirId = 0
    ): bool {
        $stmt = $this->db->prepare("
            SELECT id FROM cultura
            WHERE UPPER(nome) = UPPER(?) AND UPPER(variedade) = UPPER(?)
              AND id != ?
            LIMIT 1
        ");
        $stmt->bind_param("ssi", $nome, $variedade, $excluirId);
        $stmt->execute();
        $existe = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $existe;
    }

    /**
     * Verifica se a cultura está vinculada a alguma safra.
     * Usado para bloquear exclusão antes de tentar o DELETE.
     */
    private function culturaEstaEmUso(int $cultura_id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM safra WHERE cultura_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $cultura_id);
        $stmt->execute();
        $emUso = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $emUso;
    }
}