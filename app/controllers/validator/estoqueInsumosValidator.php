<?php
// app/controllers/validator/EstoqueInsumosValidator.php

require_once __DIR__ . '/../../../config/conexao.php';

class EstoqueInsumosValidator
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    // Agora verifica se o insumo existe DENTRO daquela propriedade específica
    public function EstoqueParaEsteInsumoJaExiste(int $insumo_id, int $propriedade_id): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM estoque_insumos WHERE insumo_id = ? AND propriedade_id = ? LIMIT 1");
        $stmt->bind_param("ii", $insumo_id, $propriedade_id);
        $stmt->execute();
        $stmt->store_result();

        $existe = $stmt->num_rows > 0;
        $stmt->close();

        return $existe;
    }
}