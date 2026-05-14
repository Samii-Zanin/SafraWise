<?php
session_start();
require_once "../app/controllers/AuthController.php";
require_once "../app/controllers/ProprietarioController.php";
require_once "../app/controllers/PeaoController.php";
require_once "../app/controllers/InsumoController.php";
require_once "../app/controllers/PropriedadeController.php";

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

    case 'equipe':
        requireAuth(); 
        $peaoController = new PeaoController();
        $peaoController->index(); 
        break;
    
    case 'talhoes':
        requireAuth();
        loadView('talhoes');
        break;

    case 'insumos':
        requireAuth();
        $insumoController = new InsumoController();
        $insumoController->index();
        break;

    case 'store_insumo':
        requireAuth();
        $insumoController = new InsumoController();
        $insumoController->save();
        break;

    case 'update_insumo':
        requireAuth();
        $insumoController = new InsumoController();
        $insumoController->update();
        break;

    case 'delete_insumo':
        requireAuth();
        $insumoController = new InsumoController();
        $insumoController->delete();
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php?page=login");
        exit;

    case 'cadastro_proprietario':
        
        loadView('create_prop');
        break;


    case 'store_proprietario':
        $propController = new ProprietarioController();
        $propController->save();
        break;

    case 'configuracoes':
        requireAuth();
        (new ProprietarioController())->configuracoes();
        break;

    case 'update_perfil':
        requireAuth();
        (new ProprietarioController())->update();
        break;

    case 'store_peao':
        requireAuth(); 
        $peaoController = new PeaoController();
        $peaoController->save();
        break;
    
    case 'edit_peao':
    requireAuth();
    $controller = new PeaoController();
    $controller->edit();
    break;

    case 'update_peao':
        requireAuth();
        $controller = new PeaoController();
        $controller->update();
        break;

    case 'delete_peao':
        requireAuth();
        $controller = new PeaoController();
        $controller->delete();
        break;
    
    case 'propriedades':
        requireAuth();
        (new PropriedadeController())->index();
        break;

    case 'store_propriedade':
        requireAuth();
        (new PropriedadeController())->store();
        break;

    case 'update_propriedade':
        requireAuth();
        (new PropriedadeController())->update();
        break;

    case 'delete_propriedade':
        requireAuth();
        (new PropriedadeController())->delete();
        break;

    default:
        http_response_code(404);
        require_once "../app/views/notfound.php";
        break;
}