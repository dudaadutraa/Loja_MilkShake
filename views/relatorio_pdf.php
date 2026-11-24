<?php
/**
 * Relatório para salvar em PDF (RelatorioController::exportarPdf).
 * A página abre a janela de impressão do navegador; lá a pessoa escolhe "Salvar como PDF".
 * Variáveis: $de, $ate, $vendas, $compras, $porDia, $topProdutos, $porCategoria, $porVendedor
 */
$saldo = (float) $vendas['total'] - (float) $compras['total'];

$tabela = function (string $titulo, array $linhas, array $colunas): void {
    echo '<h2>' . h($titulo) . '</h2>';
    if (!$linhas) {
        echo '<p class="vazio">Sem dados no período.</p>';
        return;
    }
    echo '<table><tr>';
    foreach ($colunas as $nome => $_) {
        echo '<th>' . h($nome) . '</th>';
    }
    echo '</tr>';
    foreach ($linhas as $l) {
        echo '<tr>';
        foreach ($colunas as $valor) {
            echo '<td>' . $valor($l) . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Relatório <?= h(data_br($de)) ?> a <?= h(data_br($ate)) ?> - Shake Nature</title>
<style>
@page { margin: 28px 32px; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #4A2E1C; max-width: 800px; margin: 24px auto; padding: 0 16px; }
.barra { text-align: center; margin: 24px 0; }
.barra button, .barra a { font: bold 14px Arial, sans-serif; padding: 10px 18px; border-radius: 999px; border: 0; cursor: pointer; text-decoration: none; background: #E8670C; color: #fff; margin: 0 4px; }
.barra a { background: #fff; color: #4A2E1C; border: 1px solid #ccc; }
@media print { .barra { display: none; } body { margin: 0 auto; } }
.topo { border-bottom: 3px solid #E8670C; padding-bottom: 8px; margin-bottom: 14px; }
.topo b { font-size: 20px; color: #E8670C; }
.topo span { float: right; color: #8A7262; margin-top: 6px; }
.resumo { width: 100%; margin-bottom: 8px; }
.resumo td { width: 25%; background: #FFF4D6; padding: 8px; border: 2px solid #fff; }
.resumo small { display: block; color: #8A7262; font-size: 9px; text-transform: uppercase; }
.resumo strong { font-size: 14px; }
h2 { font-size: 13px; color: #E8670C; margin: 16px 0 6px; }
table { width: 100%; border-collapse: collapse; }
th { background: #4A2E1C; color: #fff; text-align: left; padding: 5px 6px; font-size: 10px; }
td { padding: 4px 6px; border-bottom: 1px solid #F2E3C6; }
.vazio { color: #8A7262; }
.rodape { margin-top: 20px; font-size: 9px; color: #8A7262; text-align: center; }
</style>
</head>
<body>
<div class="topo">
    <b>Shake Nature</b> &nbsp;Relatório de vendas e compras
    <span><?= h(data_br($de)) ?> a <?= h(data_br($ate)) ?></span>
</div>

<table class="resumo">
    <tr>
        <td><small>Vendas</small><strong><?= (int) $vendas['qtd'] ?></strong></td>
        <td><small>Faturamento</small><strong><?= moeda($vendas['total']) ?></strong></td>
        <td><small>Compras (entradas)</small><strong><?= moeda($compras['total']) ?></strong></td>
        <td><small>Vendas − compras</small><strong><?= moeda($saldo) ?></strong></td>
    </tr>
</table>

<?php
$tabela('Mais vendidos', $topProdutos, [
    'Produto' => fn ($l) => h($l['produto'] . ' - ' . $l['tamanho']),
    'Qtd.'    => fn ($l) => (int) $l['qtd'],
    'Total'   => fn ($l) => moeda($l['total']),
]);
$tabela('Por categoria', $porCategoria, [
    'Categoria' => fn ($l) => h(trim($l['categoria'])),
    'Qtd.'      => fn ($l) => (int) $l['qtd'],
    'Total'     => fn ($l) => moeda($l['total']),
]);
$tabela('Vendas por dia', $porDia, [
    'Dia'    => fn ($l) => h(data_br($l['data'])),
    'Vendas' => fn ($l) => (int) $l['qtd'],
    'Total'  => fn ($l) => moeda($l['total']),
]);
$tabela('Por vendedor', $porVendedor, [
    'Vendedor' => fn ($l) => h($l['vendedor']),
    'Vendas'   => fn ($l) => (int) $l['qtd'],
    'Total'    => fn ($l) => moeda($l['total']),
]);
?>

<p class="rodape">Gerado em <?= date('d/m/Y H:i') ?> pelo sistema Shake Nature.</p>

<div class="barra">
    <button type="button" onclick="window.print()">Salvar como PDF / Imprimir</button>
    <a href="<?= h(sn_url(['controller' => 'relatorio', 'de' => $de, 'ate' => $ate])) ?>">Voltar</a>
</div>
<script>
// abre a janela de impressão sozinha; em "Destino" escolha "Salvar como PDF"
window.addEventListener('load', function () { window.print(); });
</script>
</body>
</html>
