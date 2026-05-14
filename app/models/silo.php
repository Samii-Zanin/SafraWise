<?php

Class Silo {

    private int $id;
    private int $propriedade_id;
    private string $cultura;
    private float $quantidade_kg;
    private float $capacidade_kg;

    public function __construct(
        int $insumo_id,
        float $quantidade_kg,
        float $capacidade_kg,
        int $propriedade_id,
        string $cultura

    ){
        $this->propriedade_id = $propriedade_id;
        $this->cultura = $cultura;
        $this->quantidade_kg = $quantidade_kg;
        $this->capacidade_kg = $capacidade_kg;
    } 
    
    public function getId(): int {
        return $this->id;
    }
    public function getQuantidade(): float {
        return $this->quantidade_kg;
    }
    public function getCultura(): string {
        return $this->cultura;
    }
    public function getCapacidade(): float {
        return $this->capacidade_kg;
    }
    public function getPropriedadeId(): int {
        return $this->propriedade_id;
    }
    public function setCultura(string $cultura): void {
        $this->cultura = $cultura;
    }
    public function setQuantidade(float $quantidade): void {
        $this->quantidade_kg = $quantidade;
    }
    public function setCapacidade(float $capacidade): void {
        $this->capacidade_kg = $capacidade;
    }
}