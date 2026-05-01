<?php
// app/controllers/validator/PeaoValidator.php

require_once __DIR__ . '/../../../config/conexao.php';

class PeaoValidator
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function cpfJaExiste(string $cpf_cnpj): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM peao WHERE cpf_cnpj = ? LIMIT 1");
        $stmt->bind_param("s", $cpf_cnpj);
        $stmt->execute();
        $stmt->store_result();

        $existe = $stmt->num_rows > 0;
        $stmt->close();

        return $existe;
    }
    public function validar(array $dados): array
    {
        $erros = [];

        if (empty($dados['nome'])) {
            $erros[] = 'Nome é obrigatório.';
        }

        if (empty($dados['cpf_cnpj'])) {
            $erros[] = 'CPF é obrigatório.';
        }

        if (empty($dados['senha'])) {
            $erros[] = 'Senha é obrigatória.';
        }

        return $erros;
    }
}