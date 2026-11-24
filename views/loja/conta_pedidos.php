<?php
$tituloPagina = 'Meus pedidos';
require __DIR__ . '/_topo.php';
require_once __DIR__ . '/_status.php';
$abaConta = 'pedidos';
?>
<div class="wrap">
  <?php require __DIR__ . '/_menu_conta.php'; ?>

  <?php if (!$pedidos): ?>
    <div class="caixa vazio">
      <p><strong>Você ainda não fez pedidos.</strong></p>
      <p>Quando fizer, eles aparecem aqui com a situação de cada um.</p>
      <a class="btn" href="<?= h(sn_url(['controller' => 'loja'])) ?>">Ver cardápio</a>
    </div>
  <?php else: ?>
  <table class="lista-pedidos">
    <thead><tr><th>Pedido</th><th>Data</th><th class="so-pc">Itens</th><th class="so-pc">Pagamento</th><th>Situação</th><th>Total</th></tr></thead>
    <tbody>
    <?php foreach ($pedidos as $p): ?>
      <tr>
        <td><a href="<?= h(sn_url(['controller' => 'conta', 'action' => 'pedido', 'id' => $p['id']])) ?>"><strong>nº <?= (int) $p['id'] ?></strong></a></td>
        <td><?= h(data_br($p['data'])) ?></td>
        <td class="so-pc"><?= (int) $p['itens'] ?></td>
        <td class="so-pc"><?= loja_status_pagamento($p['pagamento'], $p['pagamento_status']) ?></td>
        <td><?= loja_status_pedido($p) ?></td>
        <td class="preco"><?= moeda($p['valor_total']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
