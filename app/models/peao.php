<?php

require_once 'Proprietario.php';

class Peao {

    private ?int $id; 
    private string $nome;
    private string $cpf_cnpj;
    private ?string $telefone; 
    private ?string $email;    
    private string $senha;
    private int $proprietario_id;

    public function __construct(
        string $nome,
        string $cpf_cnpj,
        ?string $telefone,
        ?string $email,
        string $senha,
        int $proprietario_id
    ){
        $this->nome = $nome;
        $this->cpf_cnpj = $cpf_cnpj;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->senha = $senha;
        $this->proprietario_id = $proprietario_id;
    }
    
    // Setters opcionais para quando buscar do banco de dados
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getId(): ?int {
        return $this->id ?? null;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getCpfCnpj(): string {
        return $this->cpf_cnpj;
    }

    public function getTelefone(): ?string {
        return $this->telefone;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function getSenha(): string {
        return $this->senha;
    }

    public function getProprietarioId(): int {
        return $this->proprietario_id;
    }

    // Setters
    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function setTelefone(?string $telefone): void {
        $this->telefone = $telefone;
    }

    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function setSenha(string $senha): void {
        $this->senha = $senha;
    }
}