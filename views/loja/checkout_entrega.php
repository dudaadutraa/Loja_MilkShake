<?php
$tituloPagina = 'Entrega';
require __DIR__ . '/_topo.php';
$etapa = 1;
$ehEntrega = $checkout['entrega'] === 'entrega';
?>
<div class="wrap">
  <?php require __DIR__ . '/_etapas.php'; ?>
  <h1 class="titulo">Como você quer receber?</h1>

  <div class="duas-col">
    <form class="caixa" method="post" action="<?= h(sn_url(['controller' => 'pedido'])) ?>">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

      <fieldset class="fora">
        <legend class="sr">Forma de recebimento</legend>
        <div class="opcoes">
          <label class="opcao">
            <input type="radio" name="entrega" value="retirada" <?= $ehEntrega ? '' : 'checked' ?>>
            <span class="txt"><b>Retirar na loja</b><small>Avisamos pelo WhatsApp quando estiver pronto</small></span>
            <span class="preco">Grátis</span>
          </label>
          <label class="opcao">
            <input type="radio" name="entrega" value="entrega" <?= $ehEntrega ? 'checked' : '' ?>>
            <span class="txt"><b>Receber em casa</b><small>A taxa depende do bairro e é combinada pelo WhatsApp</small></span>
            <span class="muted" style="font-size:.88rem">a combinar</span>
          </label>
        </div>
      </fieldset>

      <div id="campoEndereco" <?= $ehEntrega ? '' : 'hidden' ?>>
      <label class="campo" style="max-width:220px"><span>CEP</span>
        <input id="cep" maxlength="9" inputmode="numeric" placeholder="00000-000" autocomplete="postal-code"
               data-cep data-endereco="#endereco" data-status="#cepStatus" data-url="<?= h(sn_url(['controller' => 'cep', 'action' => 'buscar'])) ?>&cep=">
        <small id="cepStatus" aria-live="polite">Preenche o endereço sozinho.</small></label>
      <label class="campo"><span>Endereço de entrega</span>
        <textarea id="endereco" name="endereco" rows="2" maxlength="240" autocomplete="street-address" placeholder="Rua, número, complemento, bairro, cidade e ponto de referência" <?= $ehEntrega ? 'required' : '' ?>><?= h($checkout['endereco']) ?></textarea></label>
      </div>

      <p class="muted" style="font-size:.88rem;margin-bottom:14px">Contato do pedido: <?= h($cliente['telefone']) ?> · <a href="<?= h(sn_url(['controller' => 'conta', 'action' => 'dados'])) ?>">alterar</a></p>
      <button class="btn cheio" type="submit">Continuar para o pagamento</button>
    </form>

    <?php require __DIR__ . '/_resumo_pedido.php'; ?>
  </div>
</div>
<script>
document.querySelectorAll('input[name="entrega"]').forEach(function (r) {
  r.addEventListener('change', function () {
    var c = document.getElementById('campoEndereco');
    c.hidden = r.value !== 'entrega';
    c.querySelector('textarea').required = r.value === 'entrega';
  });
});
</script>
<script src="<?= h(sn_asset('assets/js/cep.js')) ?>"></script>
<?php require __DIR__ . '/_rodape.php'; ?>
