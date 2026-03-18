<?php
require_once 'config.php';

class Conexao {

    private static $host;
    private static $usuario;
    private static $senha;
    private static $banco;

    private static $conexao;

    private static function carregarConfig() {
        self::$host = $_ENV['DB_HOST'];
        self::$usuario = $_ENV['DB_USER'];
        self::$senha = $_ENV['DB_PASS'];
        self::$banco = $_ENV['DB_NAME'];
    }

    public static function getConexao() {
        if (!self::$conexao) {
            self::carregarConfig();

            self::$conexao = new mysqli(
                self::$host,
                self::$usuario,
                self::$senha,
                self::$banco
            );
        }

        return self::$conexao;
    }
}