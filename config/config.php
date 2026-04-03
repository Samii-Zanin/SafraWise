<?php
function carregarEnv($arquivo) {
    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        if (strpos(trim($linha), '#') === 0) continue;

        list($chave, $valor) = explode('=', $linha, 2);
        $_ENV[$chave] = $valor;
    }
}

carregarEnv(__DIR__ . '/.env');