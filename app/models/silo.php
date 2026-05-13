<?php

Class Silo {

    private int $id;
    private int $propriedade_id;
    private int $cultura_id;
    private float $quantidade_kg;

    public function __construct(
        int $insumo_id,
        float $quantidade_kg,
        int $propriedade_id,
        int $cultura_id
    ){
        $this->propriedade_id = $propriedade_id;
        $this->cultura_id = $cultura_id;
        $this->quantidade_kg = $quantidade_kg;
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