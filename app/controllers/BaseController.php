<?php

abstract class BaseController
{
    protected function setToast(string $tipo, string $titulo, string $mensagem): void
    {
        $_SESSION['toast'] = [
            'tipo'     => $tipo,
            'titulo'   => $titulo,
            'mensagem' => $mensagem,
        ];
    }

    protected function redirect(string $page): never
    {
        header("Location: index.php?page={$page}");
        exit;
    }
}