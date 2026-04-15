<?php
session_start();
require_once "../app/controllers/AuthController.php";

$page = $_GET['page'] ?? 'login';

// Carrega uma view com segurança — se o arquivo não existir, mostra 404
function loadView(string $view): void {
    $path = "../app/views/{$view}.php";
    if (!file_exists($path)) {
        http_response_code(404);
        require_once "../app/views/notfound.php";
        exit;
    }
    require_once $path;
}

// Redireciona para login se não houver sessão ativa
function requireAuth(): void {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?page=login");
        exit;
    }
}

switch ($page) {
    case 'login':
        loadView('login');
        break;

    case 'auth':
        $auth = new AuthController();
        $auth->login();
        break;

    case 'dashboard':
        requireAuth();
        loadView('dashboard');
        break;

    case 'talhoes':
        requireAuth();
        loadView('talhoes');
        break;

    case 'insumos':
        requireAuth();
        loadView('insumos');
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php?page=login");
        exit;

    default:
        http_response_code(404);
        require_once "../app/views/notfound.php";
        break;
}