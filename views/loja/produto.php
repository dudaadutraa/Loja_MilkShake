<?php
$tituloPagina = $produto['nome'];
require __DIR__ . '/_topo.php';
require_once __DIR__ . '/_icone.php';

$temEstoque = false;
foreach ($variacoes as $v) {
    if ((int) $v['estoque'] > 0) { $temEstoque = true; break; }
}
?>
<div class="wrap">
  <p class="trilha">
    <a href="<?= h(sn_url(['controller' => 'loja'])) ?>">Cardápio</a> ›
    <a href="<?= h(sn_url(['controller' => 'loja', 'categoria' => $produto['categoria_id']])) ?>"><?= h(trim($produto['categoria'])) ?></a> ›
    <?= h($produto['nome']) ?>
  </p>

  <section class="detalhe">
    <?= loja_foto($produto, !$temEstoque) ?>

    <div>
      <span class="etiqueta"><?= h(trim($produto['categoria'])) ?></span>
      <h1><?= h($produto['nome']) ?></h1>
      <?php if (trim((string) $produto['descricao']) !== ''): ?>
        <p class="desc"><?= nl2br(h($produto['descricao'])) ?></p>
      <?php endif; ?>

      <?php if (!$variacoes): ?>
        <p class="aviso erro">Este produto ainda não está disponível para pedido.</p>
      <?php else: ?>
      <form method="post" action="<?= h(sn_url(['controller' => 'carrinho', 'action' => 'adicionar'])) ?>">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
        <input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>">

        <fieldset class="fora">
          <legend style="font-weight:700;margin-bottom:8px">Tamanho</legend>
          <div class="opcoes">
            <?php $marcado = false; foreach ($variacoes as $v): $ok = (int) $v['estoque'] > 0; ?>
              <label class="opcao">
                <input type="radio" name="variacao_id" value="<?= (int) $v['id'] ?>" <?= $ok ? '' : 'disabled' ?> <?= $ok && !$marcado ? 'checked' : '' ?> required>
                <span class="txt">
                  <b><?= h($v['tamanho']) ?></b>
                  <small><?= $ok ? ((int) $v['estoque'] <= 5 ? 'Restam ' . (int) $v['estoque'] : 'Disponível') : 'Esgotado' ?></small>
                </span>
                <span class="preco"><?= moeda($v['preco']) ?></span>
              </label>
            <?php if ($ok) { $marcado = true; } endforeach; ?>
          </div>
        </fieldset>

        <div class="compra">
          <div class="qtd">
            <button type="button" data-d="-1" aria-label="Diminuir">−</button>
            <input type="number" name="quantidade" value="1" min="1" max="99" aria-label="Quantidade">
            <button type="button" data-d="1" aria-label="Aumentar">+</button>
          </div>
          <button class="btn" type="submit" <?= $temEstoque ? '' : 'disabled' ?>><?= $temEstoque ? 'Adicionar ao carrinho' : 'Esgotado' ?></button>
        </div>
        <p class="nota">Você escolhe retirada ou entrega e a forma de pagamento ao finalizar.</p>
      </form>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($outros): ?>
  <h2 class="titulo" style="font-size:1.25rem;margin-top:44px">Da mesma categoria</h2>
  <div class="grade">
    <?php foreach ($outros as $o): ?>
      <a class="card" href="<?= h(sn_url(['controller' => 'loja', 'action' => 'produto', 'id' => $o['id']])) ?>">
        <?= loja_foto($o) ?>
        <div class="card-corpo">
          <span class="card-nome"><?= h($o['nome']) ?></span>
          <span class="card-preco preco"><small>a partir de</small><?= moeda($o['preco_min']) ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.qtd button').forEach(function (b) {
  b.addEventListener('click', function () {
    var i = b.parentNode.querySelector('input');
    i.value = Math.max(1, Math.min(99, (parseInt(i.value, 10) || 1) + parseInt(b.dataset.d, 10)));
  });
});
</script>
<?php require __DIR__ . '/_rodape.php'; ?>
