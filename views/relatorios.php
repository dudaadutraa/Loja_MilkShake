<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Relatórios', 'relatorio');

function rel_tabela(array $linhas, array $colunas): void
{
    if (!$linhas) {
        echo '<div class="vazio">Sem dados no período.</div>';
        return;
    }
    echo '<table><thead><tr>';
    foreach ($colunas as $titulo => $_) {
        echo '<th>' . h($titulo) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($linhas as $l) {
        echo '<tr>';
        foreach ($colunas as $formato) {
            echo '<td>' . $formato($l) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
}

$lucroBruto = (float) $vendas['total'] - (float) $compras['total'];
?>
<div class="card">
    <form method="get" action="<?= h(BASE_URL . 'index.php') ?>" class="acoes" style="margin:0;align-items:end">
        <input type="hidden" name="controller" value="relatorio">
        <div><label for="de">De</label><input class="input" type="date" id="de" name="de" value="<?= h($de) ?>"></div>
        <div><label for="ate">Até</label><input class="input" type="date" id="ate" name="ate" value="<?= h($ate) ?>"></div>
        <button class="btn btn-primario" type="submit">Gerar</button>
        <button class="btn" type="button" onclick="window.print()">Imprimir</button>
        <a class="btn" href="<?= h(sn_url(['controller' => 'relatorio', 'action' => 'exportarExcel', 'de' => $de, 'ate' => $ate])) ?>">Exportar Excel</a>
        <a class="btn" href="<?= h(sn_url(['controller' => 'relatorio', 'action' => 'exportarPdf', 'de' => $de, 'ate' => $ate])) ?>" target="_blank" rel="noopener">Exportar PDF</a>
    </form>
</div>

<div class="numeros">
    <div class="numero"><span>Vendas</span><strong><?= (int) $vendas['qtd'] ?></strong></div>
    <div class="numero"><span>Faturamento</span><strong><?= moeda($vendas['total']) ?></strong></div>
    <div class="numero"><span>Compras (entradas)</span><strong><?= moeda($compras['total']) ?></strong></div>
    <div class="numero"><span>Vendas − compras</span><strong style="color:<?= $lucroBruto < 0 ? '#B3261E' : '#2E6B2A' ?>"><?= moeda($lucroBruto) ?></strong></div>
</div>

<div class="grade" style="grid-template-columns:1fr 1fr">
    <div class="card">
        <h2>Mais vendidos</h2>
        <?php rel_tabela($topProdutos, [
            'Produto' => function ($l) { return h($l['produto'] . ' - ' . $l['tamanho']); },
            'Qtd.'    => function ($l) { return (int) $l['qtd']; },
            'Total'   => function ($l) { return moeda($l['total']); },
        ]); ?>
    </div>
    <div class="card">
        <h2>Por categoria</h2>
        <?php rel_tabela($porCategoria, [
            'Categoria' => function ($l) { return h(trim($l['categoria'])); },
            'Qtd.'      => function ($l) { return (int) $l['qtd']; },
            'Total'     => function ($l) { return moeda($l['total']); },
        ]); ?>
    </div>
    <div class="card">
        <h2>Vendas por dia</h2>
        <?php rel_tabela($porDia, [
            'Dia'    => function ($l) { return h(data_br($l['data'])); },
            'Vendas' => function ($l) { return (int) $l['qtd']; },
            'Total'  => function ($l) { return moeda($l['total']); },
        ]); ?>
    </div>
    <div class="card">
        <h2>Por vendedor</h2>
        <?php rel_tabela($porVendedor, [
            'Vendedor' => function ($l) { return h($l['vendedor']); },
            'Vendas'   => function ($l) { return (int) $l['qtd']; },
            'Total'    => function ($l) { return moeda($l['total']); },
        ]); ?>
    </div>
</div>
<?php layout_rodape(); ?>
