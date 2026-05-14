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

    public function EstoqueParaEsteInsumoJaExiste(int $insumo_id): bool
    {
        $stmt = $this->db->prepare("SELECT insumo_id FROM estoque_insumos WHERE insumo_id = ? LIMIT 1");
        $stmt->bind_param("i", $insumo_id);
        $stmt->execute();
        $stmt->store_result();

        $existe = $stmt->num_rows > 0;
        $stmt->close();

        return $existe;
    }
}