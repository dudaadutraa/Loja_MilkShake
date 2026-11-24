<?php /** Resumo lateral dos itens no checkout. Usa $itens e $total. */ ?>
<aside class="caixa resumo">
  <h2>Seu pedido</h2>
  <ul>
    <?php foreach ($itens as $i): ?>
      <li><span><?= (int) $i['quantidade'] ?>× <?= h($i['produto']) ?> <span class="muted"><?= h($i['tamanho']) ?></span></span><span class="preco"><?= moeda($i['subtotal']) ?></span></li>
    <?php endforeach; ?>
  </ul>
  <dl>
    <div class="total"><dt style="color:inherit">Total</dt><dd class="preco"><?= moeda($total) ?></dd></div>
  </dl>
  <a class="link" href="<?= h(sn_url(['controller' => 'carrinho'])) ?>" style="font-size:.85rem;display:inline-block;margin-top:10px">Alterar carrinho</a>
</aside>
