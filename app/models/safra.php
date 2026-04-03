<?php

Class Safra {

    private int $id;
    private DateTime  $data_inicio;
    private DateTime  $data_fim;
    private int $cultura_id;
    private int $talhao_id;

    public function __construct(
        int $id,
        DateTime  $data_inicio,
        DateTime  $data_fim,
        int $cultura_id,
        int $talhao_id
    ){
        $this->id = $id;
        $this->data_inicio = $data_inicio;
        $this->data_fim = $data_fim;
        $this->cultura_id = $cultura_id;
        $this->talhao_id = $talhao_id;
    } 
    
    public function getId(): int {
        return $this->id;
    }
    public function getDataInicio(): DateTime  {
        return $this->data_inicio;
    }
    public function getDataFim(): DateTime  {
        return $this->data_fim;
    }
    public function getCulturaId(): int {
        return $this->cultura_id;
    }
    public function getTalhaoId(): int {
        return $this->talhao_id;
    }
    public function setDataInicio(string $data_inicio): void {
        $this->data_inicio = $data_inicio;
    }
    public function setDataFim(string $data_fim): void {
        $this->data_fim = $data_fim;
    }
}