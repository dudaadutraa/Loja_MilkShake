<?php
$tituloPagina = 'Criar conta';
require __DIR__ . '/_topo.php';
?>
<div class="wrap">
  <div class="auth" style="max-width:560px">
    <h1 class="titulo">Criar conta</h1>
    <form class="caixa" method="post" action="<?= h(sn_url(['controller' => 'conta', 'action' => 'cadastro'])) ?>">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

      <label class="campo"><span>Nome completo</span>
        <input name="nome" required maxlength="100" autocomplete="name" value="<?= h($f['nome']) ?>"></label>
      <div class="linha2">
        <label class="campo"><span>E-mail</span>
          <input type="email" name="email" required maxlength="100" autocomplete="email" value="<?= h($f['email']) ?>"></label>
        <label class="campo"><span>Telefone / WhatsApp</span>
          <input name="telefone" required maxlength="20" inputmode="tel" autocomplete="tel" placeholder="(21) 99999-9999" value="<?= h($f['telefone']) ?>"></label>
      </div>
      <label class="campo"><span>CPF</span>
        <input name="cpf" required maxlength="14" inputmode="numeric" placeholder="000.000.000-00" value="<?= h($f['cpf']) ?>">
        <small>Usado na nota fiscal do pedido.</small></label>
      <div class="linha2">
        <label class="campo"><span>CEP (opcional)</span>
          <input id="cep" maxlength="9" inputmode="numeric" placeholder="00000-000" autocomplete="postal-code"
                 data-cep data-endereco="#endereco" data-status="#cepStatus" data-url="<?= h(sn_url(['controller' => 'cep', 'action' => 'buscar'])) ?>&cep=">
          <small id="cepStatus" aria-live="polite">Preenche o endereço sozinho.</small></label>
      </div>
      <label class="campo"><span>Endereço (opcional)</span>
        <input id="endereco" name="endereco" maxlength="240" autocomplete="street-address" placeholder="Rua, número, bairro e cidade" value="<?= h($f['endereco']) ?>">
        <small>Se preencher, já aparece pronto quando você escolher entrega.</small></label>
      <div class="linha2">
        <label class="campo"><span>Senha</span>
          <input type="password" name="senha" required minlength="6" autocomplete="new-password">
          <small>Mínimo de 6 caracteres.</small></label>
        <label class="campo"><span>Repita a senha</span>
          <input type="password" name="senha2" required minlength="6" autocomplete="new-password"></label>
      </div>
      <button class="btn cheio" type="submit">Criar conta</button>
    </form>
    <p class="troca">Já tem conta? <a href="<?= h(sn_url(['controller' => 'conta', 'action' => 'entrar'])) ?>">Entrar</a></p>
  </div>
</div>
<script src="<?= h(sn_asset('assets/js/cep.js')) ?>"></script>
<?php require __DIR__ . '/_rodape.php'; ?>
