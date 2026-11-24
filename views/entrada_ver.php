<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Entrada #' . (int) $entrada['id'], 'entrada');
?>
<p><a href="<?= h(sn_url(['controller' => 'entrada'])) ?>">&larr; Voltar para entradas</a></p>

<div class="numeros">
    <div class="numero"><span>Data</span><strong><?= h(data_br($entrada['data'])) ?></strong></div>
    <div class="numero"><span>Fornecedor</span><strong><?= h($entrada['fornecedor']) ?></strong></div>
    <div class="numero"><span>Nota fiscal</span><strong><?= h($entrada['nf_numero'] ? $entrada['nf_numero'] . ' / ' . $entrada['nf_serie'] : '-') ?></strong></div>
    <div class="numero"><span>Total</span><strong><?= moeda($entrada['valor_total']) ?></strong></div>
</div>

<div class="card">
    <h2>Itens</h2>
    <table>
        <thead><tr><th>Produto</th><th>Variação</th><th>Qtd.</th><th>Custo unit.</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($itens as $i): ?>
            <tr>
                <td><?= h($i['produto']) ?></td>
                <td><?= h($i['tamanho']) ?></td>
                <td><?= (int) $i['quantidade'] ?></td>
                <td><?= moeda($i['custo_unitario']) ?></td>
                <td><strong><?= moeda($i['quantidade'] * $i['custo_unitario']) ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="acoes">
        <?php if (($_SESSION['perfil'] ?? '') === 'admin'): ?>
                    <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'entrada', 'action' => 'excluir'])) ?>" onsubmit="return confirm('Excluir a entrada #<?= (int) $entrada['id'] ?>? Os itens dela saem do estoque.')">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $entrada['id'] ?>">
                        <button class="btn btn-perigo" type="submit">Excluir</button>
                    </form>
                    <?php endif; ?>
    </div>
</div>
<?php layout_rodape(); ?>
