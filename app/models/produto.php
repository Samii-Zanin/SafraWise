<?php

Class Produto {

    private int $id;
    private string $nome;
    private string $marca;
    private string $unidade_medida;
    private string $descricao;
    private string $tipo;

    public function __construct(
        int $id,
        string $nome,
        string $marca,
        string $unidade_medida,
        string $descricao,
        string $tipo
    ){
        $this->id = $id;
        $this->nome = $nome;
        $this->marca = $marca;
        $this->unidade_medida = $unidade_medida;
        $this->descricao = $descricao;
        $this->tipo = $tipo;
    } 
    
    public function getId(): int {
        return $this->id;
    }
    public function getNome(): string {
        return $this->nome;
    }
    public function getMarca(): string {
        return $this->marca;
    }
    public function getUnidadeMedida(): string {
        return $this->unidade_medida;
    }
    public function getDescricao(): string {
        return $this->descricao;
    }
    public function getTipo(): string {
        return $this->tipo;
    }
    public function setNome(string $nome): void {
        $this->nome = $nome;
    }
    public function setUnidadeMedida(string $unidade_medida): void {
        $this->unidade_medida = $unidade_medida;
    }
    public function setDescricao(string $descricao): void {
        $this->descricao = $descricao;
    }
}