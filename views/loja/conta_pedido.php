<?php
$tituloPagina = 'Pedido nº ' . (int) $pedido['id'];
require __DIR__ . '/_topo.php';
require_once __DIR__ . '/_status.php';
require_once __DIR__ . '/_icone.php';
$abaConta = 'pedidos';
?>
<div class="wrap">
  <?php require __DIR__ . '/_menu_conta.php'; ?>
  <p style="margin-bottom:14px"><a href="<?= h(sn_url(['controller' => 'conta'])) ?>">‹ Todos os pedidos</a></p>

  <div class="duas-col">
    <div class="caixa">
      <h2 style="font-size:1.2rem;margin-bottom:4px">Pedido nº <?= (int) $pedido['id'] ?> <?= loja_status_pedido($pedido) ?></h2>
      <p class="muted" style="margin-bottom:12px">Feito em <?= h(data_br($pedido['data'])) ?></p>
      <table class="tabela-itens">
        <thead><tr><th>Produto</th><th>Qtd.</th><th class="direita">Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($itens as $i): ?>
          <tr>
            <td><div class="prod"><?= loja_foto(['id' => $i['produto_id'], 'nome' => $i['produto'], 'descricao' => $i['descricao'], 'imagem' => $i['imagem']]) ?><div><a href="<?= h(sn_url(['controller' => 'loja', 'action' => 'produto', 'id' => $i['produto_id']])) ?>"><?= h($i['produto']) ?></a><div class="muted" style="font-size:.88rem">Tamanho <?= h($i['tamanho']) ?> · <?= moeda($i['preco_unitario']) ?></div></div></div></td>
            <td><?= (int) $i['quantidade'] ?></td>
            <td class="direita preco"><?= moeda($i['quantidade'] * $i['preco_unitario']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <aside class="caixa resumo">
      <h2>Resumo</h2>
      <dl>
        <div><dt>Recebimento</dt><dd><?= ($pedido['entrega'] ?? '') === 'entrega' ? 'Entrega' : 'Retirada na loja' ?></dd></div>
        <?php if (!empty($pedido['endereco_entrega'])): ?>
          <div style="display:block"><dd class="muted" style="font-size:.88rem"><?= h($pedido['endereco_entrega']) ?></dd></div>
        <?php endif; ?>
        <div><dt>Pagamento</dt><dd><?= loja_status_pagamento($pedido['pagamento'], $pedido['pagamento_status']) ?></dd></div>
        <?php if (!empty($pedido['cartao_final'])): ?>
          <div><dt>Cartão</dt><dd>final <?= h($pedido['cartao_final']) ?></dd></div>
        <?php endif; ?>
        <div class="total"><dt style="color:inherit">Total</dt><dd class="preco"><?= moeda($pedido['valor_total']) ?></dd></div>
      </dl>
    </aside>
  </div>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
