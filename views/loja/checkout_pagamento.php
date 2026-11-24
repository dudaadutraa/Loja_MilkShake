<?php
$tituloPagina = 'Pagamento';
require __DIR__ . '/_topo.php';
$etapa = 2;
$receber = $checkout['entrega'] === 'entrega' ? 'na entrega' : 'na retirada';
?>
<div class="wrap">
  <?php require __DIR__ . '/_etapas.php'; ?>
  <h1 class="titulo">Forma de pagamento</h1>

  <div class="duas-col">
    <form class="caixa" method="post" action="<?= h(sn_url(['controller' => 'pedido', 'action' => 'finalizar'])) ?>" id="formPag">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

      <p class="muted" style="font-size:.9rem;margin-bottom:14px">
        <?= $checkout['entrega'] === 'entrega' ? 'Entrega em: ' . h($checkout['endereco']) : 'Retirada na loja' ?>
        · <a href="<?= h(sn_url(['controller' => 'pedido'])) ?>">alterar</a>
      </p>

      <fieldset class="fora">
        <legend class="sr">Forma de pagamento</legend>
        <div class="opcoes">
          <label class="opcao">
            <input type="radio" name="metodo" value="pix" <?= $metodo === 'pix' ? 'checked' : '' ?>>
            <span class="txt"><b>Pix</b><small>Você recebe a chave na próxima tela</small></span>
          </label>
          <label class="opcao">
            <input type="radio" name="metodo" value="cartao" <?= $metodo === 'cartao' ? 'checked' : '' ?>>
            <span class="txt"><b>Cartão de crédito</b><small>Pagamento à vista</small></span>
          </label>
          <label class="opcao">
            <input type="radio" name="metodo" value="dinheiro" <?= $metodo === 'dinheiro' ? 'checked' : '' ?>>
            <span class="txt"><b>Dinheiro</b><small>Pague <?= $receber ?></small></span>
          </label>
        </div>
      </fieldset>

      <div class="painel-pag" id="painelCartao" <?= $metodo === 'cartao' ? '' : 'hidden' ?>>
        <p class="seguro">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
          O número completo e o CVV não são enviados nem guardados pela loja.
        </p>
        <!-- Os campos do cartão NÃO têm "name": não vão para o servidor. Só os 4 últimos dígitos e a validade são enviados. -->
        <label class="campo"><span>Número do cartão</span>
          <input id="cNumero" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="0000 0000 0000 0000"></label>
        <label class="campo"><span>Nome impresso no cartão</span>
          <input id="cNome" autocomplete="cc-name" maxlength="60"></label>
        <div class="linha2">
          <label class="campo"><span>Validade</span>
            <input id="cValidade" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="MM/AA"></label>
          <label class="campo"><span>CVV</span>
            <input id="cCvv" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="123"></label>
        </div>
        <input type="hidden" name="cartao_final" id="cFinal">
        <input type="hidden" name="cartao_validade" id="cValEnvio">
        <input type="hidden" name="cartao_ok" id="cOk">
      </div>

      <p class="aviso erro" id="erroCartao" role="alert" hidden style="margin:0 0 14px"></p>
      <button class="btn cheio" type="submit" id="btnEnviar">Confirmar pedido · <?= moeda($total) ?></button>
    </form>

    <?php require __DIR__ . '/_resumo_pedido.php'; ?>
  </div>
</div>
<script>
(function () {
  var form = document.getElementById('formPag');
  var painel = document.getElementById('painelCartao');
  var btn = document.getElementById('btnEnviar');
  var textoBtn = btn.textContent;
  var erro = document.getElementById('erroCartao');
  var num = document.getElementById('cNumero');
  var val = document.getElementById('cValidade');
  var cvv = document.getElementById('cCvv');
  var nome = document.getElementById('cNome');

  function metodo() { return document.querySelector('input[name="metodo"]:checked').value; }
  function mostrarErro(msg, campo) {
    erro.textContent = msg; erro.hidden = !msg;
    if (campo) { campo.focus(); }
  }
  function atualizar() { painel.hidden = metodo() !== 'cartao'; mostrarErro(''); }
  document.querySelectorAll('input[name="metodo"]').forEach(function (r) { r.addEventListener('change', atualizar); });
  atualizar();

  // máscaras
  num.addEventListener('input', function () { num.value = num.value.replace(/\D/g, '').slice(0, 19).replace(/(\d{4})(?=\d)/g, '$1 '); });
  val.addEventListener('input', function () {
    var v = val.value.replace(/\D/g, '').slice(0, 4);
    val.value = v.length > 2 ? v.slice(0, 2) + '/' + v.slice(2) : v;
  });
  cvv.addEventListener('input', function () { cvv.value = cvv.value.replace(/\D/g, '').slice(0, 4); });

  function luhn(n) {
    var soma = 0, dobra = false;
    for (var i = n.length - 1; i >= 0; i--) {
      var d = +n[i];
      if (dobra) { d *= 2; if (d > 9) { d -= 9; } }
      soma += d; dobra = !dobra;
    }
    return soma % 10 === 0;
  }

  // Confere o cartão aqui no navegador (o pagamento é simulado: nada é cobrado)
  function validarCartao() {
    var n = num.value.replace(/\D/g, '');
    if (n.length < 13 || n.length > 19 || !luhn(n)) { return mostrarErro('Número do cartão inválido. Confira os dígitos.', num), false; }
    if (nome.value.trim().length < 3) { return mostrarErro('Informe o nome impresso no cartão.', nome), false; }
    var m = /^(0[1-9]|1[0-2])\/(\d{2})$/.exec(val.value);
    if (!m) { return mostrarErro('Validade inválida. Use MM/AA.', val), false; }
    var fim = new Date(2000 + +m[2], +m[1], 0, 23, 59, 59);
    if (fim < new Date()) { return mostrarErro('Este cartão está vencido.', val), false; }
    if (cvv.value.length < 3) { return mostrarErro('CVV inválido.', cvv), false; }
    document.getElementById('cFinal').value = n.slice(-4);
    document.getElementById('cValEnvio').value = val.value;
    document.getElementById('cOk').value = '1';
    return true;
  }

  form.addEventListener('submit', function (e) {
    mostrarErro('');
    if (metodo() === 'cartao') {
      if (!validarCartao()) { e.preventDefault(); return; }
    } else {
      document.getElementById('cFinal').value = '';
      document.getElementById('cValEnvio').value = '';
      document.getElementById('cOk').value = '';
    }
    btn.disabled = true;
    btn.textContent = 'Enviando pedido…';
    // Se a resposta demorar demais, libera o botão de novo
    setTimeout(function () { btn.disabled = false; btn.textContent = textoBtn; }, 20000);
  });

  // Voltar pelo navegador não deixa o botão travado
  window.addEventListener('pageshow', function () { btn.disabled = false; btn.textContent = textoBtn; });
})();
</script>
<?php require __DIR__ . '/_rodape.php'; ?>
