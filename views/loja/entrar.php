<?php
$tituloPagina = 'Entrar';
require __DIR__ . '/_topo.php';
?>
<div class="wrap">
  <div class="auth">
    <h1 class="titulo">Entrar</h1>
    <form class="caixa" method="post" action="<?= h(sn_url(['controller' => 'conta', 'action' => 'entrar'])) ?>">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <label class="campo"><span>E-mail</span>
        <input type="email" name="email" required autocomplete="email" value="<?= h($email) ?>" autofocus></label>
      <label class="campo"><span>Senha</span>
        <input type="password" name="senha" required autocomplete="current-password"></label>
      <button class="btn cheio" type="submit">Entrar</button>
    </form>
    <p class="troca">Ainda não tem conta? <a href="<?= h(sn_url(['controller' => 'conta', 'action' => 'cadastro'])) ?>">Criar conta</a></p>
  </div>
</div>
<?php require __DIR__ . '/_rodape.php'; ?>
