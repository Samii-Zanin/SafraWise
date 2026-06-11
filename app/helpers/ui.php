<?php
/**
 * app/helpers/ui.php
 * Helpers de UI compartilhados pelas views do SafraWise.
 * Substituem os blocos repetidos no topo de cada página.
 *
 * Uso no topo da view:
 *   require_once '../app/helpers/ui.php';
 *   $iniciais = iniciais($_SESSION['user']['nome']);
 *   $saudacao = saudacao();
 *   $toast    = flashToast();
 */

if (!function_exists('iniciais')) {
    /** Iniciais a partir do nome: 1ª letra do 1º + 1ª do último nome. */
    function iniciais(string $nome): string {
        $nome = trim($nome);
        if ($nome === '') {
            return '?';
        }
        if (str_contains($nome, ' ')) {
            $partes = explode(' ', $nome);
            return strtoupper($partes[0][0] . end($partes)[0]);
        }
        return strtoupper(substr($nome, 0, 1));
    }
}

if (!function_exists('saudacao')) {
    /** Saudação conforme a hora do dia. */
    function saudacao(): string {
        $h = (int) date('H');
        return $h < 12 ? 'Bom dia' : ($h < 18 ? 'Boa tarde' : 'Boa noite');
    }
}

if (!function_exists('flashToast')) {
    /** Lê e consome (remove) o toast da sessão. Retorna null se não houver. */
    function flashToast(): ?array {
        if (!isset($_SESSION['toast'])) {
            return null;
        }
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
}