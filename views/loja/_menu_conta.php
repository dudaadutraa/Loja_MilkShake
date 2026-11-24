<?php $abaConta = $abaConta ?? 'pedidos'; ?>
<h1 class="titulo">Minha conta</h1>
<nav class="menu-conta" aria-label="Minha conta">
  <a href="<?= h(sn_url(['controller' => 'conta'])) ?>" class="<?= $abaConta === 'pedidos' ? 'ativa' : '' ?>">Meus pedidos</a>
  <a href="<?= h(sn_url(['controller' => 'conta', 'action' => 'dados'])) ?>" class="<?= $abaConta === 'dados' ? 'ativa' : '' ?>">Meus dados</a>
</nav>
