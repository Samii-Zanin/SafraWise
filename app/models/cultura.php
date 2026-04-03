<?php

Class Cultura {

    private int $id;
    private string $nome;
    private string $variedade;

    public function __construct(
        string $nome,
        string $variedade
    )
    {
        $this->nome = $nome;
        $this->variedade = $variedade;
    } 
    
    public function getId(): int {
        return $this->id;
    }
    public function getNome(): string {
        return $this->nome;
    }
    public function getVariedade(): string {
        return $this->variedade;
    }
    }