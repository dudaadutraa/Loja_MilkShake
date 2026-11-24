<?php
/** Indicador das etapas do pedido. Defina $etapa = 1, 2 ou 3 antes de incluir. */
$nomes = [1 => 'Entrega', 2 => 'Pagamento', 3 => 'Pedido recebido'];
?>
<ol class="etapas" aria-label="Etapas do pedido">
  <?php foreach ($nomes as $n => $nome): ?>
    <li class="<?= $n < $etapa ? 'feita' : ($n === $etapa ? 'atual' : '') ?>" <?= $n === $etapa ? 'aria-current="step"' : '' ?>>
      <i><?= $n < $etapa ? '✓' : $n ?></i><?= $nome ?>
    </li>
  <?php endforeach; ?>
</ol>
