<?php
$tituloPagina = 'Pedido recebido';
require __DIR__ . '/_topo.php';
$etapa = 3;
$ehEntrega = $pedido['entrega'] === 'entrega';
?>
<div class="wrap">
  <?php require __DIR__ . '/_etapas.php'; ?>

  <div class="recebido">
    <div class="caixa">
      <div class="topo-ok">
        <svg viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="19" fill="#E5F2E9"/><path d="M12 20.5l5.5 5.5L28.5 14" fill="none" stroke="#2F7D4F" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <div>
          <h1 style="font-size:1.4rem">Pedido nº <?= (int) $pedido['id'] ?> recebido</h1>
          <p class="muted">Total de <?= moeda($pedido['valor_total']) ?> · <?= $ehEntrega ? 'entrega em casa' : 'retirada na loja' ?></p>
        </div>
      </div>

      <?php if ($pedido['pagamento'] === 'pix'): ?>
        <h2 style="font-size:1.05rem;margin-bottom:6px">Pague com Pix</h2>
        <?php if ($chavePix !== ''): ?>
          <p>Faça um Pix de <strong><?= moeda($pedido['valor_total']) ?></strong> para a chave abaixo e envie o comprovante pelo WhatsApp. Começamos a preparar assim que o pagamento cair.</p>
          <div class="chave">
            <input id="chavePix" readonly value="<?= h($chavePix) ?>" aria-label="Chave Pix">
            <button class="btn sec" type="button" onclick="var c=document.getElementById('chavePix');c.select();navigator.clipboard&&navigator.clipboard.writeText(c.value);this.textContent='Copiada'">Copiar</button>
          </div>
        <?php else: ?>
          <p>Vamos enviar a chave Pix de <strong><?= moeda($pedido['valor_total']) ?></strong> pelo WhatsApp do seu cadastro. Começamos a preparar assim que o pagamento cair.</p>
        <?php endif; ?>
      <?php elseif ($pedido['pagamento'] === 'cartao'): ?>
        <p>Pagamento aprovado no cartão final <strong><?= h($pedido['cartao_final']) ?></strong>. Já estamos preparando seu pedido.</p>
      <?php else: ?>
        <p>Você paga em dinheiro <?= $ehEntrega ? 'na entrega' : 'na retirada' ?>. Se precisar de troco, avise quando entrarmos em contato.</p>
      <?php endif; ?>

      <p class="muted" style="margin-top:12px"><?= $ehEntrega
          ? 'Vamos chamar no seu WhatsApp para confirmar a taxa e o horário de entrega.'
          : 'Avisamos pelo WhatsApp quando estiver pronto para retirar.' ?></p>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px">
        <a class="btn" href="<?= h(sn_url(['controller' => 'conta', 'action' => 'pedido', 'id' => $pedido['id']])) ?>">Acompanhar pedido</a>
        <a class="btn sec" href="<?= h(sn_url(['controller' => 'loja'])) ?>">Voltar ao cardápio</a>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
