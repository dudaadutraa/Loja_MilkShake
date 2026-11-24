</main>
<footer class="rodape">
  <div class="wrap">
    <div>
      <b>Shake Nature</b>
      <p>Milkshakes e doces. Faça o pedido pelo site, pague como preferir e retire na loja ou receba em casa.</p>
    </div>
    <div>
      <b>Minha conta</b>
      <?php if ($cliente): ?>
        <a href="<?= h(sn_url(['controller' => 'conta'])) ?>">Meus pedidos</a>
        <a href="<?= h(sn_url(['controller' => 'conta', 'action' => 'dados'])) ?>">Meus dados</a>
      <?php else: ?>
        <a href="<?= h(sn_url(['controller' => 'conta', 'action' => 'entrar'])) ?>">Entrar</a>
        <a href="<?= h(sn_url(['controller' => 'conta', 'action' => 'cadastro'])) ?>">Criar conta</a>
      <?php endif; ?>
      <a href="<?= h(sn_url(['controller' => 'carrinho'])) ?>">Carrinho</a>
    </div>
    <div>
      <b>Formas de pagamento</b>
      <p>Pix<br>Cartão de crédito<br>Dinheiro na retirada ou entrega</p>
    </div>
    <p class="fim">© <?= date('Y') ?> Shake Nature</p>
  </div>
</footer>
</body>
</html>
