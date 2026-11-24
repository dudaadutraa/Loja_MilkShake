<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Estoque', 'estoque');
$baixos = count(array_filter($itens, function ($i) { return (int) $i["ativo"] === 1 && (int) $i["quantidade"] <= (int) $i["minimo"]; }));
?>
<div class="numeros">
    <div class="numero"><span>Tamanhos cadastrados</span><strong><?= count($itens) ?></strong></div>
    <div class="numero"><span>Unidades em estoque</span><strong><?= array_sum(array_column($itens, 'quantidade')) ?></strong></div>
    <div class="numero"><span>Abaixo do mínimo</span><strong><?= $baixos ?></strong></div>
</div>

<div class="card">
    <div class="acoes" style="margin:0 0 16px">
        <a class="btn <?= $filtro === '' ? 'btn-primario' : '' ?>" href="<?= h(sn_url(['controller' => 'estoque'])) ?>">Todos</a>
        <a class="btn <?= $filtro === 'baixo' ? 'btn-primario' : '' ?>" href="<?= h(sn_url(['controller' => 'estoque', 'filtro' => 'baixo'])) ?>">Só estoque baixo</a>
        <a class="btn btn-ok" href="<?= h(sn_url(['controller' => 'entrada', 'action' => 'nova'])) ?>">+ Registrar entrada</a>
    </div>

    <?php if (!$itens): ?>
        <div class="vazio">Nada para mostrar. Cadastre os tamanhos em <a href="<?= h(sn_url(['controller' => 'variacao'])) ?>">Variações</a>.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Produto</th><th>Tamanho</th><th>Preço</th><th>Quantidade</th><th>Mínimo</th></tr></thead>
        <tbody>
        <?php foreach ($itens as $i): ?>
            <tr>
                <td><strong><?= h($i['produto']) ?></strong><?= $i['ativo'] ? '' : ' <span class="tag off">inativo</span>' ?><div class="muted"><?= h($i['sku']) ?></div></td>
                <td><?= h($i['tamanho']) ?></td>
                <td><?= moeda($i['preco']) ?></td>
                <td><span class="tag <?= (int) $i['quantidade'] <= (int) $i['minimo'] ? 'alerta' : 'ok' ?>"><?= (int) $i['quantidade'] ?></span></td>
                <td>
                    <?php if (sn_eh_admin()): ?>
                    <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'estoque', 'action' => 'minimo'])) ?>" style="display:flex;gap:6px">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
                        <input class="input" type="number" name="minimo" min="0" value="<?= (int) $i['minimo'] ?>" style="width:80px;padding:6px 8px">
                        <button class="btn" type="submit">OK</button>
                    </form>
                    <?php else: ?>
                        <?= (int) $i['minimo'] ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Últimos movimentos</h2>
    <?php if (!$movimentos): ?>
        <div class="vazio">Nenhum movimento ainda.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Data</th><th>Produto</th><th>Tipo</th><th>Qtd.</th><th>Origem</th></tr></thead>
        <tbody>
        <?php foreach ($movimentos as $m): ?>
            <tr>
                <td><?= h(data_br($m['data'])) ?></td>
                <td><?= h($m['produto'] . ' - ' . $m['tamanho']) ?></td>
                <td><span class="tag <?= $m['tipo'] === 'entrada' ? 'ok' : 'alerta' ?>"><?= $m['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?></span></td>
                <td><?= (int) $m['quantidade'] ?></td>
                <td><?php
                    // devolução de venda cancelada = entrada com origem "venda"; exclusão de entrada = saída com origem "entrada"
                    if ($m['origem'] === 'venda') {
                        echo $m['tipo'] === 'entrada' ? 'Cancelamento da venda' : 'Venda';
                    } else {
                        echo $m['tipo'] === 'saida' ? 'Exclusão da entrada' : 'Entrada';
                    }
                ?> #<?= (int) $m['origem_id'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php layout_rodape(); ?>
