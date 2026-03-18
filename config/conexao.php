<?php
class Conexao {

    private static $host = "localhost";
    private static $usuario = "root";
    private static $senha = "";
    private static $banco = "patp_ads5";

    private static $conexao;

    public static function getConexao() {
        if (!isset(self::$conexao)) {
            self::$conexao = new mysqli(
                self::$host,
                self::$usuario,
                self::$senha,
                self::$banco
            );
            if (self::$conexao->connect_error) {
                die("Erro de conexão: " . self::$conexao->connect_error);
            }
        }
        return self::$conexao;
    }
}