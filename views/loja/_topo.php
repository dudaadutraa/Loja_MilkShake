<?php
/**
 * Cabeçalho da loja virtual. No início de cada view da loja:
 *   $tituloPagina = 'Cardápio'; require __DIR__ . '/_topo.php';
 * Variáveis vindas do LojaBaseController: $qtdCarrinho, $flash, $categoriasMenu, $cliente, $csrf
 */
$tituloPagina = $tituloPagina ?? 'Shake Nature';
$catAtual  = (int) ($_GET['categoria'] ?? 0);
$naVitrine = ($_GET['controller'] ?? '') === 'loja' && ($_GET['action'] ?? 'index') === 'index';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($tituloPagina) ?> · Shake Nature</title>
<meta name="description" content="Shake Nature: milkshakes e doces. Peça pelo site e retire na loja ou receba em casa.">
<link rel="icon" type="image/png" href="<?= h(sn_asset('assets/img/logo.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h(sn_asset('assets/css/loja.css')) ?>">
</head>
<body>
<a class="sr" href="#conteudo">Ir para o conteúdo</a>

<header class="topo">
  <div class="wrap">
    <a class="marca" href="<?= h(sn_url(['controller' => 'loja'])) ?>" aria-label="Shake Nature, página inicial">
      <img src="<?= h(sn_asset('assets/img/logo.png')) ?>" alt="">
      <span><strong>SHAKE</strong><em>nature</em></span>
    </a>

    <nav class="menu" aria-label="Principal">
      <a href="<?= h(sn_url(['controller' => 'loja'])) ?>#cardapio" class="<?= $naVitrine ? 'ativo' : '' ?>">Cardápio</a>
      <a href="<?= h(sn_url(['controller' => 'loja'])) ?>#como-funciona" class="esconde">Como funciona</a>
      <?php if ($cliente): ?>
        <a href="<?= h(sn_url(['controller' => 'conta'])) ?>" class="<?= ($_GET['controller'] ?? '') === 'conta' ? 'ativo' : '' ?>">Meus pedidos</a>
      <?php endif; ?>
    </nav>

    <div class="conta">
      <?php if ($cliente): ?>
        <span class="ola esconde">Olá, <b><?= h(explode(' ', trim($cliente['nome']))[0]) ?></b></span>
        <form method="post" action="<?= h(sn_url(['controller' => 'conta', 'action' => 'sair'])) ?>" class="esconde">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
          <button class="link" type="submit">Sair</button>
        </form>
      <?php else: ?>
        <a class="entrar" href="<?= h(sn_url(['controller' => 'conta', 'action' => 'entrar'])) ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
          <span>Entrar</span>
        </a>
      <?php endif; ?>
      <a class="sacola" href="<?= h(sn_url(['controller' => 'carrinho'])) ?>" aria-label="Carrinho com <?= (int) $qtdCarrinho ?> itens">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1.2 11.2a2 2 0 0 1-2 1.8H8.2a2 2 0 0 1-2-1.8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
        <?php if ($qtdCarrinho > 0): ?><span class="n"><?= (int) $qtdCarrinho ?></span><?php endif; ?>
      </a>
    </div>
  </div>
</header>

<main id="conteudo">
<?php if (!empty($flash)): ?>
  <div class="wrap"><div class="aviso <?= $flash['tipo'] === 'ok' ? 'ok' : 'erro' ?>" role="<?= $flash['tipo'] === 'ok' ? 'status' : 'alert' ?>"><?= h($flash['msg']) ?></div></div>
<?php endif; ?>
