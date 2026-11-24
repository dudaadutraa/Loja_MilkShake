<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Entradas de mercadoria', 'entrada');
?>
<div class="acoes" style="margin:0 0 20px">
    <a class="btn btn-primario" href="<?= h(sn_url(['controller' => 'entrada', 'action' => 'nova'])) ?>">+ Nova entrada</a>
</div>

<div class="card">
    <?php if (!$entradas): ?>
        <div class="vazio">Nenhuma entrada registrada.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Data</th><th>Fornecedor</th><th>Nota fiscal</th><th>Itens</th><th>Valor</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($entradas as $e): ?>
            <tr>
                <td><strong>#<?= (int) $e['id'] ?></strong></td>
                <td><?= h(data_br($e['data'])) ?></td>
                <td><?= h($e['fornecedor']) ?></td>
                <td><?= h($e['nf_numero'] ?? '-') ?></td>
                <td><?= (int) $e['itens'] ?></td>
                <td><strong><?= moeda($e['valor_total']) ?></strong></td>
                <td>
                    <a class="btn" href="<?= h(sn_url(['controller' => 'entrada', 'action' => 'ver', 'id' => $e['id']])) ?>">Ver</a>
                    <?php if (($_SESSION['perfil'] ?? '') === 'admin'): ?>
                    <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'entrada', 'action' => 'excluir'])) ?>" onsubmit="return confirm('Excluir a entrada #<?= (int) $e['id'] ?>? Os itens dela saem do estoque.')">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
                        <button class="btn btn-perigo" type="submit">Excluir</button>
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
