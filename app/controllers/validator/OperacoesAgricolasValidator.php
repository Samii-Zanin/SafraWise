<?php

require_once __DIR__ . '/../../../config/conexao.php';

class OperacoesAgricolasValidator
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function StatusSafraVinculada(int $safra_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT t.status
            FROM safra s
            LEFT JOIN talhoes t ON t.id = s.talhao_id
            WHERE s.id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $safra_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Retorna true se talhão NÃO está Vazio
        return !empty($result) && $result['status'] !== 'Vazio';
    }

    public function EstoqueInsumoDisponivel(int $insumo_id, float $quantidade): bool
    {
        $stmt = $this->db->prepare(
            "SELECT quantidade FROM estoque_insumos WHERE insumo_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $insumo_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return !empty($result) && (float)$result['quantidade'] >= $quantidade;
    }

    public function BuscaDadosdeEstoque(int $insumo_id, int $propriedade_id): array
    {
        $stmt = $this->db->prepare("
            SELECT
                e.id,
                e.quantidade,
                p.nome,
                p.tipo,
                p.marca
            FROM estoque_insumos e
            JOIN insumo i  ON i.id  = e.insumo_id
            JOIN produto p ON p.id  = i.produto_id
            WHERE e.insumo_id = ? AND e.propriedade_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $insumo_id, $propriedade_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?? [];
    }

    public function getPropriedadeIdByTalhao(int $talhao_id): int
    {
        $stmt = $this->db->prepare(
            "SELECT propriedade_id FROM talhoes WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $talhao_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) ($result['propriedade_id'] ?? 0);
    }
}