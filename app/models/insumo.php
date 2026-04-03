<?php

Class Insumo {

    private int $id;
    private float $valor_por_dose;
    private int $produto_id;

    public function __construct(
        float $valor_por_dose,
        int $produto_id
    ){
        $this->valor_por_dose = $valor_por_dose;
        $this->produto_id = $produto_id;
    }
    
    public function getId(): int {
        return $this->id;
    }
    public function getValorPorDose(): float {
        return $this->valor_por_dose;
    }
    public function getProdutoId(): int {
        return $this->produto_id;
    }

    public function setValorPorDose(float $valor_por_dose): void {
        $this->valor_por_dose = $valor_por_dose;
    }
    public function setProdutoId(int $produto_id): void {
        $this->produto_id = $produto_id;
    }
}