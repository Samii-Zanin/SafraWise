<?php

Class Talhao {

    private int $id;
    private string $nome;
    private float $area_hectare;
    private string $coordenadas_json;
    private int $propriedade_id;

    public function __construct(
        int $id,
        string $nome,
        float $area_hectare,
        string $coordenadas_json,
        int $propriedade_id
    ){
        $this->id = $id;
        $this->nome = $nome;
        $this->area_heactare = $area_heactare;
        $this->coordenadas_json = $coordenadas_json;
        $this->propriedade_id = $propriedade_id;
    } 
    
    public function getId(): int {
        return $this->id;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getAreaHectare(): float {
        return $this->area_hectare;
    }

    public function getCoordenadasJson(): string {
        return $this->coordenadas_json;
    }

    public function getPropriedadeId(): int {
        return $this->propriedade_id;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }
    public function setAreaHectare(float $area_hectare): void {
        $this->area_hectare = $area_hectare;
    }
    public function setCoordenadasJson(string $coordenadas_json): void {
        $this->coordenadas_json = $coordenadas_json;
    }
    public function setPropriedadeId(int $propriedade_id): void {
        $this->propriedade_id = $propriedade_id;
    }

}