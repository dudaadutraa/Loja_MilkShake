<?php
$tituloPagina = 'Carrinho';
require __DIR__ . '/_topo.php';
require_once __DIR__ . '/_icone.php';
?>
<div class="wrap">
  <h1 class="titulo">Carrinho</h1>

  <?php if (!$itens): ?>
    <div class="caixa vazio">
      <p><strong>Seu carrinho está vazio.</strong></p>
      <p>Escolha um produto no cardápio para começar.</p>
      <a class="btn" href="<?= h(sn_url(['controller' => 'loja'])) ?>">Ver cardápio</a>
    </div>
  <?php else: ?>
  <div class="duas-col">
    <div class="caixa">
      <form id="formQtd" method="post" action="<?= h(sn_url(['controller' => 'carrinho', 'action' => 'atualizar'])) ?>">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      </form>
      <table class="tabela-itens">
        <thead><tr><th>Produto</th><th class="so-pc">Preço</th><th>Qtd.</th><th class="direita">Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($itens as $i): ?>
          <tr>
            <td>
              <div class="prod">
                <?= loja_foto(['id' => $i['produto_id'], 'nome' => $i['produto'], 'descricao' => $i['descricao'], 'imagem' => $i['imagem']]) ?>
                <div>
                  <a href="<?= h(sn_url(['controller' => 'loja', 'action' => 'produto', 'id' => $i['produto_id']])) ?>"><?= h($i['produto']) ?></a>
                  <div class="muted" style="font-size:.88rem">Tamanho <?= h($i['tamanho']) ?></div>
                  <form method="post" action="<?= h(sn_url(['controller' => 'carrinho', 'action' => 'remover'])) ?>">
                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                    <input type="hidden" name="variacao_id" value="<?= (int) $i['id'] ?>">
                    <button class="link" type="submit" style="font-size:.82rem">Remover</button>
                  </form>
                </div>
              </div>
            </td>
            <td class="so-pc"><?= moeda($i['preco']) ?></td>
            <td><input form="formQtd" type="number" name="qtd[<?= (int) $i['id'] ?>]" value="<?= (int) $i['quantidade'] ?>" min="0" max="<?= max(1, (int) $i['estoque']) ?>" aria-label="Quantidade de <?= h($i['produto'] . ' ' . $i['tamanho']) ?>" onchange="this.form.submit()"></td>
            <td class="direita preco"><?= moeda($i['subtotal']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <noscript><button class="btn sec" form="formQtd" type="submit" style="margin-top:12px">Atualizar quantidades</button></noscript>
    </div>

    <aside class="caixa resumo">
      <h2>Resumo</h2>
      <dl>
        <div><dt>Itens</dt><dd><?= (int) array_sum(array_column($itens, 'quantidade')) ?></dd></div>
        <div><dt>Entrega</dt><dd class="muted">a combinar</dd></div>
        <div class="total"><dt style="color:inherit">Total</dt><dd class="preco"><?= moeda($total) ?></dd></div>
      </dl>
      <a class="btn cheio" href="<?= h(sn_url(['controller' => 'pedido'])) ?>">Finalizar pedido</a>
      <a class="btn sec cheio" href="<?= h(sn_url(['controller' => 'loja'])) ?>">Continuar comprando</a>
      <?php if (!$cliente): ?>
        <p class="muted" style="font-size:.82rem;margin-top:12px">Para finalizar você entra na sua conta ou cria uma em menos de um minuto.</p>
      <?php endif; ?>
    </aside>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
