<?php
require_once __DIR__ . '/../../controllers/cotacoesController.php';
/*
 * layouts/ticker.php
 *
 * Carousel infinito de cotações agrícolas.
 * Baseado na técnica CSS pura: duplica os itens no PHP e anima -50%,
 * que equivale exatamente à largura de uma cópia — loop seamless garantido.
 *
 * Para conectar ao banco, substitua o array $cotacoes por uma query:
 *   $stmt = $db->query("SELECT cultura, estado, preco, tendencia FROM cotacoes ORDER BY cultura");
 *   $cotacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
 */

$cotacoesController = new CotacoesController();
$resultado      = $cotacoesController->buscarParaTicker();
$cotacoes       = $resultado['cotacoes'];
$dataCotacao    = $resultado['dataCotacao'];

/*
 * Função auxiliar para renderizar um único item da lista.
 * Separada para evitar repetir HTML inline no loop duplo abaixo.
 */
function renderTickerItem(array $c): string {
    $seta   = $c['tendencia'] === 'up' ? '▲' : '▼';
    $classe = htmlspecialchars($c['tendencia']);
    $texto  = htmlspecialchars($c['cultura']) . '-' . htmlspecialchars($c['estado']) . ' (' . htmlspecialchars($c['unidade']) . ')';
    $preco  = htmlspecialchars($c['preco']);
    $unidade = htmlspecialchars($c['unidade']);

    return "
        <li class=\"ticker-item\">
            <span class=\"ticker-cultura\">{$texto}</span>
            <span class=\"ticker-preco ticker-{$classe}\">{$seta} R\$ {$preco}</span>
        </li>
        <li class=\"ticker-sep\" aria-hidden=\"true\">|</li>";
}
?>

<div class="ticker-wrap">

    <div class="ticker-label">
        <a href="#" class="text-decoration-none text-white">COTAÇÕES  <?= $dataCotacao ?></a>
    </div>

    <div class="ticker-viewport">
        <ul class="ticker-list">
            <?php
            /*
             * Renderiza DUAS cópias dos itens.
             *
             * A animação desloca -50% do width total do <ul>.
             * Como o <ul> tem width:max-content (soma das duas cópias),
             * -50% = exatamente a largura de UMA cópia.
             *
             * Quando a primeira cópia sai pela esquerda, o ponto de chegada
             * (-50%) é visualmente idêntico ao ponto de partida (0%),
             * então o loop recomeça sem salto — seamless perfeito.
             */
            foreach ($cotacoes as $cotacao) echo renderTickerItem($cotacao);
            foreach ($cotacoes as $cotacao) echo renderTickerItem($cotacao);
            ?>
        </ul>
    </div>

</div>