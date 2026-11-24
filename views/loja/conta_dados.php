<?php
$tituloPagina = 'Meus dados';
require __DIR__ . '/_topo.php';
$abaConta = 'dados';
$cpf = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', (string) $cliente['cpf_cnpj']);
?>
<div class="wrap">
  <?php require __DIR__ . '/_menu_conta.php'; ?>

  <div class="duas-col" style="grid-template-columns:minmax(0,1fr) minmax(0,1fr)">
    <form class="caixa" method="post" action="<?= h(sn_url(['controller' => 'conta', 'action' => 'dados'])) ?>">
      <h2>Dados pessoais</h2>
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="acao" value="dados">
      <label class="campo"><span>Nome completo</span>
        <input name="nome" required maxlength="100" value="<?= h($cliente['nome']) ?>"></label>
      <label class="campo"><span>Telefone / WhatsApp</span>
        <input name="telefone" required maxlength="20" inputmode="tel" value="<?= h($cliente['telefone']) ?>"></label>
      <label class="campo"><span>Endereço</span>
        <input name="endereco" maxlength="240" value="<?= h($cliente['endereco']) ?>"></label>
      <p class="muted" style="font-size:.85rem;margin-bottom:14px">E-mail: <?= h($cliente['email']) ?><br>CPF: <?= h($cpf) ?></p>
      <button class="btn" type="submit">Salvar dados</button>
    </form>

    <form class="caixa" method="post" action="<?= h(sn_url(['controller' => 'conta', 'action' => 'dados'])) ?>">
      <h2>Trocar senha</h2>
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="acao" value="senha">
      <label class="campo"><span>Senha atual</span>
        <input type="password" name="senha_atual" required autocomplete="current-password"></label>
      <label class="campo"><span>Nova senha</span>
        <input type="password" name="senha_nova" required minlength="6" autocomplete="new-password"></label>
      <button class="btn sec" type="submit">Trocar senha</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
