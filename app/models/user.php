<?php

Class User {

    private $id;
    private $cpf_cnpj;
    private $name;
    private $email;
    private $password;
    private $user_type;
    private $active;
    private $created_at;


    public function __contruct(
        string $id,
        string $cpf_cnpj,
        string $name,
        string $email,
        string $password,
        string $user_type,
        bool $active,
        string $created_at
    ){
        $this->id = $id;
        $this->cpf_cnpj = $cpf_cnpj;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->user_type = $user_type;
        $this->active = $active;
        $this->created_at = $created_at;
    }
    
    


}