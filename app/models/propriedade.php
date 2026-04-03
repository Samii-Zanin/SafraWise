<?php

Class Propriedade {

    private int $id;
    private string $nome;
    private string $localizacao;
    private string $municipio;
    private string $estado;
    private float $area_total;
    private float $area_produtiva;
    private int $proprietario_id;

    public function __construct(
        int $id,
        string $nome,
        string $localizacao,
        string $municipio,
        string $estado,
        float $area_total,
        float $area_produtiva,
        int $proprietario_id
    ){
        $this->id = $id;
        $this->nome = $nome;
        $this->localizacao = $localizacao;
        $this->municipio = $municipio;
        $this->estado = $estado;
        $this->area_total = $area_total;
        $this->area_produtiva = $area_produtiva;
        $this->proprietario_id = $proprietario_id;
    } 
    
    public function getId(): string {
        return $this->id;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getLocalizacao(): string {
        return $this->localizacao;
    }

    public function getMunicipio(): string {
        return $this->municipio;
    }

    public function getEstado(): string {
        return $this->estado;
    }

    public function getAreaTotal(): float {
        return $this->area_total;
    }

    public function getAreaProdutiva(): float {
        return $this->area_plodutiva;
    }

    public function getProprietarioId(): int {
        return $this->proprietario_id;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setLocalizacao(string $localizacao): void {
        $this->localizacao = $localizacao;
    }

    public function setMunicipio(string $municipio): void {
        $this->municipio = $municipio;
    }

    public function setEstado(string $estado): void {
        $this->estado = $estado;
    }

    public function setAreaTotal(float $area_total): void {
        $this->area_total = $area_total;
    }

    public function setAreaProdutiva(float $area_plodutiva): void {
        $this->area_plodutiva = $area_plodutiva;
    }

    public function setProprietarioId(int $proprietario_id): void {
        $this->proprietario_id = $proprietario_id;
    }
}