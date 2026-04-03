<?php

Class OperacoesFinanceiras {

    private int $id;
    private string $tipo_operacao;
    private float $valor_operacao;
    private string $descricao;
    private int $produto_id;   
    private float $quantidade;
    private int $safra_id;    
    private DateTime $data_operacao;

    public function __construct(
        int $id,
        string $tipo_operacao,
        float $valor_operacao,
        string $descricao,
        int $produto_id,
        float $quantidade,
        int $safra_id,
        DateTime $data_operacao
    ){
        $this->id             = $id;
        $this->tipo_operacao  = $tipo_operacao;
        $this->valor_operacao = $valor_operacao;
        $this->descricao      = $descricao;
        $this->produto_id     = $produto_id;
        $this->quantidade     = $quantidade;
        $this->safra_id       = $safra_id;
        $this->data_operacao  = $data_operacao;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTipoOperacao(): string {
        return $this->tipo_operacao;
    }

    public function getValorOperacao(): float {
        return $this->valor_operacao;
    }

    public function getDescricao(): string {
        return $this->descricao;
    }

    public function getProdutoId(): int {
        return $this->produto_id;
    }

    public function getQuantidade(): float {
        return $this->quantidade;
    }

    public function getSafraId(): int {
        return $this->safra_id;
    }

    public function setTipoOperacao(string $tipo_operacao): void {
        $this->tipo_operacao = $tipo_operacao;
    }

    public function setValorOperacao(float $valor_operacao): void {
        $this->valor_operacao = $valor_operacao;
    }

    public function setDescricao(string $descricao): void {
        $this->descricao = $descricao;
    }

    public function setQuantidade(float $quantidade): void {
        $this->quantidade = $quantidade;
    }
}