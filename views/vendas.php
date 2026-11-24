<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Vendas', 'venda');
$ehAdmin = ($_SESSION['perfil'] ?? '') === 'admin';
?>
<div class="card">
    <form method="get" action="<?= h(BASE_URL . 'index.php') ?>" class="acoes" style="margin:0;align-items:end">
        <input type="hidden" name="controller" value="venda">
        <div><label for="de">De</label><input class="input" type="date" id="de" name="de" value="<?= h($de) ?>"></div>
        <div><label for="ate">Até</label><input class="input" type="date" id="ate" name="ate" value="<?= h($ate) ?>"></div>
        <button class="btn" type="submit">Filtrar</button>
        <a class="btn btn-primario" href="<?= h(sn_url(['controller' => 'venda', 'action' => 'nova'])) ?>" style="margin-left:auto">+ Nova venda</a>
    </form>
</div>

<div class="numeros">
    <div class="numero"><span>Vendas no período</span><strong><?= (int) $qtd ?></strong></div>
    <div class="numero"><span>Faturamento</span><strong><?= moeda($total) ?></strong></div>
    <div class="numero"><span>Ticket médio</span><strong><?= moeda($qtd ? $total / $qtd : 0) ?></strong></div>
</div>

<div class="card">
    <?php if (!$vendas): ?>
        <div class="vazio">Nenhuma venda neste período.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Data</th><th>Cliente</th><th>Vendedor</th><th>Itens</th><th>Situação</th><th>Total</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($vendas as $v): ?>
            <tr>
                <td><strong>#<?= (int) $v['id'] ?></strong></td>
                <td><?= h(data_br($v['data'])) ?></td>
                <td><?= h($v['cliente']) ?></td>
                <td><?= h($v['vendedor']) ?></td>
                <td><?= (int) $v['itens'] ?></td>
                <td><?= ['aberta' => '<span class="tag alerta">Pedido do site · em aberto</span>', 'cancelada' => '<span class="tag off">Cancelada</span>'][$v['status']] ?? '<span class="tag ok">Finalizada</span>' ?></td>
                <td><strong<?= $v['status'] === 'cancelada' ? ' style="text-decoration:line-through;opacity:.6"' : '' ?>><?= moeda($v['valor_total']) ?></strong></td>
                <td>
                    <a class="btn" href="<?= h(sn_url(['controller' => 'venda', 'action' => 'ver', 'id' => $v['id']])) ?>">Ver</a>
                    <?php if ($ehAdmin && $v['status'] !== 'cancelada'): ?>
                    <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'venda', 'action' => 'cancelar'])) ?>" onsubmit="return confirm('Cancelar a venda #<?= (int) $v['id'] ?>? Os itens voltam para o estoque.')">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                        <button class="btn btn-perigo" type="submit">Cancelar</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php layout_rodape(); ?>
