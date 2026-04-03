<?php

Class Cotacoes {

    private int $id;
    private string $produto;
    private string $praca;
    private DateTime  $data;
    private float $preco;
    private float $variacao_mensal;
    private string $moeda;
    private string $unidade;
    

    public function __construct(
        int $id,
        string $produto,
        string $praca,
        string $data,
        float $preco,
        float $variacao_mensal,
        string $moeda,
        string $unidade
    ){
        $this->id = $id;
        $this->produto = $produto;
        $this->praca = $praca;
        $this->data = $data;
        $this->preco = $preco;
        $this->variacao_mensal = $variacao_mensal;
        $this->moeda = $moeda;
        $this->unidade = $unidade;
    } 
    
    public function getId(): int {
        return $this->id;
    }
    public function getProduto(): string {
        return $this->produto;
    }
    public function getPraca(): string {
        return $this->praca;
    }
    public function getData(): DateTime  {
        return $this->data;
    }
    public function getPreco(): float {
        return $this->preco;
    }
    public function getVariacaoMensal(): float {
        return $this->variacao_mensal;
    }
    public function getMoeda(): string {
        return $this->moeda;
    }
    public function getUnidade(): string {
        return $this->unidade;
    }}