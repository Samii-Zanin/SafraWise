<?php

Class EsrtoqueInsumos {

    private int $id;
    private int $insumo_id;
    private float $quantidade;
    private int $propriedade_id;

    public function __construct(
        int $insumo_id,
        float $quantidade,
        int $propriedade_id
    ){
        $this->insumo_id = $insumo_id;
        $this->quantidade = $quantidade;
        $this->propriedade_id = $propriedade_id;
    } 
    
    public function getId(): int {
        return $this->id;
    }
    public function getInsumoId(): int {
        return $this->insumo_id;
    }
    public function getQuantidade(): float {
        return $this->quantidade;
    }
    public function getPropriedadeId(): int {
        return $this->propriedade_id;
    }
    public function setQuantidade(float $quantidade): void {
        $this->quantidade = $quantidade;
    }
}