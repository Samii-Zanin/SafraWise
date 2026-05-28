<?php
session_start();
require_once "../app/controllers/AuthController.php";
require_once "../app/controllers/ProprietarioController.php";
require_once "../app/controllers/PeaoController.php";
require_once "../app/controllers/InsumoController.php";
require_once "../app/controllers/PropriedadeController.php";
require_once "../app/controllers/CulturaController.php";
require_once "../app/controllers/ProdutoController.php";
require_once "../app/controllers/estoqueInsumosController.php";
require_once "../app/controllers/siloController.php";
require_once "../app/controllers/TalhaoController.php";
require_once "../app/controllers/DashboardController.php";
require_once "../app/controllers/SafraController.php";
require_once "../app/controllers/RelatoriosController.php";
require_once "../app/controllers/ClimaController.php";

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
        (new DashboardController())->index();
        break;

    case 'safras':
        requireAuth();
        require_once '../app/controllers/SafraController.php';
        require_once '../app/controllers/CulturaController.php';
        require_once '../app/controllers/TalhaoController.php';

        $safras        = (new SafraController())->getAll();
        $culturasModal = (new CulturaController())->getDistintas();
        $talhoesModal  = (new TalhaoController())->getVazios();

        require '../app/views/safras.php';
        break;
        
    case 'safra_detalhe':
        requireAuth();
        require_once '../app/controllers/SafraController.php';
        require_once '../app/controllers/OperacoesFinanceirasController.php';
        require_once '../app/controllers/ProdutoController.php';
        require_once '../app/controllers/PropriedadeController.php';
        require_once '../app/controllers/InsumoController.php';
        require_once '../app/controllers/TalhaoController.php';
        require_once '../app/controllers/PeaoController.php'; 

        $insumosAgricolas = (new InsumoController())->getAllNotCereal();
        $talhoes          = (new TalhaoController())->getAll();
        $peoes            = (new PeaoController())->getAll();

        $id    = (int) ($_GET['id'] ?? 0);
        $safra = (new SafraController())->getById($id);

        if (!$safra) {
            header('Location: index.php?page=safras');
            exit;
        }

        $operacoes    = (new OperacoesFinanceirasController())->getBySafra($id);

        $totalGastos  = array_sum(array_column(
            array_filter($operacoes, fn($o) =>
                $o['tipo_operacao'] === 'COMPRA DE INSUMOS' ||
                $o['operacao']      === 'Operação Agrícola'
            ),
            'valor_operacao'
        ));
        $totalInsumos = array_sum(array_column(
            array_filter($operacoes, fn($o) => $o['tipo_operacao'] === 'COMPRA DE INSUMOS'),
            'quantidade'
        ));
        $totalReceita = array_sum(array_column(
            array_filter($operacoes, fn($o) => $o['tipo_operacao'] === 'VENDA DE CEREAIS'),
            'valor_operacao'
        ));

        $produtos     = (new ProdutoController())->getAllnotCereal();              
        $cereais      = (new ProdutoController())->getAllByTipo('CEREAL');
        $propriedades = (new PropriedadeController())->getAll();          

        require '../app/views/safra_detalhe.php';
        break;

    case 'encerrar_safra':
        requireAuth();
        require_once '../app/controllers/SafraController.php';
        (new SafraController())->encerrar();
        break;

    case 'store_op_agricola':
        requireAuth();
        require_once '../app/controllers/OperacoesAgricolasController.php';
        (new OperacoesAgricolasController())->save();
        break;

    case 'store_safra':
        requireAuth();
        require_once '../app/controllers/SafraController.php';
        (new SafraController())->store();
        break;

    case 'equipe':
        requireAuth(); 
        $peaoController = new PeaoController();
        $peaoController->index(); 
        break;
    
    case 'talhoes':
        requireAuth();
        (new TalhaoController())->index();
        break;

    case 'store_talhao':
        requireAuth();
        (new TalhaoController())->store();
        break;

    case 'update_talhao':
        requireAuth();
        (new TalhaoController())->update();
        break;

    case 'delete_talhao':
        requireAuth();
        (new TalhaoController())->delete();
        break;

    case 'detalhes_propriedade': 
        requireAuth(); 
        (new PropriedadeController())->detalhes(); 
        break;
        
    case 'produtos_culturas':
        requireAuth();
        $culturas = (new CulturaController())->getAll();
        $produtos = (new ProdutoController())->getAll();
        require_once "../app/views/produtos_culturas.php"; // ← no escopo global, vê as variáveis
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
    case 'store_cultura':
        requireAuth();
        $culturaController = new CulturaController();
        $culturaController->save();
        break;
    case 'update_cultura':
        requireAuth();
        (new CulturaController())->update();
        break;
    case 'delete_cultura':
        requireAuth();
        (new CulturaController())->delete();
        break;
    case 'store_produto':
        requireAuth();
        $produtoController = new ProdutoController();
        $produtoController->save();
        break;
    case 'update_produto':
        requireAuth();
        (new ProdutoController())->update();
        break;
    case 'delete_produto':
        requireAuth();
        (new ProdutoController())->delete();
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

    case 'estoques':
        requireAuth();
        require_once '../app/controllers/EstoqueInsumosController.php';
        require_once '../app/controllers/SiloController.php';
        require_once '../app/controllers/ProdutoController.php';
        require_once '../app/controllers/SafraController.php';
        require_once '../app/controllers/PropriedadeController.php';
        require_once '../app/controllers/InsumoController.php';
        require_once '../app/controllers/OperacoesFinanceirasController.php';
        require_once '../app/controllers/PeaoController.php';

        $estoqueInsumos       = (new EstoqueInsumosController())->getAll();
        $estoquesSilos        = (new SiloController())->getAll();
        $produtos             = (new ProdutoController())->getAllNotCereal();
        $cereais              = (new ProdutoController())->getAllByTipo('CEREAL');
        $safras               = (new SafraController())->getAtivas();
        $propriedades         = (new PropriedadeController())->getAll();
        $insumos              = (new InsumoController())->getAllComProduto();
        $insumosAgricolas     = (new InsumoController())->getAllNotCereal(); // ← faltava
        $movimentacoesInsumos = (new OperacoesFinanceirasController())->getMovimentacoesEstoqueInsumos();
        $movimentacoesSilos   = (new OperacoesFinanceirasController())->getMovimentacoesSilos();
        $peoes                = (new PeaoController())->getAll();
        $todosSilos           = (new SiloController())->getAllSilos();

        require '../app/views/estoques.php';
        break;

    case 'store_colheita':
        requireAuth();
        require_once '../app/controllers/OperacoesAgricolasController.php';
        (new OperacoesAgricolasController())->storeColheita();
        break;

    case 'store_compra_insumo':
        require_once '../app/controllers/OperacoesFinanceirasController.php';
        (new OperacoesFinanceirasController())->storeCompraInsumo();
        break;
    
    case 'store_entrada_insumo':
        require_once '../app/controllers/EstoqueInsumosController.php';
        (new EstoqueInsumosController())->storeEntrada();
        break;

    case 'venda_cereal':
        require_once '../app/controllers/OperacoesFinanceirasController.php';
        (new OperacoesFinanceirasController())->VendaCereal();
        break;
    case 'saida_simples_cereal':
        require_once '../app/controllers/OperacoesFinanceirasController.php';
        (new OperacoesFinanceirasController())->registrarSaidaSimplesCereal();
        break;

    case 'entrada_silo_cereal':
        require_once '../app/controllers/OperacoesAgricolasController.php';
        (new OperacoesAgricolasController())->save();
        break;


    case 'silos':
        require_once '../app/controllers/siloController.php';
        (new SiloController())->index();
        break;

    case 'store_silo':
        require_once '../app/controllers/siloController.php';
        (new SiloController())->store();
        break;

    case 'update_silo':
        require_once '../app/controllers/siloController.php';
        (new SiloController())->update();
        break;

    case 'delete_silo':
        require_once '../app/controllers/siloController.php';
        (new SiloController())->delete();
        break;

    case 'store_proprietario':
        $propController = new ProprietarioController();
        $propController->save();
        break;

    case 'relatorios':
        requireAuth();
        (new RelatoriosController())->index();
        break;

    case 'export_relatorio':
        requireAuth();
        (new RelatoriosController())->export();
        break;

    case 'clima':
        requireAuth();
        (new ClimaController())->index();
        break;

    case 'weather_proxy':
        requireAuth();
        (new ClimaController())->weather();
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