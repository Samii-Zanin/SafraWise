<?php

require_once 'proprietario.php';

Class Peao {

    private  int $id;
    private string $nome;
    private string $cpf_cnpj;
    private string $telefone;
    private string $email;
    private string $senha;
    private int $proprietario_id;

    public function __construct(
        string $nome,
        string $cpf_cnpj,
        string $telefone,
        string $email,
        string $senha,
        int $proprietario_id
    ){
        $this->cpf_cnpj = $cpf_cnpj;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->telefone = $telefone;
        $this->proprietario_id = $proprietario_id;
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getCpfCnpj(): string {
        return $this->cpf_cnpj;
    }

    public function getTelefone(): string {
        return $this->telefone;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setTelefone(string $telefone): void {
        $this->telefone = $telefone;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setSenha(string $senha): void {
        $this->senha = $senha;
    }


}