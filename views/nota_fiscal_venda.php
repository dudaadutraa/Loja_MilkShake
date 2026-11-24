<?php
/**
 * Nota fiscal da venda para imprimir (VendaController::nota).
 * Simulação para fins didáticos: não tem validade fiscal.
 * Variáveis: $venda, $nota, $itens
 */
$doc = preg_replace('/\D/', '', (string) ($venda['cpf_cnpj'] ?? ''));
if (strlen($doc) === 11) {
    $doc = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $doc);
}
$qtdItens = array_sum(array_column($itens, 'quantidade'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nota fiscal nº <?= h($nota['numero']) ?> - Shake Nature</title>
<link rel="icon" type="image/png" href="<?= h(sn_asset('assets/img/logo.png')) ?>">
<style>
body{margin:0;background:#EEE6DA;font:14px/1.45 "Courier New",Courier,monospace;color:#222}
.cupom{width:380px;max-width:100%;margin:24px auto;background:#fff;padding:22px 20px;box-shadow:0 6px 20px rgba(0,0,0,.12)}
.centro{text-align:center}
h1{font-size:16px;margin:6px 0 2px}
hr{border:0;border-top:1px dashed #999;margin:12px 0}
table{width:100%;border-collapse:collapse}
td{padding:2px 0;vertical-align:top}
.dir{text-align:right;white-space:nowrap}
.total td{font-weight:bold;font-size:16px;padding-top:6px}
.aviso{font-size:11px;color:#666;text-align:center}
.barra{width:380px;max-width:100%;margin:0 auto 24px;display:flex;gap:8px;justify-content:center}
.barra button,.barra a{font:600 14px Arial,sans-serif;padding:10px 18px;border-radius:999px;border:0;cursor:pointer;text-decoration:none;background:#E8670C;color:#fff}
.barra a{background:#fff;color:#4A2E1C;border:1px solid #ccc}
@media print{body{background:#fff}.cupom{box-shadow:none;margin:0 auto}.barra{display:none}}
</style>
</head>
<body>
<div class="cupom">
    <div class="centro">
        <img src="<?= h(sn_asset('assets/img/logo.png')) ?>" alt="" width="56" height="56" style="border-radius:50%">
        <h1>SHAKE NATURE</h1>
        <div>Milkshakes e doces</div>
    </div>
    <hr>
    <div class="centro">
        <strong>DOCUMENTO AUXILIAR DA NOTA FISCAL<br>DE CONSUMIDOR ELETRÔNICA</strong>
    </div>
    <hr>
    <table>
        <tr><td>Nº <?= h($nota['numero']) ?></td><td class="dir">Série <?= h($nota['serie']) ?> · <?= h($nota['modelo']) ?></td></tr>
        <tr><td>Emissão</td><td class="dir"><?= h(data_br($nota['data_emissao'])) ?></td></tr>
        <tr><td>Venda</td><td class="dir">#<?= (int) $venda['id'] ?> de <?= h(data_br($venda['data'])) ?></td></tr>
    </table>
    <hr>
    <div>CONSUMIDOR: <?= h($venda['cliente']) ?></div>
    <?php if ($doc !== ''): ?><div>CPF/CNPJ: <?= h($doc) ?></div><?php else: ?><div>CONSUMIDOR NÃO IDENTIFICADO</div><?php endif; ?>
    <hr>
    <table>
        <?php foreach ($itens as $i): ?>
            <tr><td colspan="2"><?= h($i['produto'] . ' ' . $i['tamanho']) ?></td></tr>
            <tr>
                <td><?= (int) $i['quantidade'] ?> x <?= moeda($i['preco_unitario']) ?></td>
                <td class="dir"><?= moeda($i['quantidade'] * $i['preco_unitario']) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr><td colspan="2"><hr></td></tr>
        <tr><td>Qtd. de itens</td><td class="dir"><?= (int) $qtdItens ?></td></tr>
        <tr class="total"><td>TOTAL</td><td class="dir"><?= moeda($nota['valor_total']) ?></td></tr>
        <?php if (!empty($venda['pagamento'])): ?>
            <tr><td>Pagamento</td><td class="dir"><?= h(['pix' => 'Pix', 'cartao' => 'Cartão', 'dinheiro' => 'Dinheiro'][$venda['pagamento']] ?? $venda['pagamento']) ?></td></tr>
        <?php endif; ?>
    </table>
    <hr>
    <p class="aviso">Atendido por <?= h($venda['vendedor']) ?>.<br>
    Simulação para fins didáticos (projeto escolar). Sem valor fiscal.</p>
</div>
<div class="barra">
    <button type="button" onclick="window.print()">Imprimir</button>
    <a href="<?= h(sn_url(['controller' => 'venda', 'action' => 'ver', 'id' => $venda['id']])) ?>">Voltar para a venda</a>
</div>
</body>
</html>
