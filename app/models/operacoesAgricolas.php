<?php

Class OperacoesAgricolas {

    private int $id;
    private string $tipo_operacao;
    private DateTime $data_operacao; 
    private string $descricao;
    private int $insumo_id;                
    private int $safra_id;               
    private int $talhao_id;                
    private int $peao_id;                  
    private float $quantidade_insumo;

    public function __construct(
        int $id,
        string $tipo_operacao,
        DateTime $data_operacao,     
        string $descricao,
        int $insumo_id,
        int $safra_id,
        int $talhao_id,
        int $peao_id,
        float $quantidade_insumo
    ){
        $this->id               = $id;
        $this->tipo_operacao    = $tipo_operacao;
        $this->data_operacao    = $data_operacao;
        $this->descricao        = $descricao;
        $this->insumo_id        = $insumo_id;
        $this->safra_id       = $safra_id;
        $this->talhao_id        = $talhao_id;
        $this->peao_id          = $peao_id;
        $this->quantidade_insumo = $quantidade_insumo;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTipoOperacao(): string {
        return $this->tipo_operacao;
    }

    public function getDataOperacao(): DateTime {
        return $this->data_operacao;
    }

    public function getDescricao(): string {
        return $this->descricao;
    }

    public function getInsumoId(): int {
        return $this->insumo_id;
    }

    public function getSafraId(): int {
        return $this->safra_id;
    }

    public function getTalhaoId(): int {
        return $this->talhao_id;
    }

    public function getPeaoId(): int {
        return $this->peao_id;
    }

    public function getQuantidadeInsumo(): float {
        return $this->quantidade_insumo;
    }

    public function setTipoOperacao(string $tipo_operacao): void {
        $this->tipo_operacao = $tipo_operacao;
    }

    public function setDataOperacao(DateTime $data_operacao): void {
        $this->data_operacao = $data_operacao;
    }

    public function setDescricao(string $descricao): void {
        $this->descricao = $descricao;
    }

    public function setQuantidadeInsumo(float $quantidade_insumo): void {
        $this->quantidade_insumo = $quantidade_insumo;
    }
}