<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/cotacoes.php';

class CotacoesController extends BaseController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexao::getConexao();
    }

    public function buscarParaTicker(): array
{
    $stmt = $this->db->prepare(
        "SELECT produto, preco, DATE(data) as data, variacao_mensal, unidade, uf
         FROM cotacoes
         WHERE data = (SELECT MAX(data) FROM cotacoes)
           AND (
               unidade LIKE '%SACA%'
            OR unidade LIKE '%TON%'
            OR unidade LIKE '%CX%'
            OR unidade LIKE '%CENTS%'
           )
         ORDER BY produto, uf"
    );
    $stmt->execute();

    $cotacoes = [];
    $result   = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
       
        $dataCotacao = date('d/m/Y', strtotime($row['data']));
    
        $cotacoes[] = [
            'cultura'   => $row['produto'],
            'estado'    => $row['uf'],
            'preco'     => number_format((float) $row['preco'], 2, ',', '.'),
            'unidade'   => $row['unidade'],
            'tendencia' => (float) $row['variacao_mensal'] >= 0 ? 'up' : 'down',
        ];
    }

    $stmt->close();
    return [
    'cotacoes'      => $cotacoes,
    'dataCotacao'   => $dataCotacao ?? 'N/D',];;
    }

    public function getCotacoesToTicker(): void
    {
        $cotacoes = $this->buscarParaTicker();
        require_once __DIR__ . '/../views/layouts/ticker.php';
    }
}